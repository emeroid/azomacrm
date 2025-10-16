import { Head, usePage, router, Link, useForm } from '@inertiajs/react';
import { useState, useEffect, useRef, useCallback } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ConversationList from './ConversationList';
import ChatArea from './ChatArea';
import ChatModals from './ChatModals';

export default function ChatIndex({ auth }) {
    const { orders, selectedOrder, communications, outcomes, filters, whatsappTemplates } = usePage().props;
    const { post, processing } = useForm();
    
    // Initialize state from props (filters from backend) or from localStorage for persistence
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
    const [isLoading, setIsLoading] = useState(false);
    const [showChat, setShowChat] = useState(() => {
        return selectedOrder ? (window.innerWidth >= 768 ? true : false) : false;
    });
    const [isMobile, setIsMobile] = useState(window.innerWidth < 768);

    // Modal states
    const [showTemplateModal, setShowTemplateModal] = useState(false);
    const [showStatusModal, setShowStatusModal] = useState(false);
    const [selectedStatus, setSelectedStatus] = useState('');
    const [selectedDeliveryId, setSelectedDeliveryId] = useState('');
    const [showOutcomeModal, setShowOutcomeModal] = useState(false);
    const [showTemplateCreateModal, setShowTemplateCreateModal] = useState(false);
    const [newOutcome, setNewOutcome] = useState('');
    const [deliveryAgents, setDeliveryAgents] = useState({});
    const [showOrderPreview, setShowOrderPreview] = useState(false);
    const [previewOrder, setPreviewOrder] = useState(null);
    const [isLoadingAgents, setIsLoadingAgents] = useState(false);

    // Pagination states - persist in localStorage
    const [allOrders, setAllOrders] = useState(() => {
        const saved = localStorage.getItem('chatAllOrders');
        return saved ? JSON.parse(saved) : (orders.data || []);
    });
    const [nextPageUrl, setNextPageUrl] = useState(() => {
        const saved = localStorage.getItem('chatNextPageUrl');
        return saved ? saved : orders.next_page_url;
    });
    const [loadingMore, setLoadingMore] = useState(false);
    const [currentPage, setCurrentPage] = useState(() => {
        const saved = localStorage.getItem('chatCurrentPage');
        return saved ? parseInt(saved) : 1;
    });

    // Track if we're in the middle of an order selection to prevent double loads
    const isSelectingOrderRef = useRef(false);

    // Search and filter debouncing
    const searchTimeoutRef = useRef(null);

    // Save pagination state to localStorage whenever it changes
    useEffect(() => {
        localStorage.setItem('chatAllOrders', JSON.stringify(allOrders));
        localStorage.setItem('chatNextPageUrl', nextPageUrl || '');
        localStorage.setItem('chatCurrentPage', currentPage.toString());
    }, [allOrders, nextPageUrl, currentPage]);

    // Save filters to localStorage whenever they change
    useEffect(() => {
        localStorage.setItem('chatFilters', JSON.stringify({
            search,
            status: statusFilter
        }));
    }, [search, statusFilter]);

    // Load filters from localStorage on component mount
    useEffect(() => {
        const savedFilters = localStorage.getItem('chatFilters');
        if (savedFilters) {
            try {
                const { search: savedSearch, status: savedStatus } = JSON.parse(savedFilters);
                if (savedSearch !== undefined) setSearch(savedSearch);
                if (savedStatus !== undefined) setStatusFilter(savedStatus);
            } catch (error) {
                console.error('Error loading saved filters:', error);
            }
        }
    }, []);

    // Effect for search and filter changes - ONLY trigger for actual filter changes
    useEffect(() => {
        // Skip if we're in the middle of selecting an order
        if (isSelectingOrderRef.current) {
            isSelectingOrderRef.current = false;
            return;
        }

        // Debounce search and filter
        if (searchTimeoutRef.current) {
            clearTimeout(searchTimeoutRef.current);
        }

        searchTimeoutRef.current = setTimeout(() => {
            console.log('Triggering search/filter update:', { search, statusFilter });
            
            router.get(route('orders.chat'), {
                search: search || '',
                status: statusFilter,
                order_id: selectedOrder?.id || ''
            }, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onSuccess: (page) => {
                    console.log('Search/filter success, updating orders');
                    setAllOrders(page.props.orders.data);
                    setNextPageUrl(page.props.orders.next_page_url);
                    setCurrentPage(1);
                }
            });
        }, 500);

        return () => {
            if (searchTimeoutRef.current) {
                clearTimeout(searchTimeoutRef.current);
            }
        };
    }, [search, statusFilter]); // Removed selectedOrder from dependencies

    useEffect(() => {
        const handleResize = () => {
            const mobile = window.innerWidth < 768;
            setIsMobile(mobile);
            if (!mobile && selectedOrder) {
                setShowChat(true);
            }
        };
        
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, [selectedOrder]);

    // FIXED: handleOrderSelect function with proper router call
    const handleOrderSelect = async (orderId) => {
        if (isLoading || orderId === selectedOrder?.id) return;
        
        setIsLoading(true);
        isSelectingOrderRef.current = true; // Mark that we're selecting an order

        // Set showChat immediately for a responsive feel on mobile
        if (isMobile) {
            setShowChat(true);
        }
        
        try {
            await router.get(route('orders.chat', { 
                order_id: orderId,
                search: search || '',
                status: statusFilter,
            }), {}, { // FIX: Added empty object as second parameter
                preserveState: true,
                preserveScroll: true,
                only: ['selectedOrder', 'communications'],
                onError: (error) => {
                    console.error('Order selection error:', error);
                    // If there's an error, hide the chat pane again on mobile
                    if (isMobile) {
                        setShowChat(false);
                    }
                },
                onFinish: () => {
                    setIsLoading(false);
                    isSelectingOrderRef.current = false;
                }
            });
        } catch (error) {
            console.error('Order selection failed:', error);
            setIsLoading(false);
            isSelectingOrderRef.current = false;
            if (isMobile) {
                setShowChat(false);
            }
        }
    };

    const loadMoreOrders = async () => {
        if (loadingMore || !nextPageUrl) return;

        setLoadingMore(true);
        try {
            const nextPage = currentPage + 1;
            
            console.log('Loading more orders, page:', nextPage);
            const response = await axios.get(route('orders.chat.load-more'), {
                params: {
                    search: search || '',
                    status: statusFilter,
                    page: nextPage
                }
            });

            console.log('Load More Response:', response.data);

            if (response.data.orders && response.data.orders.data) {
                const newOrders = response.data.orders.data;
                
                // Prevent duplicates by checking if orders already exist
                const existingIds = new Set(allOrders.map(order => order.id));
                const uniqueNewOrders = newOrders.filter(order => !existingIds.has(order.id));
                
                console.log('Adding new orders:', uniqueNewOrders.length);
                setAllOrders(prev => [...prev, ...uniqueNewOrders]);
                setNextPageUrl(response.data.orders.next_page_url);
                setCurrentPage(nextPage);
            }
        } catch (error) {
            console.error('Error loading more orders:', error);
            console.error('Error details:', error.response?.data);
        } finally {
            setLoadingMore(false);
        }
    };

    const clearAllFilters = () => {
        console.log('Clearing all filters');
        setSearch('');
        setStatusFilter('all');
        // Also reset pagination when clearing filters
        setAllOrders(orders.data || []);
        setNextPageUrl(orders.next_page_url);
        setCurrentPage(1);
    };

    const showOrderDetails = (order, e) => {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        setPreviewOrder(order);
        setShowOrderPreview(true);
    };

    const closeOrderPreview = () => {
        setShowOrderPreview(false);
        setPreviewOrder(null);
    };

    // Debug effect to log state changes
    useEffect(() => {
        console.log('Current state:', {
            allOrdersCount: allOrders.length,
            currentPage,
            nextPageUrl: nextPageUrl ? 'yes' : 'no',
            selectedOrder: selectedOrder?.id,
            search,
            statusFilter
        });
    }, [allOrders, currentPage, nextPageUrl, selectedOrder, search, statusFilter]);

    return (
        <AuthenticatedLayout auth={auth} header="Customer Communications">
            <div className="flex h-[calc(100vh-64px)] bg-white overflow-hidden">
                <ConversationList
                    orders={{ ...orders, data: allOrders }}
                    selectedOrder={selectedOrder}
                    search={search}
                    setSearch={setSearch}
                    statusFilter={statusFilter}
                    setStatusFilter={setStatusFilter}
                    showChat={showChat}
                    isMobile={isMobile}
                    setShowChat={setShowChat}
                    isLoading={isLoading}
                    handleOrderSelect={handleOrderSelect}
                    showOrderDetails={showOrderDetails}
                    loadMoreOrders={loadMoreOrders}
                    loadingMore={loadingMore}
                    hasMore={!!nextPageUrl}
                    clearAllFilters={clearAllFilters}
                    currentPage={currentPage}
                    totalLoadedOrders={allOrders.length}
                />
                
                <ChatArea
                    auth={auth}
                    selectedOrder={selectedOrder}
                    communications={communications}
                    showChat={showChat}
                    isMobile={isMobile}
                    setShowChat={setShowChat}
                    setShowStatusModal={setShowStatusModal}
                    setShowTemplateModal={setShowTemplateModal}
                    setShowOutcomeModal={setShowOutcomeModal}
                    showOrderDetails={showOrderDetails}
                    outcomes={outcomes}
                />
            </div>

            <ChatModals
                showStatusModal={showStatusModal}
                setShowStatusModal={setShowStatusModal}
                selectedStatus={selectedStatus}
                setSelectedStatus={setSelectedStatus}
                selectedDeliveryId={selectedDeliveryId}
                setSelectedDeliveryId={setSelectedDeliveryId}
                deliveryAgents={deliveryAgents}
                setDeliveryAgents={setDeliveryAgents}
                isLoadingAgents={isLoadingAgents}
                setIsLoadingAgents={setIsLoadingAgents}
                selectedOrder={selectedOrder}
                
                showOutcomeModal={showOutcomeModal}
                setShowOutcomeModal={setShowOutcomeModal}
                newOutcome={newOutcome}
                setNewOutcome={setNewOutcome}
                
                showTemplateCreateModal={showTemplateCreateModal}
                setShowTemplateCreateModal={setShowTemplateCreateModal}
                
                showTemplateModal={showTemplateModal}
                setShowTemplateModal={setShowTemplateModal}
                whatsappTemplates={whatsappTemplates}
                
                showOrderPreview={showOrderPreview}
                setShowOrderPreview={setShowOrderPreview}
                previewOrder={previewOrder}
                closeOrderPreview={closeOrderPreview}
                handleOrderSelect={handleOrderSelect}
            />
        </AuthenticatedLayout>
    );
}