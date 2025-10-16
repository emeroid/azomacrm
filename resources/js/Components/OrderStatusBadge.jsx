export default function OrderStatusBadge({ status }) {
    const statusClasses = {
        'pending': 'bg-gray-100 text-gray-800',
        'confirmed': 'bg-blue-100 text-blue-800',
        'processing': 'bg-yellow-100 text-yellow-800',
        'in_transit': 'bg-indigo-100 text-indigo-800',
        'delivered': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800',
        'returned': 'bg-purple-100 text-purple-800',
    };

    const statusText = {
        'pending': 'Pending',
        'confirmed': 'Confirmed',
        'processing': 'Processing',
        'in_transit': 'In Transit',
        'delivered': 'Delivered',
        'cancelled': 'Cancelled',
        'returned': 'Returned',
    };

    return (
        <span className={`text-xs px-2 py-0.5 rounded-full ${statusClasses[status] || 'bg-gray-100 text-gray-800'}`}>
            {statusText[status] || status.replace('_', ' ')}
        </span>
    );
}