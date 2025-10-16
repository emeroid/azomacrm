import { useState, useCallback } from 'react';
import { useForm, router } from '@inertiajs/react';
import ActionMenu from '@/Components/ActionMenu';

export default function MessageInput({
    selectedOrder,
    setShowTemplateModal,
    setShowOutcomeModal,
    setShowStatusModal,
    outcomes,
    handleCallAction
}) {
    const [formData, setFormData] = useState({
        content: '',
        type: 'note',
        outcome: null,
        outcome_details: {},
        labels: [],
        whatsapp_template: null,
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    const resetForm = useCallback(() => {
        setFormData(prev => ({
            ...prev,
            content: '',
            type: 'note',
            outcome: null,
            outcome_details: {},
            whatsapp_template: null,
        }));
    }, []);

    const handleContentChange = (e) => {
        setFormData(prev => ({
            ...prev,
            content: e.target.value
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (isSubmitting || !formData.content.trim()) return;
        
        setIsSubmitting(true);
        try {
            if(formData.outcome === 'whatsapp_template_sent') {
                return initiateWhatsApp();
            }

            const response = await router.post(
                route('orders.communications.store', selectedOrder.id), 
                formData,
                {
                    preserveScroll: true,
                    onError: () => {
                        setIsSubmitting(false);
                    }
                }
            );
            
            if (!response?.error) {
                resetForm();
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    const initiateWhatsApp = async (message = null) => {
        if (selectedOrder?.mobile && !isSubmitting) {
            setIsSubmitting(true);
            try {
                const phone = selectedOrder.mobile;
                const text = message || formData.content;
                
                if (text) {
                    await router.post(
                        route('orders.communications.store', selectedOrder.id), 
                        {
                            content: `${text}`,
                            type: 'whatsapp',
                            outcome: 'whatsapp_template_sent',
                            whatsapp_template: true,
                        },
                        { preserveScroll: true }
                    );

                    const whatsappUrl = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
                    window.open(whatsappUrl, '_blank');
                }
            } finally {
                setIsSubmitting(false);
                resetForm();
            }
        }
    };

    const handleWhatsAppAction = async () => {
        if (selectedOrder?.mobile && !isSubmitting) {
            setIsSubmitting(true);
            try {
                await router.post(route('orders.communications.store', selectedOrder.id), {
                    content: 'WhatsApp conversation started',
                    type: 'whatsapp',
                    outcome: 'whatsapp_initiated',
                }, {
                    preserveScroll: true,
                });

                const phone = selectedOrder.mobile;
                window.open(`https://wa.me/${phone}`, '_blank');
            } finally {
                setIsSubmitting(false);
                resetForm();
            }
        }
    };

    return (
        <div className="bg-white p-2 sm:p-3 border-t border-gray-200 sticky bottom-0">
            <form onSubmit={handleSubmit} className="space-y-1 sm:space-y-2">
                <div className="flex items-center space-x-1 sm:space-x-2">
                    <ActionMenu
                        onCall={handleCallAction}
                        onWhatsApp={handleWhatsAppAction}
                        onTemplate={() => setShowTemplateModal(true)}
                        onOutcome={(outcome) => {
                            if (outcome) {
                                setFormData({
                                    content: `Outcome updated to: ${outcome.name}`,
                                    type: 'note',
                                    outcome: outcome.key
                                });
                                handleSubmit(new Event('submit'));
                            } else {
                                setShowOutcomeModal(true);
                            }
                        }}
                        onStatusUpdate={() => setShowStatusModal(true)}
                        outcomes={outcomes}
                    />
                    <div className="flex-1 relative min-w-0">
                        <textarea
                            rows={1}
                            value={formData.content}
                            onChange={handleContentChange}
                            placeholder="Type a message..."
                            className="block w-full px-2 py-1 sm:px-3 sm:py-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onKeyDown={(e) => {
                                if (e.key === 'Enter' && !e.shiftKey) {
                                    e.preventDefault();
                                    handleSubmit(e);
                                }
                            }}
                        />
                    </div>
                    <button
                        type="submit"
                        disabled={isSubmitting || !formData.content.trim()}
                        className={`p-1 sm:p-2 rounded-lg text-white focus:outline-none focus:ring-2 ${
                            isSubmitting || !formData.content.trim()
                                ? 'bg-gray-300 cursor-not-allowed'
                                : 'bg-blue-500 hover:bg-blue-600 focus:ring-blue-500'
                        }`}
                        aria-label="Send message"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clipRule="evenodd" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    );
}