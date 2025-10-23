import React, { useState, useEffect, useCallback } from 'react';
import AuthenticatedLayout from '@/Layouts/WaAuthLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';

// --- NEW Search/Debounce Hook ---
const useDebounce = (value, delay) => {
    const [debouncedValue, setDebouncedValue] = useState(value);

    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedValue(value);
        }, delay);

        return () => {
            clearTimeout(handler);
        };
    }, [value, delay]);

    return debouncedValue;
};


export default function Index({ auth, messages, devices, orderStatuses, userFormTemplates, supportedPlaceholders }) {
    
    // --- State for dynamic form field validation (FORM_SUBMISSION) ---
    const [formCheckResult, setFormCheckResult] = useState({
        success: null,
        message: '',
        potentialFields: [],
        isMultiple: false,
    });
    
    // --- State for Enhanced Filtering (New) ---
    const [orderSearchTerm, setOrderSearchTerm] = useState('');
    const [orderSearchList, setOrderSearchList] = useState([]);
    const [submissionSearchTerm, setSubmissionSearchTerm] = useState('');
    const [submissionSearchList, setSubmissionSearchList] = useState([]);
    
    const debouncedOrderSearch = useDebounce(orderSearchTerm, 500);
    const debouncedSubmissionSearch = useDebounce(submissionSearchTerm, 500);

    const [activeTab, setActiveTab] = useState('create');
    
    // --- Form State Management ---
    const { data, setData, post, processing, errors, reset, setError, clearErrors } = useForm({
        device_id: '',
        action: 'ORDER',
        // ORDER fields
        order_criteria_type: 'ALL', 
        status: '', // Used for STATUS and SPECIFIC_ORDER
        target_id: '', // Used for SPECIFIC_ORDER ID or SPECIFIC_SUBMISSION ID (NEW)
        // FORM_SUBMISSION fields
        form_template_id: '',
        whatsapp_field_name: '',
        submission_criteria_type: 'ALL', // NEW
        // Message fields
        message: '',
        send_at: '',
    });

    // --- EFFECT 1: Reset Dynamic Fields when 'action' changes ---
    useEffect(() => {
        clearErrors();
        setData(prevData => ({
            ...prevData,
            // Reset ORDER fields
            order_criteria_type: 'ALL',
            status: '',
            // Reset FORM_SUBMISSION fields
            form_template_id: '',
            whatsapp_field_name: '',
            submission_criteria_type: 'ALL',
            // Reset common specific target ID
            target_id: '',
        }));
        setFormCheckResult({ success: null, message: '', potentialFields: [], isMultiple: false });
        setOrderSearchTerm('');
        setOrderSearchList([]);
        setSubmissionSearchTerm('');
        setSubmissionSearchList([]);
    }, [data.action]);
    
    // --- EFFECT 2: Check Form Template fields when 'form_template_id' changes ---
    useEffect(() => {
        if (data.action === 'FORM_SUBMISSION' && data.form_template_id) {
            setData('whatsapp_field_name', '');
            setFormCheckResult({ success: null, message: 'Checking form fields...', potentialFields: [], isMultiple: false });
            
            clearErrors('form_template_id');
            
            axios.get(route('scheduler.potential-fields', data.form_template_id))
                .then(response => {
                    const fields = response.data.fields || [];
                    const isMultiple = fields.length > 1;

                    setFormCheckResult({
                        success: true,
                        message: response.data.message,
                        potentialFields: fields,
                        isMultiple: isMultiple,
                    });
                    
                    if (fields.length === 1) {
                         setData('whatsapp_field_name', fields[0].name);
                    }
                })
                .catch(error => {
                    const errorMessage = error.response?.data?.message || 'Failed to check form fields. Network error.';
                    setFormCheckResult({
                        success: false,
                        message: errorMessage,
                        potentialFields: [],
                        isMultiple: false,
                    });
                });
        } else {
            setFormCheckResult({ success: null, message: '', potentialFields: [], isMultiple: false });
            setData('whatsapp_field_name', '');
        }
    }, [data.action, data.form_template_id]);
    
    // --- EFFECT 3: Fetch Orders on Status/Search Change (ORDER) ---
    useEffect(() => {
        if (data.action === 'ORDER' && data.order_criteria_type !== 'ALL' && data.status && debouncedOrderSearch !== null) {
            axios.get(route('scheduler.search-orders', { status: data.status, search: debouncedOrderSearch }))
                .then(response => {
                    setOrderSearchList(response.data);
                })
                .catch(error => {
                    console.error("Order search failed:", error);
                    setOrderSearchList([]);
                });
        } else {
            setOrderSearchList([]);
            // When criteria changes away from SPECIFIC_ORDER, clear target_id
            if (data.order_criteria_type !== 'SPECIFIC_ORDER') {
                setData('target_id', '');
            }
        }
    }, [data.action, data.order_criteria_type, data.status, debouncedOrderSearch]);
    
    // --- EFFECT 4: Fetch Submissions on Template/Search Change (FORM_SUBMISSION) ---
    useEffect(() => {
        if (data.action === 'FORM_SUBMISSION' && data.submission_criteria_type === 'SPECIFIC_SUBMISSION' && data.form_template_id && debouncedSubmissionSearch !== null) {
            axios.get(route('scheduler.search-submissions', { form_template_id: data.form_template_id, search: debouncedSubmissionSearch }))
                .then(response => {
                    setSubmissionSearchList(response.data);
                })
                .catch(error => {
                    console.error("Submission search failed:", error);
                    setSubmissionSearchList([]);
                });
        } else {
            setSubmissionSearchList([]);
            // When criteria changes away from SPECIFIC_SUBMISSION, clear target_id
            if (data.submission_criteria_type !== 'SPECIFIC_SUBMISSION') {
                setData('target_id', '');
            }
        }
    }, [data.action, data.submission_criteria_type, data.form_template_id, debouncedSubmissionSearch]);

    // --- Form Submission Handler ---
    const submit = (e) => {
        e.preventDefault();
        
        // Custom validation check before submission
        if (data.action === 'FORM_SUBMISSION') {
            if (formCheckResult.success !== true) {
                setFormCheckResult(prev => ({ 
                    ...prev, 
                    message: prev.message || 'Please select a valid form template with a phone/number field.',
                    success: false 
                }));
                return; 
            }
            if (!data.whatsapp_field_name) {
                setError('whatsapp_field_name', 'Please select the field containing the customer\'s WhatsApp number.');
                return;
            }
        }

        // Clean up payload based on action type for clean controller logic
        const payload = { ...data };

        if (payload.action === 'ORDER') {
            // Remove FORM_SUBMISSION fields
            delete payload.form_template_id;
            delete payload.whatsapp_field_name;
            delete payload.submission_criteria_type;

            // Remove status/target_id if not applicable
            if (payload.order_criteria_type === 'ALL') {
                delete payload.status;
                delete payload.target_id;
            } else if (payload.order_criteria_type === 'STATUS') {
                 delete payload.target_id;
            }
            
        } else if (payload.action === 'FORM_SUBMISSION') {
            // Remove ORDER fields
            delete payload.order_criteria_type;
            delete payload.status;
            
            // Remove target_id if not applicable
            if (payload.submission_criteria_type !== 'SPECIFIC_SUBMISSION') {
                 delete payload.target_id;
            }
        }
        
        post(route('scheduler.store'), payload, {
            onSuccess: () => {
                reset();
                setActiveTab('list');
            }
        });
    };

    // --- Message Deletion Logic ---
    const handleDelete = (message) => {
        if (confirm('Are you sure you want to delete this scheduled message?')) {
            router.delete(route('scheduler.destroy', message.id));
        }
    };
    
    // --- Helper Functions for Display ---
    const getActionText = (type) => {
        switch (type) {
            case 'App\\Models\\Order': return 'Order Follow-up';
            case 'App\\Models\\FormSubmission': return 'Form Submission Follow-up';
            default: return 'Unknown Action';
        }
    }
    
    const getCriteriaText = (message) => {
        const criteria = message.target_criteria;
        
        if (message.action_type === 'App\\Models\\Order') {
            const statusName = criteria.status 
                ? (Object.entries(orderStatuses).find(([key]) => key === criteria.status)?.[1] || criteria.status) 
                : '';
                
            if (criteria.type === 'ALL') return 'All Orders';
            if (criteria.type === 'STATUS' && criteria.status) return `Status: ${statusName}`;
            if (criteria.type === 'SPECIFIC_ORDER' && criteria.order_id) return `Order ID: #${criteria.order_id} (${statusName})`;
        }
        
        if (message.action_type === 'App\\Models\\FormSubmission') {
            const template = userFormTemplates.find(t => t.id === criteria.form_template_id);
            const templateName = template ? template.name : 'Unknown Form';

            if (criteria.type === 'ALL') return `Form: ${templateName} (All Submissions)`;
            if (criteria.type === 'SPECIFIC_SUBMISSION' && criteria.form_submission_id) return `Submission ID: #${criteria.form_submission_id} (Form: ${templateName})`;
        }
        
        return 'No specific criteria';
    }

    const getStatusBadge = (message) => {
        if (message.sent_at) {
            return { text: 'SENT', class: 'bg-green-100 text-green-800' };
        } else if (new Date(message.send_at) < new Date() && message.sent_count === 0) {
            return { text: 'EXPIRED', class: 'bg-gray-100 text-gray-800' };
        } else {
            return { text: 'PENDING', class: 'bg-yellow-100 text-yellow-800' };
        }
    }

    // Get the placeholders for the current action type
    const currentPlaceholders = supportedPlaceholders[data.action] || [];

    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Message Scheduler 📅</h2>
                        <p className="text-gray-600 mt-1">Automate WhatsApp messages based on triggers</p>
                    </div>
                    <div className="flex space-x-3 mt-4 sm:mt-0">
                        <Link 
                            href={route('analytics.index')} 
                            className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            View Analytics
                        </Link>
                        <Link 
                            href={route('auto-responders.index')} 
                            className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            Auto Responders
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Message Scheduler" />

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* Tab Navigation */}
                    <div className="bg-white rounded-2xl shadow-sm mb-8">
                        <div className="border-b border-gray-200">
                            <nav className="flex -mb-px">
                                <button
                                    onClick={() => setActiveTab('create')}
                                    className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                                        activeTab === 'create'
                                            ? 'border-indigo-500 text-indigo-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                                >
                                    <div className="flex items-center space-x-2">
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        <span>Schedule New Message</span>
                                    </div>
                                </button>
                                <button
                                    onClick={() => setActiveTab('list')}
                                    className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                                        activeTab === 'list'
                                            ? 'border-indigo-500 text-indigo-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                                >
                                    <div className="flex items-center space-x-2">
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        <span>Scheduled Messages ({messages.length})</span>
                                    </div>
                                </button>
                            </nav>
                        </div>
                    </div>

                    {/* CREATE NEW SCHEDULE FORM */}
                    {activeTab === 'create' && (
                        <div className="bg-white rounded-2xl shadow-lg p-8 mb-8">
                            <div className="flex items-center space-x-3 mb-6">
                                <div className="p-2 bg-indigo-100 rounded-lg">
                                    <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 className="text-xl font-bold text-gray-900">Schedule New Message</h3>
                                    <p className="text-gray-600">Automate messages based on order status or form submissions</p>
                                </div>
                            </div>
                            
                            <form onSubmit={submit} className="space-y-8">
                                
                                {/* Basic Configuration */}
                                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div>
                                        <label htmlFor="action" className="block text-sm font-medium text-gray-700 mb-2">
                                            Trigger Type
                                        </label>
                                        <select
                                            id="action"
                                            value={data.action}
                                            onChange={(e) => setData('action', e.target.value)}
                                            className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                        >
                                            <option value="ORDER">Order Events</option>
                                            <option value="FORM_SUBMISSION">Form Submissions</option>
                                        </select>
                                        {errors.action && <p className="text-red-600 text-sm mt-2">{errors.action}</p>}
                                    </div>
                                    
                                    <div>
                                        <label htmlFor="device_id" className="block text-sm font-medium text-gray-700 mb-2">
                                            Sending Device
                                        </label>
                                        <select
                                            id="device_id"
                                            value={data.device_id}
                                            onChange={(e) => setData('device_id', e.target.value)}
                                            className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                        >
                                            <option value="">Select a Device</option>
                                            {devices && devices.map(device => (
                                                <option key={device.id} value={device.id}>
                                                    {device.name} (+{device.session_id})
                                                </option>
                                            ))}
                                        </select>
                                        {errors.device_id && <p className="text-red-600 text-sm mt-2">{errors.device_id}</p>}
                                    </div>
                                </div>
                                
                                {/* Trigger Configuration */}
                                <div className="bg-blue-50 border border-blue-200 rounded-2xl p-6">
                                    <h4 className="font-semibold text-blue-900 mb-4 flex items-center space-x-2">
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        <span>Trigger Configuration</span>
                                    </h4>
                                    
                                    {/* ORDER CRITERIA */}
                                    {data.action === 'ORDER' && (
                                        <div className="space-y-4">
                                            <label className="block text-sm font-medium text-gray-700">
                                                Target Audience:
                                            </label>
                                            <div className="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-6">
                                                <label className="flex items-center space-x-3">
                                                    <input 
                                                        type="radio" 
                                                        value="ALL" 
                                                        checked={data.order_criteria_type === 'ALL'} 
                                                        onChange={() => setData('order_criteria_type', 'ALL')} 
                                                        className="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" 
                                                    />
                                                    <span className="text-sm text-gray-700">All Orders</span>
                                                </label>
                                                <label className="flex items-center space-x-3">
                                                    <input 
                                                        type="radio" 
                                                        value="STATUS" 
                                                        checked={data.order_criteria_type === 'STATUS'} 
                                                        onChange={() => {setData('order_criteria_type', 'STATUS'); setData('target_id', '');}} 
                                                        className="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" 
                                                    />
                                                    <span className="text-sm text-gray-700">All Orders in a Specific Status</span>
                                                </label>
                                                <label className="flex items-center space-x-3">
                                                    <input 
                                                        type="radio" 
                                                        value="SPECIFIC_ORDER" 
                                                        checked={data.order_criteria_type === 'SPECIFIC_ORDER'} 
                                                        onChange={() => setData('order_criteria_type', 'SPECIFIC_ORDER')} 
                                                        className="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" 
                                                    />
                                                    <span className="text-sm text-gray-700">A Single Specific Order</span>
                                                </label>
                                            </div>
                                            {errors.order_criteria_type && <p className="text-red-600 text-sm mt-2">{errors.order_criteria_type}</p>}
                                            
                                            {(data.order_criteria_type === 'STATUS' || data.order_criteria_type === 'SPECIFIC_ORDER') && (
                                                <div className="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label htmlFor="status" className="block text-sm font-medium text-gray-700 mb-2">
                                                            Order Status
                                                        </label>
                                                        <select
                                                            id="status"
                                                            value={data.status}
                                                            onChange={(e) => {setData('status', e.target.value); setOrderSearchTerm('');}}
                                                            className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                                        >
                                                            <option value="">-- Select Order Status --</option>
                                                            {Object.entries(orderStatuses).map(([key, name]) => (
                                                                <option key={key} value={key}>{name}</option>
                                                            ))}
                                                        </select>
                                                        {errors.status && <p className="text-red-600 text-sm mt-2">{errors.status}</p>}
                                                    </div>
                                                    
                                                    {data.order_criteria_type === 'SPECIFIC_ORDER' && (
                                                        <div className="relative">
                                                            <label htmlFor="target_id" className="block text-sm font-medium text-gray-700 mb-2">
                                                                Specific Order (Search by ID or Name)
                                                            </label>
                                                            <input
                                                                type="text"
                                                                id="order_search_term"
                                                                placeholder="Start typing Order ID or Customer Name..."
                                                                value={orderSearchTerm}
                                                                onChange={(e) => setOrderSearchTerm(e.target.value)}
                                                                className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                                                disabled={!data.status}
                                                            />
                                                            {errors.target_id && <p className="text-red-600 text-sm mt-2">{errors.target_id}</p>}

                                                            {orderSearchTerm.length > 0 && orderSearchList.length > 0 && (
                                                                <ul className="absolute z-10 w-full bg-white border border-gray-300 rounded-xl mt-1 max-h-60 overflow-y-auto shadow-lg">
                                                                    {orderSearchList.map(order => (
                                                                        <li 
                                                                            key={order.id} 
                                                                            onClick={() => {
                                                                                setData('target_id', order.id);
                                                                                setOrderSearchTerm(`Order #${order.order_number} - ${order.full_name}`);
                                                                                setOrderSearchList([]);
                                                                            }}
                                                                            className="p-3 cursor-pointer hover:bg-indigo-50 transition-colors border-b border-gray-100"
                                                                        >
                                                                            <span className="font-semibold text-gray-800">Order #{order.order_number}</span>
                                                                            <span className="ml-2 text-sm text-gray-600">by {order.full_name}</span>
                                                                        </li>
                                                                    ))}
                                                                </ul>
                                                            )}
                                                            {data.target_id && data.order_criteria_type === 'SPECIFIC_ORDER' && (
                                                                <p className="text-xs text-green-600 mt-1">Targeting Order ID: **{data.target_id}**</p>
                                                            )}
                                                        </div>
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    )}
                                    
                                    {/* FORM SUBMISSION CRITERIA */}
                                    {data.action === 'FORM_SUBMISSION' && (
                                        <div className="space-y-4">
                                            <div>
                                                <label htmlFor="form_template_id" className="block text-sm font-medium text-gray-700 mb-2">
                                                    Target Form
                                                </label>
                                                <select
                                                    id="form_template_id"
                                                    value={data.form_template_id}
                                                    onChange={(e) => {
                                                        setData('form_template_id', e.target.value);
                                                        setData('target_id', '');
                                                        setSubmissionSearchTerm('');
                                                    }}
                                                    className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                                >
                                                    <option value="">-- Select Form Template --</option>
                                                    {userFormTemplates.map(template => (
                                                        <option key={template.id} value={template.id}>{template.name}</option>
                                                    ))}
                                                </select>
                                                {errors.form_template_id && <p className="text-red-600 text-sm mt-2">{errors.form_template_id}</p>}
                                            </div>

                                            {/* Field Selection */}
                                            {formCheckResult.success && formCheckResult.potentialFields.length > 0 && (
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label htmlFor="whatsapp_field_name" className="block text-sm font-medium text-gray-700 mb-2">
                                                            WhatsApp Number Field
                                                            {formCheckResult.isMultiple && <span className="text-red-500 ml-1">*</span>}
                                                        </label>
                                                        <select
                                                            id="whatsapp_field_name"
                                                            value={data.whatsapp_field_name}
                                                            onChange={(e) => setData('whatsapp_field_name', e.target.value)}
                                                            className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                                        >
                                                            {formCheckResult.isMultiple && <option value="">-- Select Field --</option>}
                                                            {formCheckResult.potentialFields.map(field => (
                                                                <option key={field.name} value={field.name}>
                                                                    {field.label} ({field.name})
                                                                </option>
                                                            ))}
                                                        </select>
                                                        {errors.whatsapp_field_name && <p className="text-red-600 text-sm mt-2">{errors.whatsapp_field_name}</p>}
                                                    </div>
                                                    
                                                    {/* Submission Criteria Type */}
                                                    <div>
                                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                                            Submissions to Target:
                                                        </label>
                                                        <div className="flex items-center space-x-6 h-full">
                                                            <label className="flex items-center space-x-3">
                                                                <input 
                                                                    type="radio" 
                                                                    value="ALL" 
                                                                    checked={data.submission_criteria_type === 'ALL'} 
                                                                    onChange={() => {setData('submission_criteria_type', 'ALL'); setData('target_id', '');}} 
                                                                    className="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" 
                                                                />
                                                                <span className="text-sm text-gray-700">All Submissions</span>
                                                            </label>
                                                            <label className="flex items-center space-x-3">
                                                                <input 
                                                                    type="radio" 
                                                                    value="SPECIFIC_SUBMISSION" 
                                                                    checked={data.submission_criteria_type === 'SPECIFIC_SUBMISSION'} 
                                                                    onChange={() => setData('submission_criteria_type', 'SPECIFIC_SUBMISSION')} 
                                                                    className="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" 
                                                                />
                                                                <span className="text-sm text-gray-700">Single Submission</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            )}

                                            {/* Submission Search */}
                                            {data.submission_criteria_type === 'SPECIFIC_SUBMISSION' && data.form_template_id && (
                                                <div className="relative mt-4">
                                                    <label htmlFor="target_id" className="block text-sm font-medium text-gray-700 mb-2">
                                                        Specific Submission (Search by ID or Data Hint)
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="submission_search_term"
                                                        placeholder="Start typing Submission ID or data hint..."
                                                        value={submissionSearchTerm}
                                                        onChange={(e) => setSubmissionSearchTerm(e.target.value)}
                                                        className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                                        disabled={!data.form_template_id}
                                                    />
                                                    {errors.target_id && <p className="text-red-600 text-sm mt-2">{errors.target_id}</p>}

                                                    {submissionSearchTerm.length > 0 && submissionSearchList.length > 0 && (
                                                        <ul className="absolute z-10 w-full bg-white border border-gray-300 rounded-xl mt-1 max-h-60 overflow-y-auto shadow-lg">
                                                            {submissionSearchList.map(submission => (
                                                                <li 
                                                                    key={submission.id} 
                                                                    onClick={() => {
                                                                        setData('target_id', submission.id);
                                                                        setSubmissionSearchTerm(`Submission #${submission.id} - ${submission.submitter_name_hint}`);
                                                                        setSubmissionSearchList([]);
                                                                    }}
                                                                    className="p-3 cursor-pointer hover:bg-indigo-50 transition-colors border-b border-gray-100"
                                                                >
                                                                    <span className="font-semibold text-gray-800">ID #{submission.id}</span>
                                                                    <span className="ml-2 text-sm text-gray-600">({submission.submitter_name_hint}...)</span>
                                                                </li>
                                                            ))}
                                                        </ul>
                                                    )}
                                                    {data.target_id && data.submission_criteria_type === 'SPECIFIC_SUBMISSION' && (
                                                        <p className="text-xs text-green-600 mt-1">Targeting Submission ID: **{data.target_id}**</p>
                                                    )}
                                                </div>
                                            )}

                                            {/* Validation Message */}
                                            {formCheckResult.success !== null && (
                                                <div className={`p-4 rounded-xl border ${
                                                    formCheckResult.success 
                                                        ? 'bg-green-50 border-green-200 text-green-700' 
                                                        : 'bg-red-50 border-red-200 text-red-700'
                                                }`}>
                                                    <div className="flex items-center space-x-3">
                                                        {formCheckResult.success ? (
                                                            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                                            </svg>
                                                        ) : (
                                                            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                                                            </svg>
                                                        )}
                                                        <p className="text-sm">{formCheckResult.message}</p>
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                                
                                {/* Scheduling */}
                                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div>
                                        <label htmlFor="send_at" className="block text-sm font-medium text-gray-700 mb-2">
                                            Schedule Date & Time
                                        </label>
                                        <input
                                            id="send_at"
                                            type="datetime-local"
                                            value={data.send_at}
                                            onChange={(e) => setData('send_at', e.target.value)}
                                            className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                        />
                                        {errors.send_at && <p className="text-red-600 text-sm mt-2">{errors.send_at}</p>}
                                    </div>
                                </div>
                                
                                {/* Message Content and Placeholders */}
                                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <div className="lg:col-span-2">
                                        <label htmlFor="message" className="block text-sm font-medium text-gray-700 mb-2">
                                            Message Content
                                        </label>
                                        <textarea
                                            id="message"
                                            value={data.message}
                                            onChange={(e) => setData('message', e.target.value)}
                                            rows="6"
                                            placeholder={`Write your WhatsApp message here. Use dynamic variables from the list on the right, e.g., "Hello {customer_name}, your order {order_number} is ready!"`}
                                            className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                        ></textarea>
                                        {errors.message && <p className="text-red-600 text-sm mt-2">{errors.message}</p>}
                                        <p className="text-gray-500 text-xs mt-2">
                                            **Note**: Dynamic keywords only work when targeting a **Specific Order** or **Specific Submission**.
                                        </p>
                                    </div>

                                    {/* Available Placeholders */}
                                    <div className="lg:col-span-1 bg-gray-50 p-4 rounded-xl border border-gray-200 self-start">
                                        <h5 className="font-semibold text-sm text-gray-700 mb-3">Available Dynamic Keywords ({data.action})</h5>
                                        <div className="flex flex-wrap gap-2 text-xs">
                                            {currentPlaceholders.map(placeholder => (
                                                <span 
                                                    key={placeholder}
                                                    className={`px-3 py-1 bg-white border rounded-full font-mono text-gray-700 cursor-copy hover:bg-indigo-50 transition-colors`}
                                                    onClick={() => {
                                                        const keyword = `{${placeholder}}`;
                                                        setData('message', data.message + keyword);
                                                    }}
                                                    title={`Click to insert ${placeholder}`}
                                                >
                                                    {`{${placeholder}}`}
                                                </span>
                                            ))}
                                        </div>
                                        {currentPlaceholders.length === 0 && (
                                            <p className="text-gray-500 text-sm">Select a Trigger Type to see available keywords.</p>
                                        )}
                                    </div>
                                </div>
                                
                                {/* Submit Button */}
                                <div className="flex justify-end pt-6">
                                    <button
                                        type="submit"
                                        disabled={processing || (data.action === 'FORM_SUBMISSION' && formCheckResult.success === false)}
                                        className="px-8 py-4 bg-indigo-600 text-white font-medium rounded-xl shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {processing ? (
                                            <div className="flex items-center space-x-2">
                                                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                                <span>Scheduling...</span>
                                            </div>
                                        ) : (
                                            'Schedule Message'
                                        )}
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}

                    {/* SCHEDULED MESSAGES LIST */}
                    {activeTab === 'list' && (
                        <div className="bg-white rounded-2xl shadow-lg p-8">
                            <div className="flex items-center justify-between mb-6">
                                <div className="flex items-center space-x-3">
                                    <div className="p-2 bg-blue-100 rounded-lg">
                                        <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 className="text-xl font-bold text-gray-900">Scheduled Messages</h3>
                                        <p className="text-gray-600">Manage your automated message schedules</p>
                                    </div>
                                </div>
                                <span className="bg-indigo-100 text-indigo-800 text-sm font-medium px-3 py-1 rounded-full">
                                    {messages.length} total
                                </span>
                            </div>
                            
                            {messages && messages.length > 0 ? (
                                <div className="space-y-4">
                                    {messages.map((message) => {
                                        const status = getStatusBadge(message);
                                        return (
                                            <div key={message.id} className="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow group">
                                                <div className="flex justify-between items-start">
                                                    <div className="flex-1 space-y-3">
                                                        <div className="flex items-center space-x-4">
                                                            <div className="flex items-center space-x-2">
                                                                <svg className="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                                <span className="font-semibold text-gray-900">
                                                                    {new Date(message.send_at).toLocaleString()}
                                                                </span>
                                                            </div>
                                                            <span className={`text-xs font-medium px-2 py-1 rounded-full ${status.class}`}>
                                                                {status.text}
                                                            </span>
                                                        </div>
                                                        
                                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                            <div>
                                                                <span className="font-medium text-gray-700">Trigger:</span>
                                                                <span className="ml-2 text-gray-600">{getActionText(message.action_type)}</span>
                                                            </div>
                                                            <div>
                                                                <span className="font-medium text-gray-700">Criteria:</span>
                                                                <span className="ml-2 text-gray-600">{getCriteriaText(message)}</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div className="bg-gray-50 p-4 rounded-lg border-l-4 border-indigo-400">
                                                            <p className="text-gray-700 text-sm leading-relaxed">
                                                                {message.message.length > 120 ? 
                                                                    message.message.substring(0, 120) + '...' : 
                                                                    message.message
                                                                }
                                                            </p>
                                                        </div>
                                                    </div>

                                                    {!message.sent_at && (
                                                        <button 
                                                            onClick={() => handleDelete(message)} 
                                                            className="ml-4 p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors opacity-0 group-hover:opacity-100"
                                                            title="Delete schedule"
                                                        >
                                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    )}
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            ) : (
                                <div className="text-center py-12">
                                    <svg className="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p className="text-gray-500 text-lg mb-2">No scheduled messages</p>
                                    <p className="text-gray-400 text-sm mb-6">Create your first scheduled message to automate customer communication</p>
                                    <button
                                        onClick={() => setActiveTab('create')}
                                        className="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors"
                                    >
                                        Schedule First Message
                                    </button>
                                </div>
                            )}
                        </div>
                    )}
                    
                </div>
            </div>
        </AuthenticatedLayout>
    );
}