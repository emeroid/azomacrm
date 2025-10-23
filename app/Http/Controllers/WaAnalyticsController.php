<?php

namespace App\Http\Controllers;

use App\Models\ScheduledMessage;
use App\Models\AutoResponder;
use App\Models\MessageLog; // Used for Campaigns/Broadcasts
use App\Models\AutoResponderLog; // Used for AutoResponder replies
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class WaAnalyticsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Get message logs for the current user
        $messageLogs = MessageLog::where('user_id', $user->id)
            ->whereDate('created_at', '>=', $today->subDays(7))
            ->with(['campaign', 'scheduledMessage'])
            ->get();

        // Overview Statistics
        $overview = [
            'total_messages' => $messageLogs->count(),
            'successful_messages' => $messageLogs->whereIn('status', ['sent', 'delivered'])->count(),
            'failed_messages' => $messageLogs->where('status', 'failed')->count(),
            'success_rate' => $messageLogs->count() > 0 ? 
                round(($messageLogs->whereIn('status', ['sent', 'delivered'])->count() / $messageLogs->count()) * 100, 1) : 0,
        ];

        // Message Trends
        $messageTrends = [
            'sent' => $messageLogs->where('status', 'sent')->count(),
            'delivered' => $messageLogs->where('status', 'delivered')->count(),
            'failed' => $messageLogs->where('status', 'failed')->count(),
            'pending' => $messageLogs->where('status', 'pending')->count(),
        ];

        // Failure Breakdown
        $failureReasons = $messageLogs->where('status', 'failed')->pluck('failure_reason');
        $failureBreakdown = [
            'invalid_number' => $failureReasons->filter(fn($reason) => stripos($reason, 'invalid') !== false)->count(),
            'network_error' => $failureReasons->filter(fn($reason) => stripos($reason, 'network') !== false || stripos($reason, 'timeout') !== false)->count(),
            'device_offline' => $failureReasons->filter(fn($reason) => stripos($reason, 'offline') !== false || stripos($reason, 'disconnected') !== false)->count(),
            'rate_limited' => $failureReasons->filter(fn($reason) => stripos($reason, 'rate') !== false || stripos($reason, 'limit') !== false)->count(),
            'other' => $failureReasons->count() - 
                $failureReasons->filter(fn($reason) => stripos($reason, 'invalid') !== false)->count() -
                $failureReasons->filter(fn($reason) => stripos($reason, 'network') !== false || stripos($reason, 'timeout') !== false)->count() -
                $failureReasons->filter(fn($reason) => stripos($reason, 'offline') !== false || stripos($reason, 'disconnected') !== false)->count() -
                $failureReasons->filter(fn($reason) => stripos($reason, 'rate') !== false || stripos($reason, 'limit') !== false)->count(),
        ];

        // Source Breakdown
        $sourceBreakdown = [
            'broadcasts' => $messageLogs->whereNotNull('campaign_id')->count(),
            'broadcasts_failed' => $messageLogs->whereNotNull('campaign_id')->where('status', 'failed')->count(),
            'scheduled' => $messageLogs->whereNotNull('scheduled_message_id')->count(),
            'scheduled_failed' => $messageLogs->whereNotNull('scheduled_message_id')->where('status', 'failed')->count(),
            'auto_replies' => $messageLogs->whereNotNull('auto_responder_log_id')->count(),
            'auto_replies_failed' => $messageLogs->whereNotNull('auto_responder_log_id')->where('status', 'failed')->count(),
        ];

        // Recent Messages for detailed view
        $recentMessages = $messageLogs->take(50)->map(function ($log) {
            return [
                'id' => $log->id,
                'recipient_number' => $log->recipient_number,
                'message' => $log->message,
                'status' => $log->status,
                'failure_reason' => $log->failure_reason,
                'sent_at' => $log->sent_at,
                'created_at' => $log->created_at,
                'source' => $log->campaign_id ? 'broadcast' : 
                           ($log->scheduled_message_id ? 'scheduled' : 
                           ($log->auto_responder_log_id ? 'auto_responder' : 'unknown')),
            ];
        });

        return Inertia::render('Analytics/Index', [
            'analyticsData' => [
                'overview' => $overview,
                'messageTrends' => $messageTrends,
                'failureBreakdown' => $failureBreakdown,
                'sourceBreakdown' => $sourceBreakdown,
                'recentMessages' => $recentMessages,
            ],
        ]);
    }
}