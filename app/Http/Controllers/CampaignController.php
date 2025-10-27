<?php

namespace App\Http\Controllers;

use App\Jobs\DispatchWhatsappBroadcast;
use App\Models\Campaign;
use App\Models\WhatsappDevice; // Import the model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Import Http facade
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule; // Import Rule for validation
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = auth()->user()->campaigns()
            ->with('whatsappDevice')
            ->latest()
            ->paginate(10);

        return Inertia::render('Campaigns/Index', [
            'campaigns' => $campaigns,
        ]);
    }

    public function create()
    {
        return Inertia::render('Campaigns/Create', [
            'devices' => auth()->user()->whatsappDevices()->where('status', 'connected')->get(),
            // 'campaigns' => auth()->user()->campaigns()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'session_id' => [
                'required', 'string',
                Rule::exists('whatsapp_devices', 'session_id')->where(function ($query) {
                    $query->where('user_id', auth()->id())->where('status', 'connected');
                }),
            ],
            // Message (with emojis) is nullable IF media is present
            'message' => 'nullable|string|min:1|required_without:media_file',
            'input_method' => 'required|in:manual,file',
            'phone_numbers' => 'required_if:input_method,manual|string',
            'contacts_file' => 'nullable|file|mimes:xlsx,xls,csv',
            'media_file' => [
                'nullable', 'file',
                'mimes:jpg,jpeg,png,webp,mp4,avi,pdf,doc,docx', // Common types
                'max:16384' // 16MB
            ],
        ]);
        
        // --- 1. PRE-FLIGHT WARM-UP (This is correct!) ---
        Http::withHeaders(['X-API-KEY' => config('services.whatsapp.api_key')])
            ->post(config('services.whatsapp.gateway_url') . '/sessions/start', [
                'sessionId' => $validated['session_id'],
            ]);

        // --- 2. Phone Number Processing ---
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

        // --- 3. Media File Handling ---
        $mediaUrl = null;
        if ($request->hasFile('media_file')) {
            // Store in 'public/campaign_media'
            $path = $request->file('media_file')->store('campaign_media', 'public');
            // Get the full, absolute URL for Node.js to access
            $mediaUrl = Storage::disk('public')->url($path);
        }

        // --- 4. Create Campaign Record ---
        $campaign = auth()->user()->campaigns()->create([
            'whatsapp_device_id' => auth()->user()->whatsappDevices()->where('session_id', $validated['session_id'])->firstOrFail()->id,
            'message' => $validated['message'] ?? '', // Emojis are saved here
            'media_url' => $mediaUrl ?? '', // Media URL is saved here
            'status' => 'running',
            'total_recipients' => count($phoneNumbers),
            'queued_at' => now(),
            'sent_count' => 0,
            'failed_count' => 0
        ]);

        // --- 5. Dispatch the *single* main job ---
        DispatchWhatsappBroadcast::dispatch(
            $validated['session_id'],
            $phoneNumbers,
            $validated['message'] ?? '', // Pass the message (with emojis)
            $mediaUrl ?? '',                  // Pass the media URL
            auth()->id(),
            $campaign->id
        )->onQueue('whatsapp-broadcasts');

        return redirect()->route('campaigns.index')->with('success', 'Campaign has been queued successfully!');
    }

    public function pause(Campaign $campaign)
    {
        // Add authorization logic here to ensure user owns this campaign
        
        $campaign->status = 'paused';
        $campaign->save();

        return redirect()->back()->with("success", "Campaign Paused Successfully!");
    }

    public function resume(Campaign $campaign)
    {
        // Add authorization logic here
        $campaign->status = 'running';
        $campaign->save();

        // $campaign->update(['status' => 'running']);
        return redirect()->back()->with("success", "Campaign Resumed Successfully!");
    }
}
