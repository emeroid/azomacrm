<?php

namespace App\Http\Controllers;

use App\Jobs\DispatchWhatsappBroadcast;
use App\Models\WhatsappDevice; // Import the model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Import Http facade
use Illuminate\Validation\Rule; // Import Rule for validation
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class CampaignController extends Controller
{
    public function create()
    {
        return Inertia::render('Campaigns/Create', [
            // Only pass devices that are ready to be used
            'devices' => auth()->user()->whatsappDevices()->where('status', 'connected')->get(),
            'campaigns' => auth()->user()->campaigns()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $userDevices = auth()->user()->whatsappDevices();

        $validated = $request->validate([
            // Validate that the selected session_id exists and BELONGS to the user AND is connected
            'session_id' => [
                'required',
                'string',
                Rule::exists('whatsapp_devices', 'session_id')->where(function ($query) use ($userDevices) {
                    $query->where('user_id', auth()->id())->where('status', 'connected');
                }),
            ],
            'message' => 'required|string|min:1',
            'delay' => 'required|integer|min:1',
            'input_method' => 'required|in:manual,file',
            'phone_numbers' => 'required_if:input_method,manual|string',
            'contacts_file' => 'nullable|file|mimes:xlsx,xls,csv',
        ]);
        
        // --- PRE-FLIGHT WARM-UP (with API Key) ---
        Http::withHeaders(['X-API-KEY' => config('services.whatsapp.api_key')])
            ->post(config('services.whatsapp.gateway_url') . '/sessions/start', [
                'sessionId' => $validated['session_id'],
            ]);

        // Phone number processing logic (your code is good)
        $phoneNumbers = [];
        if ($validated['input_method'] === 'manual') {
            $phoneNumbers = array_filter(array_map('trim', explode("\n", $validated['phone_numbers'])));
        } else {
            $rows = Excel::toCollection(null, $request->file('contacts_file'))[0];
            $phoneNumbers = $rows->flatten()->filter()->all();
        }

        if (empty($phoneNumbers)) {
            return redirect()->back()->withErrors(['contacts' => 'No valid phone numbers found.']);
        }

        // --- STEP 1: Create Campaign Record ---
        $campaign = auth()->user()->campaigns()->create([
            'whatsapp_device_id' => auth()->user()->whatsappDevices()->where('session_id', $validated['session_id'])->firstOrFail()->id,
            'message' => $validated['message'],
            'delay' => $validated['delay'],
            'total_recipients' => count($phoneNumbers),
            'queued_at' => now(),
            'sent_count' => 0, // and failed_count initialize to 0
            'failed_count' => 0
        ]);

        // Dispatch the job
        DispatchWhatsappBroadcast::dispatch(
            $validated['session_id'],
            $phoneNumbers,
            $validated['message'],
            $validated['delay'],
            auth()->id(),
            $campaign->id
        );

        return redirect()->back()->with('success', 'Campaign has been queued successfully!');
    }
}
