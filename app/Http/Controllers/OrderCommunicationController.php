<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderCommunication;
use App\Models\Outcome;
use App\Models\User;
use App\Models\WhatsappTemplate;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class OrderCommunicationController extends Controller
{
    public function index(Request $request)
    {
        $selectedOrder = null;
        $communications = collect();
        
        // Fetch selected order data first
        if ($request->has('order_id')) {
            $selectedOrder = Order::with(['marketer', 'callAgent', 'deliveryAgent', 'items', 'items.product'])
                ->where('call_agent_id', $request->user()->id)
                ->find($request->order_id);
                
            if ($selectedOrder) {
                $communications = $selectedOrder
                    ->communications()
                    ->with(['agent', 'sender'])
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        }
    
        // Only run the main orders query if this is not a partial reload for chat
        // Inertia's 'X-Inertia-Partial-Data' header helps detect this.
        $orders = [];
        if (!$request->header('X-Inertia-Partial-Data')) {
            $query = Order::query()
                ->with('items')
                ->where('call_agent_id', $request->user()->id)
                ->orderByDesc(
                    OrderCommunication::select('created_at')
                        ->whereColumn('order_id', 'orders.id')
                        ->latest()
                        ->take(1)
                );
        
            if ($request->has('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('order_number', 'like', "%{$request->search}%")
                    ->orWhere('full_name', 'like', "%{$request->search}%")
                    ->orWhere('mobile', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
                });
            }

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }
        
            $orders = $query->paginate(20);
        }
    
        // Get outcomes - default + user specific
        $outcomes = Outcome::where('is_default', true)
            ->orWhere('user_id', $request->user()->id)
            ->get();
    
        // Get templates - default + user specific
        $whatsappTemplates = WhatsAppTemplate::where('is_default', true)
            ->orWhere('user_id', $request->user()->id)
            ->get();
    
        return Inertia::render('Orders/ChatIndex', [
            // Use 'lazy' evaluation for orders to avoid sending an empty array on partial loads
            'orders' => fn () => $orders,
            'selectedOrder' => $selectedOrder,
            'communications' => $communications,
            'outcomes' => $outcomes,
            'whatsappTemplates' => $whatsappTemplates,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    // Add this method for infinite scroll
    public function loadMore(Request $request)
    {
        $query = Order::query()
            ->with('items')
            ->where('call_agent_id', $request->user()->id)
            ->orderByDesc(
                OrderCommunication::select('created_at')
                    ->whereColumn('order_id', 'orders.id')
                    ->latest()
                    ->take(1)
            );

        // Search filter
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', "%{$request->search}%")
                ->orWhere('full_name', 'like', "%{$request->search}%")
                ->orWhere('mobile', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20);

        return response()->json([
            'orders' => [
                'data' => $orders->items(),
                'next_page_url' => $orders->nextPageUrl(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ]
        ]);
    }
    
    // public function index(Request $request)
    // {
    //     $query = Order::query()
    //         ->with('items')
    //         ->where('call_agent_id', $request->user()->id)
    //         ->orderByDesc(
    //             OrderCommunication::select('created_at')
    //                 ->whereColumn('order_id', 'orders.id')
    //                 ->latest()
    //                 ->take(1)
    //         );
    
    //     if ($request->has('search')) {
    //         $query->where(function($q) use ($request) {
    //             $q->where('order_number', 'like', "%{$request->search}%")
    //             ->orWhere('full_name', 'like', "%{$request->search}%")
    //             ->orWhere('mobile', 'like', "%{$request->search}%")
    //             ->orWhere('email', 'like', "%{$request->search}%");
    //         });
    //     }
    
    //     $orders = $query->paginate(20);
    
    //     $selectedOrder = null;
    //     $communications = collect();
        
    //     if ($request->has('order_id')) {
    //         $selectedOrder = Order::with(['marketer', 'callAgent', 'deliveryAgent', 'items', 'items.product'])
    //             ->where('call_agent_id', $request->user()->id)
    //             ->find($request->order_id);
                
    //         if ($selectedOrder) {
    //             $communications = $selectedOrder
    //                 ->communications()
    //                 ->with(['agent', 'sender'])
    //                 ->orderBy('created_at', 'asc')
    //                 ->get();
    //         }
    //     }
    
    //     // Get outcomes - default + user specific
    //     $outcomes = Outcome::where('is_default', true)
    //         ->orWhere('user_id', $request->user()->id)
    //         ->get();
    
    //     // Get templates - default + user specific
    //     $whatsappTemplates = WhatsAppTemplate::where('is_default', true)
    //         ->orWhere('user_id', $request->user()->id)
    //         ->get();
    
    //     return Inertia::render('Orders/ChatIndex', [
    //         'orders' => $orders,
    //         'selectedOrder' => $selectedOrder,
    //         'communications' => $communications,
    //         'outcomes' => $outcomes,
    //         'whatsappTemplates' => $whatsappTemplates,
    //         'filters' => $request->only(['search']),
    //     ]);
    // }

    public function store(Request $request, Order $order)
    {
        $request->validate([
            'content' => 'required_if:type,note',
            'type' => 'required|in:call,note,whatsapp',
            'outcome' => 'nullable|string',
        ]);

        $order->communications()->create([
            'agent_id' => $request->user()->id,
            'sender_id' => $request->user()->id,
            'type' => $request->type,
            'content' => $request->content,
            // 'outcome' => $request->outcome,
        ]);

        return redirect()->back()->with('success', 'Communication recorded');
    }
    
    // Add these new methods to the controller
    public function storeOutcome(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        Outcome::create([
            'name' => $request->name,
            'user_id' => $request->user()->id,
        ]);
    
        return redirect()->back()->with('success', 'Outcome added successfully');
    }


    /**
     * Update the status of a specific order.
     *
     * If the status is 'confirmed', a 'delivery_agent_id' is required.
    */
    public function updateOrderStatus(Request $request, Order $order)
    {
        // First, check if the authenticated user is a call agent
        if ($request->user()->role !== Role::CALL_AGENT->value) {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        }
        
        // dd($request->all());
        // Validate the request. 'delivery_agent_id' is required only if status is 'confirmed'.
        $validatedData = $request->validate([
            'status' => [
                'required',
                'string',
                // It's good practice to ensure the status is one of the predefined constants
                Rule::in([
                    Order::STATUS_PROCESSING,
                    Order::STATUS_CONFIRMED,
                    Order::STATUS_CANCELLED,
                    Order::STATUS_SCHEDULED,
                    Order::STATUS_NOT_READY,
                    Order::STATUS_NOT_INTERESTED,
                    Order::STATUS_NOT_REACHABLE,
                    Order::STATUS_PHONE_SWITCHED_OFF,
                    Order::STATUS_TRAVELLED,
                    Order::STATUS_NOT_AVAILABLE,
                ]),
            ],
            'delivery_agent_id' => [
                'required_if:status,' . Order::STATUS_CONFIRMED,
                'nullable', // Allows this field to be null if not required
                'integer',
                'exists:users,id', // Ensures the agent exists in the users table
            ],
        ]);
        
        // Update the order with the validated data.
        // This works because both 'status' and 'delivery_agent_id' are in the
        // $fillable array of the Order model.
        $order->update($validatedData);

        return redirect()->back()->with('success', 'Order status updated successfully!');
    }

    /**
     * Get a list of all delivery agents.
     *
     * This method is useful for populating a dropdown menu to assign an agent to an order.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function getDeliveryAgents(): JsonResponse
    {
        // Fetch users who have the 'delivery_agent' role
        $agents = User::where('role', Role::DELIVERY_AGENT->value)
                    ->get(['id', 'first_name', 'last_name', 'mobile']);

        // Format the collection into an associative array (id => 'Name -> Mobile')
        $formattedAgents = $agents->mapWithKeys(function ($agent) {
            return [$agent->id => "{$agent->name} -> ({$agent->mobile})"];
        });

        return response()->json($formattedAgents);
    }


    // public function updateOrderStatus(Request $request, Order $order)
    // {
    //     $request->validate([
    //         'status' => 'required|string',
    //     ]);
        
    //     // check if this user is a call agent
    //     if($request->user()->role === Role::CALL_AGENT->value && $request->status) {
    //         $order->update([
    //             'status' => $request->status,
    //         ]);
    
    //         return redirect()->back()->with('success', 'Order status updated successfully');
    //     }

    //     return redirect()->back()->with('error', 'Unable to update order status!');
    // }
    
    public function deleteOutcome(Outcome $outcome)
    {
        if ($outcome->is_default) {
            return redirect()->back()->with('error', 'Cannot delete default outcomes');
        }
    
        if ($outcome->user_id !== request()->user()->id) {
            return redirect()->back()->with('error', 'You can only delete your own outcomes');
        }
    
        $outcome->delete();
        return redirect()->back()->with('success', 'Outcome deleted successfully');
    }
    
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string',
            'description' => 'required|string',
            'category' => 'required|string',
        ]);
    
        WhatsAppTemplate::updateOrCreate([
            'user_id' => $request->user()->id,
            'name' => $validated['name']
        ],
        [
            'name' => $validated['name'],
            'message' => $validated['message'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'user_id' => $request->user()->id,
        ]);
    
        return redirect()->back()->with('success', 'Template added successfully');
    }
    
    public function deleteTemplate(WhatsappTemplate $template)
    {
        if ($template->is_default) {
            return redirect()->back()->with('error', 'Cannot delete default templates');
        }
    
        if ($template->user_id !== request()->user()->id) {
            return redirect()->back()->with('error', 'You can only delete your own templates');
        }
    
        $template->delete();
        return redirect()->back()->with('success', 'Template deleted successfully');
    }

}

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Models\Order;
// use App\Models\OrderCommunication;
// use Inertia\Inertia;

// class OrderCommunicationController extends Controller
// {
//     public function index(Request $request)
//     {
//         // Get orders for the authenticated call agent with recent communications
//         $query = Order::query()
//             ->where('call_agent_id', $request->user()->id) // Only show orders assigned to current agent
//             // ->whereHas('communications')
//             ->orderByDesc(
//                 OrderCommunication::select('created_at')
//                     ->whereColumn('order_id', 'orders.id')
//                     ->latest()
//                     ->take(1)
//             );
    
//         if ($request->has('search')) {
//             $query->where(function($q) use ($request) {
//                 $q->where('order_number', 'like', "%{$request->search}%")
//                 ->orWhere('full_name', 'like', "%{$request->search}%")
//                 ->orWhere('mobile', 'like', "%{$request->search}%")
//                 ->orWhere('email', 'like', "%{$request->search}%");
//             });
//         }
    
//         $orders = $query->paginate(20);

        
    
//         // If specific order is requested, load its communications
//         $selectedOrder = null;
//         $communications = collect();
        
//         if ($request->has('order_id')) {
//             $selectedOrder = Order::with(['marketer', 'callAgent', 'deliveryAgent'])
//                 ->where('call_agent_id', $request->user()->id) // Ensure the order belongs to this agent
//                 ->find($request->order_id);
                
//             if ($selectedOrder) {
//                 $communications = $selectedOrder
//                     ->communications()
//                     ->with('agent')
//                     ->orderBy('created_at', 'asc')
//                     ->get();
//             }
//         }
    
//         return Inertia::render('Orders/ChatIndex', [
//             'orders' => $orders,
//             'selectedOrder' => $selectedOrder,
//             'communications' => $communications,
//             'outcomes' => OrderCommunication::OUTCOMES,
//             'filters' => $request->only(['search']),
//         ]);
//     }

//     public function store(Request $request, Order $order)
//     {
//         $request->validate([
//             'content' => 'required_if:type,note,email',
//             'type' => 'required|in:call,email,note',
//             'outcome' => 'nullable|in:' . implode(',', OrderCommunication::OUTCOMES),
//             'labels' => 'nullable|array',
//         ]);

//         $order->communications()->create([
//             'agent_id' => $request->user()->id,
//             'type' => $request->type,
//             'content' => $request->content,
//             'outcome' => $request->outcome,
//             'labels' => $request->labels,
//         ]);

//         return redirect()->back()->with('success', 'Communication recorded');
//     }

//     public function updateLabels(Request $request, OrderCommunication $communication)
//     {
//         $request->validate([
//             'labels' => 'required|array',
//         ]);

//         $communication->update(['labels' => $request->labels]);

//         return response()->json(['success' => true]);
//     }
// }
