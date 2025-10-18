<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\WhatsappDevice;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class WhatsAppDeviceController extends Controller
{
    /**
     * Display a listing of the user's connected devices.
     */
    public function index()
    {
        return Inertia::render('WhatsApp/Devices/Index', [
            'devices' => auth()->user()->whatsappDevices()->get(),
        ]);
    }

    // public function startSession()
    // {

    //     // Check if there's already an existing un-connected device for the user and use that session ID if available
    //     $existingDevice = auth()->user()->whatsappDevices()
    //         ->where('status', '!=', 'connected')
    //         ->first();

    //     if ($existingDevice) {
    //         $sessionId = $existingDevice->session_id;
    //         // Optionally: Check if the session is still active on the gateway, re-start if needed

    //         $this->wormServer($sessionId);

    //     } else {
    //         $sessionId = 'user-' . auth()->id() . '-' . Str::random(10);
    //         $response = $this->wormServer($sessionId);
    //         if ($response->successful() || $response->status() === 202) {
    //             // 2. Save the new device to the DB
    //             auth()->user()->whatsappDevices()->create([
    //                 'session_id' => $sessionId,
    //                 'status' => 'pending-qr',
    //                 'qr_code_url' => null,
    //             ]);
    //         } else {
    //             return redirect()->route('devices.index')->withErrors(['gateway' => 'Could not connect to the WhatsApp Gateway. Please try again later.']);
    //         }
    //     }

    //     // 3. Redirect to the dedicated status page which will handle the polling
    //     return redirect()->route('devices.status', ['sessionId' => $sessionId]);
    // }


    // // The new Inertia page that is responsible for showing status and polling
    // public function showStatus(string $sessionId)
    // {
    //     $device = auth()->user()->whatsappDevices()
    //         ->where('session_id', $sessionId)
    //         ->firstOrFail();

    //     return Inertia::render('WhatsApp/Devices/Create', [
    //         'sessionId' => $device->session_id,
    //         'device' => $device->toArray(), // Pass the device data
    //     ]);
    // }

    public function startSession()
    {
        // Find or create a device record in a 'disconnected' state.
        $device = auth()->user()->whatsappDevices()
            ->whereIn('status', ['disconnected', 'pending-qr', 'scanning', 'expired']) // Look for any non-connected device
            ->first();

        if (!$device) {
            $device = auth()->user()->whatsappDevices()->create([
                'session_id' => 'user-' . auth()->id() . '-' . Str::random(10),
                'status' => 'pending-qr', // Initial state
            ]);
        } else {
            // Reset the state for a new attempt
            $device->update(['status' => 'pending-qr', 'qr_code_url' => null]);
        }

        // Now, tell the Node server to start the process
        $response = $this->callGateway('/sessions/start', ['sessionId' => $device->session_id]);
        
        if (!$response->successful()) {
            $device->update(['status' => 'failed']);
            return redirect()->route('devices.index')->withErrors(['gateway' => 'Could not connect to the WhatsApp Gateway.']);
        }

        // Redirect to the status page, which will handle everything else
        return redirect()->route('devices.status', ['sessionId' => $device->session_id]);
    }

    public function showStatus(string $sessionId)
    {
        $device = auth()->user()->whatsappDevices()->where('session_id', $sessionId)->firstOrFail();
        
        return Inertia::render('WhatsApp/Devices/Create', [
            'initialDevice' => $device->toArray(), // Pass initial data
        ]);
    }

    /**
     * NEW: API endpoint for the frontend to poll device status.
     * This route MUST be protected by auth middleware.
     */
    public function getDeviceStatus(string $sessionId)
    {
        $device = auth()->user()->whatsappDevices()->where('session_id', $sessionId)->firstOrFail();
        return response()->json($device->toArray());
    }

    /**
     * Helper to call the gateway with the API key.
     */
    protected function callGateway(string $endpoint, array $data)
    {
        $gatewayUrl = config('services.whatsapp.gateway_url');
        $apiKey = config('services.whatsapp.api_key');

        return Http::withHeaders(['X-API-KEY' => $apiKey])
            ->post("{$gatewayUrl}{$endpoint}", $data);
    }


    public function renameDevice(Request $request, WhatsappDevice $device)
    {
        $validated = $request->validate([
            'name' => 'required|string'
        ]);

        $device->name = $validated['name'];
        $device->save();

        return redirect()->back()->with('success', 'Device Name updated successfully');
    }

    /**
     * Remove the specified device from storage.
     */
    public function destroy(WhatsappDevice $device)
    {
        // Ensure the user is authorized to delete this device
        // $this->authorize('delete', $device);
        
        $gatewayUrl = config('services.whatsapp.gateway_url');

        // Tell the gateway to terminate the session (async)
        Http::post("{$gatewayUrl}/sessions/logout", [
            'sessionId' => $device->session_id,
        ])->throw();

        $device->delete();

        return redirect()->route('devices.index')->with('success', 'Device has been disconnected successfully.');
    }
}
