<?php

namespace App\Http\Controllers;

use App\Models\ScheduledMessage;
use App\Models\AutoResponder;
use App\Models\MessageLog; // Used for Campaigns/Broadcasts
use App\Models\AutoResponderLog; // Used for AutoResponder replies
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WaAnalyticsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // --- 1. Scheduled Message Analytics ---
        $scheduledAnalytics = $user->scheduledMessages()
            ->selectRaw('SUM(sent_count) as total_sent, SUM(failed_count) as total_failed')
            ->first();
            
        $scheduledData = [
            'total' => (int) $scheduledAnalytics->total_sent + (int) $scheduledAnalytics->total_failed,
            'success' => (int) $scheduledAnalytics->total_sent,
            'failure' => (int) $scheduledAnalytics->total_failed,
        ];
        
        // --- 2. Auto Responder Analytics ---
        $responderAnalytics = $user->autoResponders()
            ->selectRaw('SUM(hit_count) as total_hits')
            ->first();
            
        $responderData = [
            'total_hits' => (int) $responderAnalytics->total_hits,
            // Get success/failure from the log table
            'reply_status' => AutoResponderLog::whereIn('auto_responder_id', $user->autoResponders()->pluck('id'))
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];
        
        // --- 3. Campaign (Broadcast) Analytics ---
        // Assuming every broadcast creates N MessageLog entries belonging to the user
        // and you can differentiate a Campaign log from a Scheduled log (e.g., using a tag in MessageLog)
        // For simplicity, we'll analyze all user's message logs based on status.
        $campaignLogStatuses = MessageLog::where('user_id', $user->id)
            // IMPORTANT: You need a way to filter only CAMPAIGN messages, 
            // e.g., where('type', 'campaign') if you added a 'type' column to MessageLog
            ->whereIn('status', ['sent', 'delivered', 'read', 'failed']) // Assuming 'failed' is a status
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $campaignData = [
            'total' => array_sum($campaignLogStatuses),
            'success' => ($campaignLogStatuses['sent'] ?? 0) + ($campaignLogStatuses['delivered'] ?? 0) + ($campaignLogStatuses['read'] ?? 0),
            'failure' => $campaignLogStatuses['failed'] ?? 0,
        ];
        
        return Inertia::render('Analytics/Index', [
            'scheduledData' => $scheduledData,
            'responderData' => $responderData,
            'campaignData' => $campaignData,
        ]);
    }
}