import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import TextInput from '@/Components/TextInput';
import OrderStatusBadge from '@/Components/OrderStatusBadge';
import TemplateModal from '@/Components/TemplateModal';

export default function ChatModals({
    showStatusModal,
    setShowStatusModal,
    selectedStatus,
    setSelectedStatus,
    selectedDeliveryId,
    setSelectedDeliveryId,
    deliveryAgents,
    setDeliveryAgents,
    isLoadingAgents,
    setIsLoadingAgents,
    selectedOrder,
    
    showOutcomeModal,
    setShowOutcomeModal,
    newOutcome,
    setNewOutcome,
    
    showTemplateCreateModal,
    setShowTemplateCreateModal,
    
    showTemplateModal,
    setShowTemplateModal,
    whatsappTemplates,
    
    showOrderPreview,
    setShowOrderPreview,
    previewOrder,
    closeOrderPreview,
    handleOrderSelect
}) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [newTemplate, setNewTemplate] = useState({
        name: '',
        message: '',
        description: '',
        category: ''
    });

    useEffect(() => {
        if (showStatusModal && Object.keys(deliveryAgents).length === 0) {
            setIsLoadingAgents(true);
            axios.get('/delivery-agents')
                .then(response => {
                    setDeliveryAgents(response.data);
                })
                .catch(error => {
                    console.error("Error fetching delivery agents:", error);
                })
                .finally(() => {
                    setIsLoadingAgents(false);
                });
        }
    }, [showStatusModal, deliveryAgents, setDeliveryAgents, setIsLoadingAgents]);

    const handleStatusUpdate = async () => {
        if (selectedStatus && selectedOrder && !isSubmitting) {
            setIsSubmitting(true);
            try {
                await router.post(
                    route('status.update', selectedOrder.id), 
                    { status: selectedStatus, delivery_agent_id: selectedDeliveryId },
                    {
                        onSuccess: () => {
                            router.post(route('orders.communications.store', selectedOrder.id), {
                                content: `Order status updated to: ${selectedStatus}`,
                                type: 'note',
                                outcome: 'status_updated',
                            });
                            setShowStatusModal(false);
                            setSelectedStatus('');
                        },
                        preserveScroll: true,
                        preserveState: true,
                    }
                );
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

    return (
        <>
            {/* Status Update Modal */}
            {showStatusModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-lg w-full max-w-sm shadow-xl">
                        <div className="p-4 border-b border-gray-200">
                            <h3 className="font-medium">Update Order Status</h3>
                        </div>
                        <div className="p-4">
                            <select
                                value={selectedStatus}
                                onChange={(e) => setSelectedStatus(e.target.value)}
                                className="w-full p-2 border border-gray-300 rounded mb-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Select status</option>
                                <option value="not_ready">Not Ready</option>
                                <option value="not_interested">Not Interested</option>
                                <option value="not_reachable">Not Reachable</option>
                                <option value="phone_switched_off">Phone Switched Off</option>
                                <option value="travelled">Travelled</option>
                                <option value="not_available">Not Available</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            
                            {selectedStatus === 'confirmed' && (
                                <div className="mt-4">
                                    <label htmlFor="delivery_agent_id" className="block text-sm font-medium text-gray-700 mb-1">Assign Delivery Agent</label>
                                    <select
                                        id="delivery_agent_id"
                                        name="delivery_agent_id"
                                        value={selectedDeliveryId}
                                        onChange={(e) => setSelectedDeliveryId(e.target.value)}
                                        className="w-full p-2 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        disabled={isLoadingAgents}
                                    >
                                        <option value="">{isLoadingAgents ? 'Loading agents...' : 'Select an agent'}</option>
                                        {Object.entries(deliveryAgents).map(([id, name]) => (
                                            <option key={id} value={id}>{name}</option>
                                        ))}
                                    </select>
                                </div>
                            )}
                        </div>
                        <div className="flex justify-end space-x-2 p-4 border-t border-gray-200">
                            <button 
                                onClick={() => setShowStatusModal(false)}
                                className="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                Cancel
                            </button>
                            <button
                                onClick={handleStatusUpdate}
                                disabled={!selectedStatus || isSubmitting}
                                className={`px-4 py-2 text-sm text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    !selectedStatus || isSubmitting ? 'bg-blue-300' : 'bg-blue-500 hover:bg-blue-600'
                                }`}
                            >
                                {isSubmitting ? 'Updating...' : 'Update'}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Outcome Modal */}
            {showOutcomeModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-lg w-full max-w-sm shadow-xl">
                        <div className="p-4 border-b border-gray-200">
                            <h3 className="font-medium">Add New Outcome</h3>
                        </div>
                        <div className="p-4">
                            <TextInput
                                value={newOutcome}
                                onChange={(e) => setNewOutcome(e.target.value)}
                                placeholder="Enter outcome name"
                                className="w-full mb-4"
                            />
                        </div>
                        <div className="flex justify-end space-x-2 p-4 border-t border-gray-200">
                            <button 
                                onClick={() => setShowOutcomeModal(false)}
                                className="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                Cancel
                            </button>
                            <button 
                                onClick={() => {
                                    router.post(route('outcomes.store'), {
                                        name: newOutcome
                                    }, {
                                        onSuccess: () => {
                                            setNewOutcome('');
                                            setShowOutcomeModal(false);
                                        }
                                    });
                                }}
                                disabled={!newOutcome.trim()}
                                className={`px-4 py-2 text-sm text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    !newOutcome.trim() ? 'bg-blue-300' : 'bg-blue-500 hover:bg-blue-600'
                                }`}
                            >
                                Add Outcome
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Template Create Modal */}
            {showTemplateCreateModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-lg w-full max-w-md shadow-xl overflow-y-auto max-h-[90vh]">
                        <div className="p-4 border-b border-gray-200">
                            <h3 className="font-medium">Create New Template</h3>
                        </div>
                        <div className="p-4 space-y-3">
                            <TextInput
                                label="Template Name"
                                value={newTemplate.name}
                                onChange={(e) => setNewTemplate({...newTemplate, name: e.target.value})}
                                placeholder="e.g. Order Confirmation"
                                className="w-full"
                            />
                            <TextInput
                                label="Category"
                                value={newTemplate.category}
                                onChange={(e) => setNewTemplate({...newTemplate, category: e.target.value})}
                                placeholder="e.g. order_confirmed"
                                className="w-full"
                            />
                            <TextInput
                                label="Description"
                                value={newTemplate.description}
                                onChange={(e) => setNewTemplate({...newTemplate, description: e.target.value})}
                                placeholder="Short description"
                                className="w-full"
                            />
                            <TextInput
                                label="Message"
                                value={newTemplate.message}
                                onChange={(e) => setNewTemplate({...newTemplate, message: e.target.value})}
                                placeholder="Template message"
                                multiline
                                rows={4}
                                className="w-full"
                            />
                        </div>
                        <div className="flex justify-end space-x-2 p-4 border-t border-gray-200">
                            <button 
                                onClick={() => setShowTemplateCreateModal(false)}
                                className="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                Cancel
                            </button>
                            <button 
                                onClick={() => {
                                    router.post(route('templates.store'), newTemplate, {
                                        onSuccess: () => {
                                            setNewTemplate({
                                                name: '',
                                                message: '',
                                                description: '',
                                                category: ''
                                            });
                                            setShowTemplateCreateModal(false);
                                        }
                                    });
                                }}
                                disabled={!newTemplate.name || !newTemplate.message}
                                className={`px-4 py-2 text-sm text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    !newTemplate.name || !newTemplate.message ? 'bg-blue-300' : 'bg-blue-500 hover:bg-blue-600'
                                }`}
                            >
                                Save Template
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Template Modal */}
            {showTemplateModal && (
                <TemplateModal 
                    order={selectedOrder}
                    templates={whatsappTemplates}
                    onClose={() => setShowTemplateModal(false)}
                    onSend={(message) => {
                        // This would be handled by the parent component
                    }}
                    onAddNew={() => {
                        setShowTemplateModal(false);
                        setShowTemplateCreateModal(true);
                    }}
                    onDelete={(templateId) => {
                        if (confirm('Are you sure you want to delete this template?')) {
                            router.delete(route('templates.destroy', templateId), {
                                preserveScroll: true,
                                onSuccess: () => {
                                    router.reload({ only: ['whatsappTemplates'] });
                                }
                            });
                        }
                    }}
                />
            )}

            {/* Order Preview Modal */}
            {showOrderPreview && previewOrder && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-xl">
                        <div className="p-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 className="font-medium">Order Details - #{previewOrder.order_number}</h3>
                            <button 
                                onClick={closeOrderPreview}
                                className="p-1 text-gray-400 hover:text-gray-600 focus:outline-none"
                                aria-label="Close"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        
                        <div className="p-6">
                            {/* Customer Information */}
                            <div className="mb-6">
                                <h4 className="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">CUSTOMER INFORMATION</h4>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-sm font-medium text-gray-900">{previewOrder.full_name}</p>
                                        <p className="text-sm text-gray-600">{previewOrder.address}, {previewOrder.state}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-600">Mobile: {previewOrder.mobile}</p>
                                        {previewOrder.phone && <p className="text-sm text-gray-600">Phone: {previewOrder.phone}</p>}
                                        {previewOrder.email && <p className="text-sm text-gray-600">Email: {previewOrder.email}</p>}
                                    </div>
                                </div>
                            </div>

                            {/* Order Information */}
                            <div className="mb-6">
                                <h4 className="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">ORDER INFORMATION</h4>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p className="text-xs text-gray-500">Order Number</p>
                                        <p className="text-sm font-medium text-gray-900">#{previewOrder.order_number}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500">Status</p>
                                        <OrderStatusBadge status={previewOrder.status} />
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-500">Date</p>
                                        <p className="text-sm text-gray-900">{new Date(previewOrder.created_at).toLocaleDateString()}</p>
                                    </div>
                                </div>
                            </div>

                            {/* Order Items */}
                            <div className="mb-6">
                                <h4 className="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">ORDER ITEMS</h4>
                                <div className="overflow-x-auto">
                                    <div className="min-w-full inline-block align-middle">
                                        <div className="border border-gray-200 rounded-lg overflow-hidden">
                                            <table className="min-w-full divide-y divide-gray-200">
                                                <thead className="bg-gray-50">
                                                    <tr>
                                                        <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Product</th>
                                                        <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Quantity</th>
                                                        <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Unit Price</th>
                                                        <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="bg-white divide-y divide-gray-200">
                                                    {previewOrder.items.map((item, index) => (
                                                        <tr key={index}>
                                                            <td className="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">{item.product.name}</td>
                                                            <td className="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{item.quantity}</td>
                                                            <td className="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">₦{Number(item.unit_price).toLocaleString()}</td>
                                                            <td className="px-4 py-3 text-sm font-semibold text-gray-900 whitespace-nowrap">₦{(Number(item.quantity) * Number(item.unit_price)).toLocaleString()}</td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                                <tfoot className="bg-gray-50">
                                                    <tr>
                                                        <td colSpan="3" className="px-4 py-3 text-sm font-medium text-gray-900 text-right whitespace-nowrap">Grand Total</td>
                                                        <td className="px-4 py-3 text-sm font-bold text-gray-900 whitespace-nowrap">
                                                            ₦{previewOrder.items.reduce((total, item) => total + Number(item.quantity) * Number(item.unit_price), 0).toLocaleString()}
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Action Buttons */}
                            <div className="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                                <button
                                    onClick={() => {
                                        copyOrderDetails(previewOrder);
                                        closeOrderPreview();
                                    }}
                                    className="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                                >
                                    Copy Details
                                </button>
                                <button
                                    onClick={() => {
                                        closeOrderPreview();
                                        handleOrderSelect(previewOrder.id);
                                    }}
                                    className="px-4 py-2 text-sm text-white bg-blue-500 hover:bg-blue-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    Open Chat
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}