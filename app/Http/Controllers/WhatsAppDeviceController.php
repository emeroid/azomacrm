<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\WhatsappDevice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    public function toggleAutoResponders(Request $request, WhatsappDevice $device)
    {
        // $this->authorize('update', $device);

        $isEnabled = $request->input('enabled', false);
        $device->update(['auto_responder_enabled' => $isEnabled]);

        // **Tell Redis about this change**
        \Illuminate\Support\Facades\Redis::hSet(
            'device_settings',
            $device->session_id,
            json_encode(['autoResponder' => $isEnabled])
        );

        return back()->with('success', 'Auto-responder settings updated.');
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

    public function edit(WhatsappDevice $device)
    {
        // Ensure the device belongs to the authenticated user
        if (auth()->id() !== $device->user_id) {
            abort(403);
        }

        return Inertia::render('WhatsApp/Devices/Edit', [
            'device' => $device,
        ]);
    }

    public function update(Request $request, WhatsappDevice $device)
    {
        // Ensure the device belongs to the authenticated user
        if (auth()->id() !== $device->user_id) {
            abort(403);
        }

        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'min_delay' => 'required|integer|min:1',
            'max_delay' => 'required|integer|min:' . $request->input('min_delay', 1), // max must be >= min
        ]);

        try{
            // Update only the fields that are fillable/editable
            $device->update([
                'name' => $validatedData['name'],
                'min_delay' => $validatedData['min_delay'],
                'max_delay' => $validatedData['max_delay'],
            ]);

            // Redirect back to the index page with a success message
            return redirect()->route('devices.index')->with('success', 'Device settings updated successfully!');
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'Failed to update device, please try again later!');
        }
    }

    /**
     * Remove the specified device from storage.
     */
    public function destroy(WhatsappDevice $device)
    {
        // Ensure the user is authorized to delete this device
        // $this->authorize('delete', $device);
        
        $gatewayUrl = config('services.whatsapp.gateway_url');
        try {
            // Tell the gateway to terminate the session (async)
            Http::withHeaders(['X-API-KEY' => config('services.whatsapp.api_key')])
                ->post("{$gatewayUrl}/sessions/logout", [
                    'sessionId' => $device->session_id,
                ])->throw();

            $device->delete();

            return redirect()->route('devices.index')->with('success', 'Device has been disconnected successfully.');

        } catch(\Exception $e) {
            Log::error("Error Removing Device: ", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to delete device. ' . $e->getMessage());
        }
    }
}
