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

    /**
     * Show the form for creating a new device (starts session process).
     */
    public function create()
    {
        $sessionId = 'user-' . auth()->id() . '-' . Str::random(10);
        $gatewayUrl = config('services.whatsapp.gateway_url');
        
        // 1. Call the Node.js gateway to START a session (it responds 202 immediately)
        $response = Http::post("{$gatewayUrl}/sessions/start", [
            'sessionId' => $sessionId,
        ]);

        if ($response->successful() || $response->status() === 202) {
            // 2. Save the device to the DB with a "pending-qr" status.
            // The QR code URL will be filled by the webhook later.
            auth()->user()->whatsappDevices()->create([
                'session_id' => $sessionId,
                'status' => 'pending-qr', // Initial status
                'qr_code_url' => null,     // Will be updated by the webhook
            ]);

            // 3. Return the Inertia view for the frontend to start polling this device's status.
            return Inertia::render('WhatsApp/Devices/Create', [
                'sessionId' => $sessionId,
                // We no longer pass the qrCode here, as it's not ready yet.
            ]);
        }

        return redirect()->route('devices.index')->withErrors(['gateway' => 'Could not connect to the WhatsApp Gateway. Please try again later.']);
    }
    
    /**
     * Inertia endpoint for the frontend to poll the status of a device.
     * This MUST return an Inertia response.
     */
    public function getDeviceStatus(Request $request, string $sessionId)
    {
        $device = auth()->user()->whatsappDevices()
            ->where('session_id', $sessionId)
            ->firstOrFail();

        // Return the current device data back to the same Inertia component
        return Inertia::render('WhatsApp/Devices/Create', [
            'sessionId' => $device->session_id,
            'device' => $device->toArray(), // Pass the device object for status/QR data
        ]);
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
