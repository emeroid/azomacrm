<?php

namespace App\Exports;

use App\Enums\Role;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected $startDate,
        protected $endDate,
        protected ?string $userType = null,   // marketer_id | call_agent_id | delivery_agent_id
        protected ?int $userId = null,
        protected $status = null               // chosen status dynamically
    ) {}

    public function collection()
    {
        $user = Auth::user();

        $query = Order::query()
            ->where('status', $this->status)
            ->whereBetween('updated_at', [$this->startDate, $this->endDate])
            ->with(['items.product', 'marketer', 'callAgent', 'deliveryAgent']);

        /**
         * Role-based filtering
         */
        switch ($user->role) {
            case Role::MARKETER->value:
                $query->where('marketer_id', $user->id);
                break;

            case Role::CALL_AGENT->value:
                $query->where('call_agent_id', $user->id);
                break;

            case Role::DELIVERY_AGENT->value:
                $query->where('delivery_agent_id', $user->id);
                break;

            case Role::MANAGER->value:
            case Role::ADMIN->value:
                if ($this->userType && $this->userId) {
                    $query->where($this->userType, $this->userId);
                }
                break;
        }

        // 🧠 CRITICAL: must return the query results!
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Order #',
            'Customer',
            'Mobile',
            'State',
            'Agent',
            'Products',
            'Unit Price',
            'Total Amount',
            ucfirst(str_replace('_', ' ', $this->status)) . ' At',
        ];
    }

    public function map($order): array
    {
        $products = $order->items->map(fn($i) => "{$i->product->name} (x{$i->quantity})")->implode(', ');
        $agentName =  User::find($this->userId ?? Auth::user()->id)->full_name;

        return [
            $order->order_number,
            $order->full_name,
            $order->mobile,
            $order->state,
            $agentName,
            $products,
            $order->items->sum('unit_price'),
            $order->items->sum('total_price'),
            $order->updated_at->format('Y-m-d H:i'),
        ];
    }
}
