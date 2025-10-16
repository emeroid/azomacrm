import { Link } from '@inertiajs/react';
import { useState, useRef, useCallback, useEffect } from 'react';
import Avatar from '@/Components/Avatar';
import OrderStatusBadge from '@/Components/OrderStatusBadge';

export default function ConversationList({
    orders,
    selectedOrder,
    search,
    setSearch,
    statusFilter,
    setStatusFilter,
    showChat,
    isMobile,
    setShowChat,
    isLoading,
    handleOrderSelect,
    showOrderDetails,
    loadMoreOrders,
    loadingMore,
    hasMore,
    clearAllFilters,
    currentPage,
    totalLoadedOrders
}) {
    const conversationListRef = useRef(null);

    const formatTime = (dateString) => {
        return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
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

    // Status options for filter
    const statusOptions = [
        { value: 'all', label: 'All Status' },
        { value: 'processing', label: 'Processing' },
        { value: 'confirmed', label: 'Confirmed' },
        { value: 'cancelled', label: 'Cancelled' },
        { value: 'scheduled', label: 'Scheduled' },
        { value: 'not_ready', label: 'Not Ready' },
        { value: 'not_interested', label: 'Not Interested' },
        { value: 'not_reachable', label: 'Not Reachable' },
        { value: 'phone_switched_off', label: 'Phone Switched Off' },
        { value: 'travelled', label: 'Travelled' },
        { value: 'not_available', label: 'Not Available' },
    ];

    // Check if any filters are active
    const hasActiveFilters = search || statusFilter !== 'all';

    return (
        <div 
            className={`${(!showChat || !isMobile) ? 'flex' : 'hidden'} md:flex w-full md:w-80 lg:w-96 bg-white flex-col border-r border-gray-200 transition-all duration-200 ease-in-out`}
        >
            {/* Search and Filter Bar */}
            <div className="p-2 sm:p-3 border-b border-gray-200 sticky top-0 bg-white z-10 space-y-2">
                <div className="flex items-center space-x-2">
                    {isMobile && showChat && (
                        <button 
                            onClick={() => setShowChat(false)} 
                            className="p-1 sm:p-2 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            aria-label="Back to conversations"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clipRule="evenodd" />
                            </svg>
                        </button>
                    )}
                    <div className="relative flex-1">
                        <input
                            type="text"
                            placeholder="Search orders..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <div className="absolute left-2.5 top-2.5 text-gray-400">
                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                {/* Status Filter */}
                <div className="flex items-center space-x-2">
                    <select
                        value={statusFilter}
                        onChange={(e) => setStatusFilter(e.target.value)}
                        className="w-full p-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        {statusOptions.map(option => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    
                    {/* Clear Filters Button */}
                    {hasActiveFilters && (
                        <button
                            onClick={clearAllFilters}
                            className="px-3 py-2 text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 whitespace-nowrap"
                            title="Clear all filters"
                        >
                            Clear
                        </button>
                    )}
                </div>

                {/* Active Filters Indicator */}
                {hasActiveFilters && (
                    <div className="flex items-center justify-between text-xs text-gray-600 bg-blue-50 px-2 py-1 rounded">
                        <span>Active filters:</span>
                        <div className="flex items-center space-x-1">
                            {search && (
                                <span className="bg-blue-100 px-2 py-1 rounded">Search: "{search}"</span>
                            )}
                            {statusFilter !== 'all' && (
                                <span className="bg-blue-100 px-2 py-1 rounded">
                                    Status: {statusOptions.find(opt => opt.value === statusFilter)?.label}
                                </span>
                            )}
                        </div>
                    </div>
                )}
            </div>

            {/* Conversation list */}
            <div 
                className="flex-1 overflow-y-auto"
                ref={conversationListRef}
            >
                {isLoading ? (
                    <div className="flex justify-center items-center h-full">
                        <div className="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
                    </div>
                ) : (
                    <>
                        {orders.data.length === 0 ? (
                            <div className="flex flex-col items-center justify-center h-full text-gray-500 p-4">
                                <svg className="h-12 w-12 mb-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p className="text-sm text-center">
                                    {hasActiveFilters 
                                        ? 'No orders match your filters' 
                                        : 'No orders found'
                                    }
                                </p>
                                {hasActiveFilters && (
                                    <button
                                        onClick={clearAllFilters}
                                        className="mt-2 px-4 py-2 text-xs text-blue-600 hover:text-blue-800"
                                    >
                                        Clear filters
                                    </button>
                                )}
                            </div>
                        ) : (
                            <>
                                {/* Results count */}
                                <div className="px-3 py-2 text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                                    Loaded {totalLoadedOrders} orders
                                    {hasActiveFilters && ' (filtered)'}
                                    {currentPage > 1 && ` • Page ${currentPage}`}
                                </div>

                                {orders.data.map((order) => (
                                    <Link
                                        key={order.id}
                                        href={route('orders.chat', { 
                                            order_id: order.id,
                                            search: search || '',
                                            status: statusFilter
                                        })}
                                        className={`flex items-center p-2 sm:p-3 border-b border-gray-100 transition-colors duration-150 ${
                                            selectedOrder?.id === order.id 
                                                ? 'bg-blue-50 border-blue-200' 
                                                : 'hover:bg-gray-50'
                                        }`}
                                        onClick={(e) => {
                                            // if (isMobile) {
                                                e.preventDefault();
                                                handleOrderSelect(order.id);
                                            // }
                                        }}
                                        preserveScroll
                                    >
                                        <Avatar name={order.full_name} className="h-8 w-8 sm:h-9 sm:w-9 rounded-lg" />
                                        <div className="ml-2 sm:ml-3 flex-1 min-w-0">
                                            <div className="flex justify-between">
                                                <h3 className="text-xs sm:text-sm font-medium truncate">{order.full_name}</h3>
                                                
                                                <span className="text-xs text-gray-500 whitespace-nowrap ml-1 sm:ml-2">
                                                    {order.latest_communication ? formatTime(order.latest_communication.created_at) : ''}
                                                </span>
                                            </div>
                                            <div className="flex items-center mt-0.5 sm:mt-1 space-x-1">
                                                <OrderStatusBadge status={order.status} className="text-[10px] sm:text-xs" />
                                                {order.latest_communication?.outcome && (
                                                    <span className={`text-[9px] sm:text-[10px] px-1 py-0.5 rounded-full ${getOutcomeColor(order.latest_communication.outcome)}`}>
                                                        {order.latest_communication.outcome.replace('_', ' ')}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        {order.unread_count > 0 && (
                                            <span className="ml-1 sm:ml-2 bg-blue-500 text-white text-[10px] sm:text-xs rounded-full h-4 w-4 sm:h-5 sm:w-5 flex items-center justify-center">
                                                {order.unread_count}
                                            </span>
                                        )}
                                    </Link>
                                ))}
                                
                                {/* Load More Button */}
                                {hasMore && (
                                    <div className="p-4 border-t border-gray-100">
                                        <button
                                            onClick={loadMoreOrders}
                                            disabled={loadingMore}
                                            className={`w-full py-2 px-4 rounded-lg border border-gray-300 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                                loadingMore 
                                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                                                    : 'bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400'
                                            }`}
                                        >
                                            {loadingMore ? (
                                                <div className="flex items-center justify-center">
                                                    <div className="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-blue-500 mr-2"></div>
                                                    Loading more orders...
                                                </div>
                                            ) : (
                                                'Load More Orders'
                                            )}
                                        </button>
                                    </div>
                                )}
                                
                                {/* End of list message */}
                                {!hasMore && orders.data.length > 0 && (
                                    <div className="text-center py-4 text-xs text-gray-500 border-t border-gray-100">
                                        {hasActiveFilters 
                                            ? "You've reached the end of filtered results" 
                                            : "You've reached the end of the list"
                                        }
                                    </div>
                                )}
                            </>
                        )}
                    </>
                )}
            </div>
        </div>
    );
}