import { router } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function TemplateModal({ order, templates, onClose, onSend }) {
    const [selectedTemplate, setSelectedTemplate] = useState(null);
    const [message, setMessage] = useState('');
    const [mode, setMode] = useState('view'); // 'view', 'add', 'edit'
    const [templateForm, setTemplateForm] = useState({
        id: null,
        name: '',
        message: '',
        category: 'general',
        description: '',
        newCategory: '' // Add this field to track new category input
    });
    const [categories, setCategories] = useState([]);
    const isFormValid = templateForm.name.trim() && 
                   templateForm.message.trim() && 
                   (templateForm.category !== '_new' || templateForm.newCategory.trim());


    // Extract unique categories from templates
    useEffect(() => {
        const uniqueCategories = [...new Set(templates.map(t => t.category))];
        setCategories(uniqueCategories);
    }, [templates]);

    const applyTemplate = (template) => {
        const filledMessage = template.message
            .replace(/{Name}/g, order.full_name)
            .replace(/{product name}/g, order.product_name || 'your order')
            .replace(/{order number}/g, order.order_number);
        setSelectedTemplate(template);
        setMessage(filledMessage);
    };

    const handleSend = () => {
        if (message.trim()) {
            onSend(message);
            setMessage(''); // Clear the message after sending
            setSelectedTemplate(null);
            onClose();
        }
    };

    const handleAddTemplate = () => {
        setMode('add');
        setTemplateForm({
            id: null,
            name: '',
            message: '',
            category: 'general',
            description: ''
        });
    };

    const handleEditTemplate = (template) => {
        setMode('edit');
        setTemplateForm({
            id: template.id,
            name: template.name,
            message: template.message,
            category: template.category,
            description: template.description || ''
        });
    };

    const handleSaveTemplate = async () => {
        const dataToSend = {
            ...templateForm,
            category: templateForm.category === '_new' 
                ? templateForm.newCategory 
                : templateForm.category
        };
    
        console.log('Saving template:', dataToSend);
        await router.post(route('templates.wa.store'), dataToSend);
        setMode('view');
    };

    const handleDeleteTemplate = async (template) => {
        if (confirm(`Are you sure you want to delete "${template.name}"?`)) {
            // In a real implementation, you would delete from the backend here
            console.log('Deleting template:', template.id);
            
            // For demo purposes, we'll just close the form
            // In a real app, you would update the templates list via state or refetch
            await router.delete(route('templates.wa.destroy', {template: template.id}))

            if (selectedTemplate?.id === template.id) {
                setSelectedTemplate(null);
                setMessage('');
            }
        }
    };

    const renderTemplateList = () => (
        <div className="w-1/3 border-r pr-4 overflow-y-auto">
            <div className="mb-4">
                <button 
                    onClick={handleAddTemplate}
                    className="w-full py-2 bg-blue-500 text-white rounded hover:bg-blue-600 flex items-center justify-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clipRule="evenodd" />
                    </svg>
                    New Template
                </button>
            </div>
            {templates.map(template => (
                <div
                    key={template.id || template.key}
                    className={`p-3 mb-2 cursor-pointer rounded ${
                        selectedTemplate?.id === template.id 
                            ? 'bg-blue-100 border border-blue-300' 
                            : 'hover:bg-gray-100'
                    }`}
                >
                    <div className="flex justify-between items-start">
                        <div onClick={() => applyTemplate(template)} className="flex-1">
                            <h4 className="font-medium">{template.name}</h4>
                            <p className="text-sm text-gray-600 truncate">{template.description}</p>
                            <span className="inline-block mt-1 px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded-full">
                                {template.category}
                            </span>
                        </div>
                        {!template.is_default && (
                            <div className="flex space-x-1 ml-2">
                                <button 
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        handleEditTemplate(template);
                                    }}
                                    className="text-blue-500 hover:text-blue-700"
                                    title="Edit template"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </button>
                                <button 
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        handleDeleteTemplate(template);
                                    }}
                                    className="text-red-500 hover:text-red-700"
                                    title="Delete template"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clipRule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            ))}
        </div>
    );

    const renderTemplateForm = () => (
        <div className="flex-1 overflow-auto p-4">
            <h4 className="font-medium text-lg mb-4">
                {mode === 'add' ? 'Add New Template' : 'Edit Template'}
            </h4>
            <div className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Template Name*</label>
                    <input
                        type="text"
                        value={templateForm.name}
                        onChange={(e) => setTemplateForm({...templateForm, name: e.target.value})}
                        className="w-full p-2 border border-gray-300 rounded"
                        placeholder="e.g. Order Confirmation"
                    />
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Category*</label>
                    <select
                        value={templateForm.category}
                        onChange={(e) => {
                            if (e.target.value === '_new') {
                                setTemplateForm({
                                    ...templateForm,
                                    category: '_new',
                                    newCategory: '' // Reset new category input
                                });
                            } else {
                                setTemplateForm({
                                    ...templateForm,
                                    category: e.target.value,
                                    newCategory: '' // Clear new category when selecting existing
                                });
                            }
                        }}
                        className="w-full p-2 border border-gray-300 rounded"
                    >
                        <option value="">Select category</option>
                        {categories.map(cat => (
                            <option key={cat} value={cat}>{cat}</option>
                        ))}
                        <option value="_new">+ New Category</option>
                    </select>
                    {templateForm.category === '_new' && (
                        <input
                            type="text"
                            value={templateForm.newCategory}
                            onChange={(e) => setTemplateForm({
                                ...templateForm,
                                newCategory: e.target.value
                            })}
                            className="w-full p-2 border border-gray-300 rounded mt-2"
                            placeholder="Enter new category name"
                        />
                    )}
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input
                        type="text"
                        value={templateForm.description}
                        onChange={(e) => setTemplateForm({...templateForm, description: e.target.value})}
                        className="w-full p-2 border border-gray-300 rounded"
                        placeholder="Short description of when to use this template"
                    />
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Message*</label>
                    <textarea
                        value={templateForm.message}
                        onChange={(e) => setTemplateForm({...templateForm, message: e.target.value})}
                        className="w-full p-2 border border-gray-300 rounded h-32"
                        placeholder={`Use variables like {Name}, {product name}, {order number}\nExample: Hello {Name}, your order #{order number} is confirmed.`}
                    />
                    <div className="text-xs text-gray-500 mt-1">
                        Available variables: {order.full_name ? '{Name}' : ''} {order.product_name ? '{product name}' : ''} {order.order_number ? '{order number}' : ''}
                    </div>
                </div>
                <div className="flex justify-end space-x-2 pt-2">
                    <button
                        onClick={() => setMode('view')}
                        className="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                    >
                        Cancel
                    </button>
                    <button
                        onClick={handleSaveTemplate}
                        disabled={!isFormValid}
                        className={`px-4 py-2 rounded ${
                            isFormValid
                                ? 'bg-blue-500 text-white hover:bg-blue-600' 
                                : 'bg-gray-300 text-gray-500'
                        }`}
                    >
                        {mode === 'add' ? 'Add Template' : 'Save Changes'}
                    </button>
                </div>
            </div>
        </div>
    );

    const renderMessageEditor = () => (
        <div className="w-2/3 pl-4 flex flex-col">
            <div className="mb-2 flex items-center justify-between">
                <h4 className="font-medium">
                    {selectedTemplate ? selectedTemplate.name : 'Custom Message'}
                </h4>
                {selectedTemplate && (
                    <button 
                        onClick={() => handleEditTemplate(selectedTemplate)}
                        className="text-blue-500 hover:text-blue-700 text-sm flex items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Edit Template
                    </button>
                )}
            </div>
            <textarea
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                className="flex-1 w-full p-3 border border-gray-300 rounded mb-4"
                placeholder="Select a template or write your message..."
            />
            <div className="flex justify-end space-x-2">
                <button
                    onClick={() => {
                        setMessage('');
                        setSelectedTemplate(null);
                    }}
                    disabled={!message}
                    className={`px-4 py-2 rounded ${
                        message ? 'bg-gray-200 text-gray-700 hover:bg-gray-300' : 'bg-gray-100 text-gray-400'
                    }`}
                >
                    Clear
                </button>
                <button
                    onClick={onClose}
                    className="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                >
                    Cancel
                </button>
                <button
                    onClick={handleSend}
                    disabled={!message.trim()}
                    className={`px-4 py-2 rounded ${
                        message.trim() 
                            ? 'bg-green-500 text-white hover:bg-green-600' 
                            : 'bg-gray-300 text-gray-500'
                    }`}
                >
                    Send
                </button>
            </div>
        </div>
    );

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg p-4 w-full max-w-4xl max-h-[90vh] flex flex-col">
                <div className="flex justify-between items-center mb-4">
                    <h3 className="font-medium text-lg">
                        {mode === 'view' ? 'WhatsApp Templates' : mode === 'add' ? 'Add Template' : 'Edit Template'}
                    </h3>
                    <button onClick={onClose} className="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div className="flex flex-1 overflow-hidden">
                    {mode === 'view' ? (
                        <>
                            {renderTemplateList()}
                            {renderMessageEditor()}
                        </>
                    ) : (
                        renderTemplateForm()
                    )}
                </div>
            </div>
        </div>
    );
}