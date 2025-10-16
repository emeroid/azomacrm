import { useState, useEffect, useRef, useCallback } from 'react';
import { router } from '@inertiajs/react';
import Avatar from '@/Components/Avatar';
import OrderStatusBadge from '@/Components/OrderStatusBadge';
import ActionMenu from '@/Components/ActionMenu';
import MessageInput from './MessageInput';

export default function ChatArea({
    auth,
    selectedOrder,
    communications,
    showChat,
    isMobile,
    setShowChat,
    setShowStatusModal,
    setShowTemplateModal,
    setShowOutcomeModal,
    showOrderDetails,
    outcomes
}) {
    const messagesEndRef = useRef(null);
    const messagesContainerRef = useRef(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    const scrollToBottom = useCallback(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, []);

    useEffect(() => {
        if (messagesContainerRef.current && selectedOrder) {
            messagesContainerRef.current.scrollTop = messagesContainerRef.current.scrollHeight;
            
            const observer = new MutationObserver(() => {
                messagesContainerRef.current.scrollTo({
                    top: messagesContainerRef.current.scrollHeight,
                    behavior: 'smooth'
                });
            });
            
            observer.observe(messagesContainerRef.current, { childList: true });
            return () => observer.disconnect();
        }
    }, [communications, selectedOrder]);

    const formatTime = (dateString) => {
        return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        const today = new Date();
        
        if (date.toDateString() === today.toDateString()) return 'Today';
        
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';
        
        return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
    };

    // Create order details message to show as first message
    const getOrderDetailsMessage = (order) => {
        if (!order) return null;

        const orderDetails = {
            id: 'order-details-system',
            type: 'system',
            content: 'order_details',
            created_at: order.created_at,
            order_data: order
        };

        return orderDetails;
    };

    // Combine order details with communications
    const getGroupedMessages = () => {
        if (!selectedOrder) return {};
        
        const orderDetailsMessage = getOrderDetailsMessage(selectedOrder);
        const allMessages = orderDetailsMessage ? [orderDetailsMessage, ...communications] : communications;
        
        return allMessages.reduce((groups, message) => {
            const date = formatDate(message.created_at);
            if (!groups[date]) groups[date] = [];
            groups[date].push(message);
            return groups;
        }, {});
    };

    const groupedMessages = getGroupedMessages();

    const getMessageClasses = (message) => {
        const baseClasses = "max-w-[85%] rounded-lg p-3";
        
        if (message.sender_id === auth.user.id) {
            return `${baseClasses} bg-blue-500 text-white self-end`;
        }
        else if (message.sender_id && message.sender_id !== auth.user.id) {
            return `${baseClasses} bg-purple-500 text-white self-start`;
        }
        else if (message.type === 'system') {
            return `${baseClasses} bg-gray-200 text-gray-800 self-center text-center`;
        }
        return `${baseClasses} bg-gray-100 text-gray-800 self-start`;
    };

    const getSenderName = (message) => {
        if (message.sender_id === auth.user.id) return 'You';
        if (message.sender?.first_name) return `${message.sender.first_name} ${message.sender.last_name}`;
        if (message.type === 'system') return 'System';
        return 'Customer';
    };

    const getSenderAvatar = (message) => {
        if (message.sender_id === auth.user.id) return <Avatar name={`${auth.user.first_name} ${auth.user.last_name}`} className="h-6 w-6" />;
        if (message.sender?.first_name) return <Avatar name={`${message.sender.first_name} ${message.sender.last_name}`} className="h-6 w-6" />;
        return <Avatar name="Customer" className="h-6 w-6" />;
    };

    const getOutcomeColor = (outcome) => {
        const colors = {
            'order_placed': 'bg-emerald-100 text-emerald-800',
            'order_cancelled': 'bg-rose-100 text-rose-800',
            'not_ready': 'bg-amber-100 text-amber-800',
            'interested': 'bg-sky-100 text-sky-800',
            'not_interested': 'bg-slate-100 text-slate-800',
            'follow_up': 'bg-violet-100 text-violet-800',
            'payment_issue': 'bg-orange-100 text-orange-800',
            'delivery_issue': 'bg-indigo-100 text-indigo-800',
        };
        return colors[outcome] || 'bg-gray-100 text-gray-800';
    };

    const handleCallAction = async () => {
        if (selectedOrder?.mobile && !isSubmitting) {
            setIsSubmitting(true);
            try {
                await router.post(route('orders.communications.store', selectedOrder.id), {
                    content: 'Call placed to customer',
                    type: 'call',
                    outcome: 'call_made',
                }, {
                    preserveScroll: true,
                });
                window.location.href = `tel:${selectedOrder.mobile}`;
            } finally {
                setIsSubmitting(false);
            }
        }
    };

    const copyOrderDetails = (order) => {
        if (!order || !navigator.clipboard) return;

        const details = [
            `Order #: ${order.order_number}`,
            `Customer: ${order.full_name}`,
            `Mobile: ${order.mobile}`,
            order.phone && `Phone: ${order.phone}`,
            order.email && `Email: ${order.email}`,
            `Address: ${order.address}`,
            `State: ${order.state}`,
            `Status: ${order.status.replace('_', ' ')}`,
            '',
            'Products:',
            ...order.items.map(item => 
                `- ${item.product.name}\n  Qty: ${item.quantity}\n  Price: ${item.unit_price}`
            )
        ].filter(Boolean).join('\n');
    
        navigator.clipboard.writeText(details)
            .catch(err => console.error('Failed to copy:', err));
    };

    // Render order details component
    const renderOrderDetails = (order) => (
        <div className="bg-white border border-gray-200 rounded-lg p-4 mb-4 mx-4">
            <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">Order Details</h3>
                <button
                    onClick={() => copyOrderDetails(order)}
                    className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                    Copy Details
                </button>
            </div>
            
            {/* Customer Information */}
            <div className="mb-4">
                <h4 className="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">CUSTOMER INFORMATION</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <p className="text-sm font-medium text-gray-900">{order.full_name}</p>
                        <p className="text-sm text-gray-600">{order.address}, {order.state}</p>
                    </div>
                    <div className="space-y-1">
                        <p className="text-sm text-gray-600">
                            <span className="font-medium">Mobile:</span> {order.mobile}
                        </p>
                        {order.phone && (
                            <p className="text-sm text-gray-600">
                                <span className="font-medium">Phone:</span> {order.phone}
                            </p>
                        )}
                        {order.email && (
                            <p className="text-sm text-gray-600">
                                <span className="font-medium">Email:</span> {order.email}
                            </p>
                        )}
                    </div>
                </div>
            </div>

            {/* Order Information */}
            <div className="mb-4">
                <h4 className="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">ORDER INFORMATION</h4>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p className="text-xs text-gray-500">Order Number</p>
                        <p className="text-sm font-medium text-gray-900">#{order.order_number}</p>
                    </div>
                    <div>
                        <p className="text-xs text-gray-500">Status</p>
                        <OrderStatusBadge status={order.status} />
                    </div>
                    <div>
                        <p className="text-xs text-gray-500">Date</p>
                        <p className="text-sm text-gray-900">{new Date(order.created_at).toLocaleDateString()}</p>
                    </div>
                    <div>
                        <p className="text-xs text-gray-500">Total</p>
                        <p className="text-sm font-semibold text-gray-900">
                            ₦{order.items.reduce((total, item) => total + Number(item.quantity) * Number(item.unit_price), 0).toLocaleString()}
                        </p>
                    </div>
                </div>
            </div>

            {/* Order Items */}
            <div>
                <h4 className="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">ORDER ITEMS</h4>
                <div className="space-y-2">
                    {order.items.map((item, index) => (
                        <div key={index} className="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                            <div className="flex-1">
                                <p className="text-sm font-medium text-gray-900">{item.product.name}</p>
                                <p className="text-xs text-gray-500">Qty: {item.quantity}</p>
                            </div>
                            <div className="text-right">
                                <p className="text-sm text-gray-600">₦{Number(item.unit_price).toLocaleString()}</p>
                                <p className="text-sm font-semibold text-gray-900">
                                    ₦{(Number(item.quantity) * Number(item.unit_price)).toLocaleString()}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );

    if (!selectedOrder) {
        return (
            <div className={`${(showChat || !isMobile) ? 'flex' : 'hidden'} md:flex flex-1 items-center justify-center bg-gray-50`}>
                <div className="text-center p-4 sm:p-6 max-w-xs">
                    <div className="mx-auto h-16 w-16 sm:h-20 sm:w-20 bg-blue-50 rounded-xl flex items-center justify-center mb-3 sm:mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8 sm:h-10 sm:w-10 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 className="text-sm sm:text-base font-semibold text-gray-900">Select a conversation</h3>
                    <p className="mt-1 text-xs sm:text-sm text-gray-500">Choose a customer to start chatting</p>
                </div>
            </div>
        );
    }

    return (
        <div className={`${(showChat || !isMobile) ? 'flex' : 'hidden'} md:flex flex-1 flex-col bg-gray-50 min-w-0`}>
            {/* Chat header */}
            <div className="bg-white p-2 sm:p-3 border-b border-gray-200 flex items-center justify-between sticky top-0 z-10">
                <div className="flex items-center space-x-1 sm:space-x-2 min-w-0">
                    {isMobile && (
                        <button 
                            onClick={() => {
                                setShowChat(false);
                                router.get(route('orders.chat'), {}, {
                                    preserveState: true,
                                    preserveScroll: true
                                });
                            }} 
                            className="p-1 sm:p-2 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            aria-label="Back to conversations"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clipRule="evenodd" />
                            </svg>
                        </button>
                    )}
                    <Avatar name={selectedOrder.full_name} className="h-8 w-8 sm:h-9 sm:w-9 rounded-lg flex-shrink-0" />
                    <div className="min-w-0">
                        <h2 className="text-sm sm:text-base font-medium truncate">{selectedOrder.full_name}</h2>
                        <div className="flex items-center space-x-1">
                            <span className="text-xs text-gray-500 truncate">#{selectedOrder.order_number}</span>
                            <OrderStatusBadge status={selectedOrder.status} className="text-[10px] sm:text-xs" />
                        </div>
                    </div>
                </div>
                <div className="flex space-x-1 sm:space-x-2">
                    <button
                        onClick={(e) => showOrderDetails(selectedOrder, e)}
                        className="ml-1 sm:ml-2 p-1 text-gray-400 hover:text-gray-600 focus:outline-none"
                        title="View order details"
                        aria-label="View order details"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                            <path fillRule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clipRule="evenodd" />
                        </svg>
                    </button>
                    <button 
                        onClick={() => setShowStatusModal(true)} 
                        className="px-2 py-1 sm:px-3 sm:py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500"
                        aria-label="Update order status"
                    >
                        <span className="hidden sm:inline">Status</span>
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 sm:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <button 
                        onClick={handleCallAction} 
                        className="p-1 sm:p-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        disabled={isSubmitting}
                        aria-label="Call customer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                        </svg>
                    </button>
                </div>
            </div>

            {/* Messages area */}
            <div className="flex-1 p-2 sm:p-3 overflow-y-auto" ref={messagesContainerRef}>

                {Object.entries(groupedMessages).map(([date, messages]) => (
                    <div key={date} className="mb-3">
                        <div className="relative mb-2">
                            <div className="absolute inset-0 flex items-center">
                                <div className="w-full border-t border-gray-200"></div>
                            </div>
                            <div className="relative flex justify-center">
                                <span className="bg-gray-50 px-2 text-xs text-gray-500">
                                    {date}
                                </span>
                            </div>
                        </div>
                        <div className="space-y-2">
                            {messages.map(message => (
                                <div key={message.id} className="flex flex-col">
                                    {/* Order Details Message */}
                                    {message.content === 'order_details' && message.order_data && (
                                        <div className="flex justify-center">
                                            {renderOrderDetails(message.order_data)}
                                        </div>
                                    )}
                                    
                                    {/* Regular Messages */}
                                    {message.content !== 'order_details' && (
                                        <>
                                            {message.type !== 'system' && (
                                                <div className={`flex items-center mb-0.5 ${message.sender_id === auth.user.id ? 'justify-end' : 'justify-start'}`}>
                                                    {message.sender_id !== auth.user.id && getSenderAvatar(message)}
                                                    <span className={`text-xs mx-1 sm:mx-2 ${message.sender_id === auth.user.id ? 'text-gray-500' : 'text-gray-600'}`}>
                                                        {getSenderName(message)}
                                                    </span>
                                                    {message.sender_id === auth.user.id && getSenderAvatar(message)}
                                                </div>
                                            )}
                                            
                                            <div className={`flex ${message.sender_id === auth.user.id ? 'justify-end' : 'justify-start'}`}>
                                                <div className={`max-w-[90%] sm:max-w-[85%] rounded-lg p-2 sm:p-3 ${getMessageClasses(message)}`}>
                                                    {message.type === 'call' ? (
                                                        <div className="flex items-center space-x-1 sm:space-x-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                                            </svg>
                                                            <span className="text-sm">Call with customer</span>
                                                        </div>
                                                    ) : message.type === 'whatsapp' ? (
                                                        <div className="flex items-start space-x-1 sm:space-x-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                            </svg>
                                                            <p className="text-sm whitespace-pre-wrap break-words">{message.content}</p>
                                                        </div>
                                                    ) : (
                                                        <p className="text-sm whitespace-pre-wrap break-words">{message.content}</p>
                                                    )}
                                                    
                                                    <div className={`flex justify-between items-center mt-1 text-[10px] sm:text-xs ${
                                                        message.sender_id === auth.user.id 
                                                            ? 'text-blue-100' 
                                                            : 'text-gray-500'
                                                    }`}>
                                                        <span>{formatTime(message.created_at)}</span>
                                                        {message.outcome && (
                                                            <span className={`px-1 py-0.5 rounded-full ${
                                                                getOutcomeColor(message.outcome)
                                                            }`}>
                                                                {message.outcome.replace('_', ' ')}
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
                <div ref={messagesEndRef} />
            </div>

            {/* Message input */}
            <MessageInput
                selectedOrder={selectedOrder}
                setShowTemplateModal={setShowTemplateModal}
                setShowOutcomeModal={setShowOutcomeModal}
                setShowStatusModal={setShowStatusModal}
                outcomes={outcomes}
                handleCallAction={handleCallAction}
            />
        </div>
    );
}