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
        
        // --- PRE-FLIGHT WARM-UP ---
        // As a safety measure, we'll ping the /sessions/start endpoint.
        // If the session is already active, the gateway will do nothing.
        // If the gateway restarted, this will re-initialize it.
        $gatewayUrl = config('services.whatsapp.gateway_url');
        Http::post("{$gatewayUrl}/sessions/start", [
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
            $campaign->id
        );

        return redirect()->back()->with('success', 'Campaign has been queued successfully!');
    }
}

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Inertia\Inertia;
// use App\Jobs\SendWhatsappBroadcast;
// use Maatwebsite\Excel\Facades\Excel;

// class CampaignController extends Controller
// {
//     public function create()
//     {
//         return Inertia::render('Campaigns/Create', [
//             'devices' => auth()->user()->whatsappDevices()->get(),
//         ]);
//     }

//     public function store(Request $request)
//     {
        
//         $validated = $request->validate([
//             'session_id' => 'required|string', // ADDED: Ensure session_id is validated from the form/request body
//             'message' => 'required|string|min:1',
//             'delay' => 'required|integer|min:1',
//             'input_method' => 'required|in:manual,file',
//             'phone_numbers' => 'required_if:input_method,manual|string',
//             'contacts_file' => 'nullable',
//         ]);

//         // dd($validated);

//         $phoneNumbers = [];

        
        
//         // Retrieve session_id from the validated data
//         $sessionId = $validated['session_id'];

//         if ($validated['input_method'] === 'manual') {
//             // Split numbers by new line, trim whitespace, and filter out empty lines
//             $phoneNumbers = array_filter(array_map('trim', explode("\n", $validated['phone_numbers'])));
//         } else {
//             // Parse the uploaded file
//             $rows = Excel::toCollection(null, $request->file('contacts_file'))[0]; // Get the first sheet
//             $phoneNumbers = $rows->flatten()->filter()->all(); // Assuming numbers are in the first column
//         }

//         // Dispatch the job to the queue
//         SendWhatsappBroadcast::dispatch(
//             $sessionId, // Now using the variable retrieved from $validated
//             $phoneNumbers,
//             $validated['message'],
//             $validated['delay']
//         );

//         return redirect()->back()->with('success', 'Campaign has been queued successfully!');
//     }
// }