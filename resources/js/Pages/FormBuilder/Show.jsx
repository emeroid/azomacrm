import { Head, usePage } from '@inertiajs/react';
import { useState, useRef, useEffect, useMemo } from 'react';
import { DndContext, closestCenter, KeyboardSensor, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { arrayMove, SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { SortableItem } from '@/Components/SortableItem';
import Modal from '@/Components/Modal';
import Button from '@/Components/Button';
import FieldPropertiesForm from '@/Components/FieldPropertiesForm';
import axios from 'axios';

export default function FormBuilderShow() {
    const { template, fieldTypes, products } = usePage().props;
    const [selectedFieldId, setSelectedFieldId] = useState(null);
    const [showPreview, setShowPreview] = useState(false);
    const [showEmbed, setShowEmbed] = useState(false);
    const [previewHtml, setPreviewHtml] = useState('');
    const [embedCode, setEmbedCode] = useState('');
    const [isUpdating, setIsUpdating] = useState(false);
    const [error, setError] = useState(null);
    const previewRef = useRef(null);
    const embedRef = useRef(null);

    // Initialize fields state from props
    const [fields, setFields] = useState(template.fields || []);

    // Get current field data
    const currentField = useMemo(() => 
        fields.find(f => f.id === selectedFieldId) || null,
        [fields, selectedFieldId]
    );

    const sensors = useSensors(
        useSensor(PointerSensor),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

    // Add a new field
    const addField = async (type) => {
        try {
            setError(null);
            
            // Prepare properties based on field type
            let properties = {};
            
            if (type === 'product_selector') {
                // For products field, nest the product data under 'products' key
                properties = {
                    products: [{
                        product: '',
                        price: '',
                        note: ''
                    }],
                    label: fieldTypes[type].label,
                    placeholder: fieldTypes[type].placeholder || ''
                };
            } else {
                // For other field types, use default properties
                properties = fieldTypes[type].properties || {};
            }
    
            const response = await axios.post(
                route('form.builder.field.store', { template: template.id }), 
                {
                    name: fieldTypes[type].name,
                    type,
                    is_required: fieldTypes[type].is_required,
                    label: fieldTypes[type].label,
                    order: fields.length + 1,
                    properties: properties // Use the properly structured properties
                }
            );
    
            setFields([...fields, response.data.field]);
            setSelectedFieldId(response.data.field.id);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to add field');
        }
    };

    // Update field properties
    const updateField = async (fieldId, data) => {
        try {
            setError(null);
            setIsUpdating(true);
            const payload = {
                label: data.label,
                is_required: data.is_required,
                properties: data.properties
            };
            
            const response = await axios.put(route('form.builder.field.update', { field: fieldId }), payload);
            
            setFields(fields.map(f => 
                f.id === fieldId ? response.data.field : f
            ));
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to update field');
        } finally {
            setIsUpdating(false);
        }
    };

    // Remove a field
    const removeField = async (fieldId) => {
        if (confirm('Are you sure you want to remove this field?')) {
            try {
                setError(null);
                await axios.delete(route('form.builder.field.destroy', { field: fieldId }));
                setFields(fields.filter(f => f.id !== fieldId));
                if (selectedFieldId === fieldId) {
                    setSelectedFieldId(null);
                }
            } catch (err) {
                setError(err.response?.data?.message || 'Failed to remove field');
            }
        }
    };

    // Handle drag and drop reordering
    const handleDragEnd = async (event) => {
        const { active, over } = event;
        
        if (active.id !== over.id) {
            const oldIndex = fields.findIndex(item => item.id === active.id);
            const newIndex = fields.findIndex(item => item.id === over.id);
            const newItems = arrayMove(fields, oldIndex, newIndex);
            
            setFields(newItems);
            
            try {
                await axios.post(route('form.builder.reorder'), {
                    orders: newItems.map((item, index) => ({
                        field_id: item.id,
                        order: index + 1
                    }))
                });
            } catch (err) {
                setError('Failed to save new field order');
                console.error('Error reordering fields:', err);
            }
        }
    };

    // Generate preview
    const generatePreview = async () => {
        try {
            setError(null);
            const response = await axios.post(route('form.builder.preview', { template: template.id }));
            setPreviewHtml(response.data.html);
            setShowPreview(true);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to generate preview');
        }
    };

    // Generate embed code
    const generateEmbedCode = async () => {
        try {
            setError(null);
            const response = await axios.post(route('form.builder.embed', { template: template.id }));
            setEmbedCode(response.data.embed_code);
            setShowEmbed(true);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to generate embed code');
        }
    };

    // Save form
    const saveForm = async () => {
        try {
            setError(null);
            await axios.post(route('form.builder.save', { template: template.id }));
            // Show success message
            console.log('Form saved successfully');
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to save form');
        }
    };

    // Copy embed code to clipboard
    const copyEmbedCode = () => {
        embedRef.current.select();
        document.execCommand('copy');
        // You can add a toast notification here
        console.log('Embed code copied to clipboard');
    };

    // Render field preview
    const renderFieldPreview = (field) => {
        if (field.rendered_field) {
            return <div dangerouslySetInnerHTML={{ __html: field.rendered_field }} />;
        }
        
        // Fallback preview
        return (
            <div className="border p-2 rounded bg-gray-50">
                {fieldTypes[field.type]?.label || field.type} Field
                {field.label && `: ${field.label}`}
            </div>
        );
    };

    return (
        <>
            <Head title={`Form Builder - ${template.name}`} />

            <div className="min-h-screen bg-gray-50">
                {/* Error message */}
                {error && (
                    <div className="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                        <p>{error}</p>
                    </div>
                )}

                {/* Header */}
                <div className="bg-gray-800 text-white py-4 px-8">
                    <div className="max-w-7xl mx-auto flex justify-between items-center">
                        <h1 className="text-xl font-semibold flex items-center gap-2">
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Form Builder - {template.name}
                        </h1>
                        <div className="flex gap-4">
                            <Button 
                                onClick={saveForm} 
                                className="bg-green-600 hover:bg-green-700 flex items-center gap-2"
                                disabled={isUpdating}
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                </svg>
                                {isUpdating ? 'Saving...' : 'Save Form'}
                            </Button>
                            <Button 
                                onClick={generateEmbedCode} 
                                className="bg-blue-600 hover:bg-blue-700 flex items-center gap-2"
                                disabled={isUpdating}
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Embed Code
                            </Button>
                            <Button 
                                onClick={generatePreview} 
                                className="bg-purple-600 hover:bg-purple-700 flex items-center gap-2"
                                disabled={isUpdating}
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Preview
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Main Content - Three Panels */}
                <div className="max-w-7xl mx-auto py-6 px-4 grid grid-cols-3 gap-6">
                    {/* Left Panel - Available Fields */}
                    <div className="bg-white rounded-lg shadow overflow-hidden flex flex-col">
                        <div className="px-4 py-3 border-b border-gray-200 font-medium text-gray-700 flex items-center gap-2">
                            <svg className="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Available Fields
                        </div>
                        <div className="p-4 flex-1 overflow-y-auto grid gap-3">
                            {Object.entries(fieldTypes).map(([type, config]) => (
                                <div 
                                    key={type}
                                    className="p-3 bg-white border border-gray-200 rounded-md cursor-pointer flex items-center gap-3 transition-all hover:border-blue-300 hover:bg-blue-50 hover:translate-x-1"
                                    onClick={() => addField(type)}
                                >
                                    <div className="w-8 h-8 bg-indigo-100 rounded flex items-center justify-center">
                                        <i className={`fas ${config.icon} text-indigo-600`} />
                                    </div>
                                    <div>
                                        <div className="font-medium text-gray-800">{config.label}</div>
                                        <div className="text-xs text-gray-500">{config.description}</div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Middle Panel - Form Canvas */}
                    <div className="bg-white rounded-lg shadow overflow-hidden flex flex-col">
                        <div className="px-4 py-3 border-b border-gray-200 font-medium text-gray-700 flex items-center gap-2">
                            <svg className="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Form Canvas
                        </div>
                        <div className="p-4 flex-1 overflow-y-auto">
                            <DndContext 
                                sensors={sensors}
                                collisionDetection={closestCenter}
                                onDragEnd={handleDragEnd}
                            >
                                <SortableContext 
                                    items={fields}
                                    strategy={verticalListSortingStrategy}
                                >
                                    {fields.map((field) => (
                                        <SortableItem 
                                            key={field.id} 
                                            id={field.id}
                                            active={selectedFieldId === field.id}
                                            onClick={() => setSelectedFieldId(field.id)}
                                        >
                                            <div className="flex justify-between items-center mb-2 pb-2 border-b border-gray-100">
                                                <div className="flex items-center gap-2">
                                                    <div className="w-7 h-7 bg-indigo-100 rounded flex items-center justify-center">
                                                        <i className={`fas ${fieldTypes[field.type]?.icon} text-indigo-600 text-sm`} />
                                                    </div>
                                                    <span className="font-medium text-gray-800">{field?.label}</span>
                                                    {field.is_required && (
                                                        <span className="px-2 py-1 bg-red-100 text-red-700 text-xs rounded inline-flex items-center gap-1">
                                                            Required
                                                        </span>
                                                    )}
                                                </div>
                                                <div className="flex gap-1">
                                                    <button 
                                                        className="p-1 text-gray-500 hover:text-red-600 hover:bg-red-100 rounded"
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            removeField(field.id);
                                                        }}
                                                    >
                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div className="opacity-70 pointer-events-none">
                                                {renderFieldPreview(field)}
                                            </div>
                                        </SortableItem>
                                    ))}
                                </SortableContext>
                            </DndContext>
                        </div>
                    </div>

                    {/* Right Panel - Field Properties */}
                    <div className="bg-white rounded-lg shadow overflow-hidden flex flex-col">
                        <div className="px-4 py-3 border-b border-gray-200 font-medium text-gray-700 flex items-center gap-2">
                            <svg className="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Field Properties
                        </div>
                        <div className="p-6 flex-1 overflow-y-auto">
                            {currentField ? (
                                <FieldPropertiesForm
                                    key={currentField.id} // Force remount when field changes
                                    field={currentField}
                                    onUpdate={(fieldId, data) => {
                                        updateField(fieldId, data);
                                    }}
                                    products={products}
                                    isUpdating={isUpdating}
                                />
                            ) : (
                                <div className="flex flex-col items-center justify-center h-full text-gray-500 text-center">
                                    <svg className="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 className="text-lg font-medium mb-1">No Field Selected</h3>
                                    <p className="max-w-xs">Click on a field in the canvas to edit its properties</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Preview Modal */}
            <Modal show={showPreview} onClose={() => setShowPreview(false)} maxWidth="4xl">
                <div className="bg-white rounded-lg overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 className="text-xl font-semibold">Form Preview</h3>
                        <button 
                            type="button" 
                            className="text-gray-400 hover:text-gray-500"
                            onClick={() => setShowPreview(false)}
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div className="p-6">
                        <div ref={previewRef} dangerouslySetInnerHTML={{ __html: previewHtml }} />
                    </div>
                    <div className="px-6 py-4 border-t border-gray-200 flex justify-end">
                        <Button onClick={() => setShowPreview(false)} className="bg-gray-600 hover:bg-gray-700">
                            Close
                        </Button>
                    </div>
                </div>
            </Modal>

            {/* Embed Code Modal */}
            <Modal show={showEmbed} onClose={() => setShowEmbed(false)}>
                <div className="bg-white rounded-lg overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 className="text-xl font-semibold">Embed Code</h3>
                        <button 
                            type="button" 
                            className="text-gray-400 hover:text-gray-500"
                            onClick={() => setShowEmbed(false)}
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div className="p-6">
                        <p className="text-gray-600 mb-2">Copy this code and paste it into your website:</p>
                        <textarea 
                            ref={embedRef}
                            className="w-full p-3 border border-gray-300 rounded-md font-mono text-sm min-h-32"
                            value={embedCode}
                            readOnly
                        />
                        <div className="mt-4 p-3 bg-gray-50 rounded-md">
                            <h4 className="text-sm font-semibold mb-2 text-gray-700">Implementation Notes:</h4>
                            <ul className="text-sm text-gray-600 list-disc pl-5">
                                <li>Paste this code where you want the form to appear</li>
                                <li>Make sure the target page has enough width for the form</li>
                                <li>Form submissions will be saved to your dashboard</li>
                            </ul>
                        </div>
                    </div>
                    <div className="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                        <Button onClick={() => setShowEmbed(false)} className="bg-gray-600 hover:bg-gray-700">
                            Close
                        </Button>
                        <Button 
                            onClick={copyEmbedCode}
                            className="bg-blue-600 hover:bg-blue-700"
                        >
                            Copy Code
                        </Button>
                    </div>
                </div>
            </Modal>
        </>
    );
}


// import { Head, usePage } from '@inertiajs/react';
// import { useState, useRef } from 'react';
// import { DndContext, closestCenter, KeyboardSensor, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
// import { arrayMove, SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
// import { SortableItem } from '@/Components/SortableItem';
// import Modal from '@/Components/Modal';
// import Button from '@/Components/Button';
// import FieldPropertiesForm from '@/Components/FieldPropertiesForm';
// import axios from 'axios';

// export default function FormBuilderShow() {
//     const { template, fieldTypes, products } = usePage().props;
//     const [selectedField, setSelectedField] = useState(null);
//     const [showPreview, setShowPreview] = useState(false);
//     const [showEmbed, setShowEmbed] = useState(false);
//     const [previewHtml, setPreviewHtml] = useState('');
//     const [embedCode, setEmbedCode] = useState('');
//     const previewRef = useRef(null);
//     const embedRef = useRef(null);

//     // Initialize fields state
//     const [fields, setFields] = useState(template.fields || []);

//     const sensors = useSensors(
//         useSensor(PointerSensor),
//         useSensor(KeyboardSensor, {
//             coordinateGetter: sortableKeyboardCoordinates,
//         })
//     );

//     // Add a new field
//     const addField = (type) => {
//         axios.post(route('form.builder.field.store', { template: template.id }), {
//             name: fieldTypes[type].name,
//             type,
//             is_required: fieldTypes[type].is_required,
//             label: fieldTypes[type].label,
//             order: fields.length + 1,
//             properties: fieldTypes[type].properties,
//         }).then(response => {
//             setFields([...fields, response.data.field]);
//             setSelectedField(response.data.field.id);
//         });
//     };

//     // Update field properties
//     const updateField = (fieldId, data) => {
//         axios.put(route('form.builder.field.update', { field: fieldId }), data)
//             .then(response => {
//                 setFields(fields.map(f => 
//                     f.id === fieldId ? response.data.field : f
//                 ));
//             });
//     };

//     // Remove a field
//     const removeField = (fieldId) => {
//         if (confirm('Are you sure you want to remove this field?')) {
//             axios.delete(route('form.builder.field.destroy', { field: fieldId }))
//                 .then(() => {
//                     setFields(fields.filter(f => f.id !== fieldId));
//                     if (selectedField === fieldId) {
//                         setSelectedField(null);
//                     }
//                 });
//         }
//     };

//     // Handle drag and drop reordering
//     const handleDragEnd = (event) => {
//         const { active, over } = event;
        
//         if (active.id !== over.id) {
//             setFields((items) => {
//                 const oldIndex = items.findIndex(item => item.id === active.id);
//                 const newIndex = items.findIndex(item => item.id === over.id);
//                 const newItems = arrayMove(items, oldIndex, newIndex);
                
//                 // Send update to server
//                 axios.post(route('form.builder.reorder'), {
//                     orders: newItems.map((item, index) => ({
//                         field_id: item.id,
//                         order: index + 1
//                     }))
//                 });

//                 return newItems;
//             });
//         }
//     };

//     // Generate preview
//     const generatePreview = () => {
//         axios.post(route('form.builder.preview', { template: template.id }))
//             .then(response => {
//                 setPreviewHtml(response.data.html);
//                 setShowPreview(true);
//             });
//     };

//     // Generate embed code
//     const generateEmbedCode = () => {
//         axios.post(route('form.builder.embed', { template: template.id }))
//             .then(response => {
//                 setEmbedCode(response.data.embed_code);
//                 setShowEmbed(true);
//             });
//     };

//     // Save form
//     const saveForm = () => {
//         axios.post(route('form.builder.save', { template: template.id }))
//             .then(() => {
//                 // Show success message
//             });
//     };

//     return (
//         <>
//             <Head title={`Form Builder - ${template.name}`} />

//             <div className="min-h-screen bg-gray-50">
//                 {/* Header */}
//                 <div className="bg-gray-800 text-white py-4 px-8">
//                     <div className="max-w-7xl mx-auto flex justify-between items-center">
//                         <h1 className="text-xl font-semibold flex items-center gap-2">
//                             <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
//                             </svg>
//                             Form Builder - {template.name}
//                         </h1>
//                         <div className="flex gap-4">
//                             <Button onClick={saveForm} className="bg-green-600 hover:bg-green-700 flex items-center gap-2">
//                                 <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
//                                 </svg>
//                                 Save Form
//                             </Button>
//                             <Button onClick={generateEmbedCode} className="bg-blue-600 hover:bg-blue-700 flex items-center gap-2">
//                                 <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
//                                 </svg>
//                                 Embed Code
//                             </Button>
//                             <Button onClick={generatePreview} className="bg-purple-600 hover:bg-purple-700 flex items-center gap-2">
//                                 <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
//                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
//                                 </svg>
//                                 Preview
//                             </Button>
//                         </div>
//                     </div>
//                 </div>

//                 {/* Main Content - Three Panels */}
//                 <div className="max-w-7xl mx-auto py-6 px-4 grid grid-cols-3 gap-6">
//                     {/* Left Panel - Available Fields */}
//                     <div className="bg-white rounded-lg shadow overflow-hidden flex flex-col">
//                         <div className="px-4 py-3 border-b border-gray-200 font-medium text-gray-700 flex items-center gap-2">
//                             <svg className="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
//                             </svg>
//                             Available Fields
//                         </div>
//                         <div className="p-4 flex-1 overflow-y-auto grid gap-3">
//                             {Object.entries(fieldTypes).map(([type, config]) => (
//                                 <div 
//                                     key={type}
//                                     className="p-3 bg-white border border-gray-200 rounded-md cursor-pointer flex items-center gap-3 transition-all hover:border-blue-300 hover:bg-blue-50 hover:translate-x-1"
//                                     onClick={() => addField(type)}
//                                 >
//                                     <div className="w-8 h-8 bg-indigo-100 rounded flex items-center justify-center">
//                                         <i className={`fas ${config.icon} text-indigo-600`} />
//                                     </div>
//                                     <div>
//                                         <div className="font-medium text-gray-800">{config.label}</div>
//                                         <div className="text-xs text-gray-500">{config.description}</div>
//                                     </div>
//                                 </div>
//                             ))}
//                         </div>
//                     </div>

//                     {/* Middle Panel - Form Canvas */}
//                     <div className="bg-white rounded-lg shadow overflow-hidden flex flex-col">
//                         <div className="px-4 py-3 border-b border-gray-200 font-medium text-gray-700 flex items-center gap-2">
//                             <svg className="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
//                             </svg>
//                             Form Canvas
//                         </div>
//                         <div className="p-4 flex-1 overflow-y-auto">
//                             <DndContext 
//                                 sensors={sensors}
//                                 collisionDetection={closestCenter}
//                                 onDragEnd={handleDragEnd}
//                             >
//                                 <SortableContext 
//                                     items={fields}
//                                     strategy={verticalListSortingStrategy}
//                                 >
//                                     {fields.map((field) => (
//                                         <SortableItem 
//                                             key={field.id} 
//                                             id={field.id}
//                                             active={selectedField === field.id}
//                                             onClick={() => setSelectedField(field.id)}
//                                         >
//                                             <div className="flex justify-between items-center mb-2 pb-2 border-b border-gray-100">
//                                                 <div className="flex items-center gap-2">
//                                                     <div className="w-7 h-7 bg-indigo-100 rounded flex items-center justify-center">
//                                                         <i className={`fas ${fieldTypes[field.type]?.icon} text-indigo-600 text-sm`} />
//                                                     </div>
//                                                     <span className="font-medium text-gray-800">{field?.label}</span>
//                                                     {field.is_required && (
//                                                         <span className="px-2 py-1 bg-red-100 text-red-700 text-xs rounded inline-flex items-center gap-1">
//                                                             Required
//                                                         </span>
//                                                     )}
//                                                 </div>
//                                                 <div className="flex gap-1">
//                                                     <button 
//                                                         className="p-1 text-gray-500 hover:text-blue-600 hover:bg-blue-100 rounded"
//                                                         onClick={(e) => {
//                                                             e.stopPropagation();
//                                                             // Move up logic
//                                                         }}
//                                                     >
//                                                         <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 15l7-7 7 7" />
//                                                         </svg>
//                                                     </button>
//                                                     <button 
//                                                         className="p-1 text-gray-500 hover:text-blue-600 hover:bg-blue-100 rounded"
//                                                         onClick={(e) => {
//                                                             e.stopPropagation();
//                                                             // Move down logic
//                                                         }}
//                                                     >
//                                                         <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
//                                                         </svg>
//                                                     </button>
//                                                     <button 
//                                                         className="p-1 text-gray-500 hover:text-red-600 hover:bg-red-100 rounded"
//                                                         onClick={(e) => {
//                                                             e.stopPropagation();
//                                                             removeField(field.id);
//                                                         }}
//                                                     >
//                                                         <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
//                                                         </svg>
//                                                     </button>
//                                                 </div>
//                                             </div>
//                                             <div className="opacity-70 pointer-events-none" dangerouslySetInnerHTML={{ __html: field.rendered_field }} />
//                                         </SortableItem>
//                                     ))}
//                                 </SortableContext>
//                             </DndContext>
//                         </div>
//                     </div>

//                     {/* Right Panel - Field Properties */}
//                     <div className="bg-white rounded-lg shadow overflow-hidden flex flex-col">
//                         <div className="px-4 py-3 border-b border-gray-200 font-medium text-gray-700 flex items-center gap-2">
//                             <svg className="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
//                                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
//                             </svg>
//                             Field Properties
//                         </div>
//                         <div className="p-6 flex-1 overflow-y-auto">
//                             {selectedField ? (
//                                 <FieldPropertiesForm 
//                                     field={fields.find(f => f.id === selectedField)}
//                                     onUpdate={updateField}
//                                     products={[{id: 1, name: 'ultamax'}, {id: 2, name: 'maximpress'}, {id:3, name: 'azoconbo'}]}
//                                 />
//                             ) : (
//                                 <div className="flex flex-col items-center justify-center h-full text-gray-500 text-center">
//                                     <svg className="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
//                                     </svg>
//                                     <h3 className="text-lg font-medium mb-1">No Field Selected</h3>
//                                     <p className="max-w-xs">Click on a field in the canvas to edit its properties</p>
//                                 </div>
//                             )}
//                         </div>
//                     </div>
//                 </div>
//             </div>

//             {/* Preview Modal */}
//             <Modal show={showPreview} onClose={() => setShowPreview(false)} maxWidth="4xl">
//                 <div className="bg-white rounded-lg overflow-hidden">
//                     <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
//                         <h3 className="text-xl font-semibold">Form Preview</h3>
//                         <button 
//                             type="button" 
//                             className="text-gray-400 hover:text-gray-500"
//                             onClick={() => setShowPreview(false)}
//                         >
//                             <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
//                             </svg>
//                         </button>
//                     </div>
//                     <div className="p-6">
//                         <div ref={previewRef} dangerouslySetInnerHTML={{ __html: previewHtml }} />
//                     </div>
//                     <div className="px-6 py-4 border-t border-gray-200 flex justify-end">
//                         <Button onClick={() => setShowPreview(false)} className="bg-gray-600 hover:bg-gray-700">
//                             Close
//                         </Button>
//                     </div>
//                 </div>
//             </Modal>

//             {/* Embed Code Modal */}
//             <Modal show={showEmbed} onClose={() => setShowEmbed(false)}>
//                 <div className="bg-white rounded-lg overflow-hidden">
//                     <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
//                         <h3 className="text-xl font-semibold">Embed Code</h3>
//                         <button 
//                             type="button" 
//                             className="text-gray-400 hover:text-gray-500"
//                             onClick={() => setShowEmbed(false)}
//                         >
//                             <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
//                             </svg>
//                         </button>
//                     </div>
//                     <div className="p-6">
//                         <p className="text-gray-600 mb-2">Copy this code and paste it into your website:</p>
//                         <textarea 
//                             ref={embedRef}
//                             className="w-full p-3 border border-gray-300 rounded-md font-mono text-sm min-h-32"
//                             value={embedCode}
//                             readOnly
//                         />
//                         <div className="mt-4 p-3 bg-gray-50 rounded-md">
//                             <h4 className="text-sm font-semibold mb-2 text-gray-700">Implementation Notes:</h4>
//                             <ul className="text-sm text-gray-600 list-disc pl-5">
//                                 <li>Paste this code where you want the form to appear</li>
//                                 <li>Make sure the target page has enough width for the form</li>
//                                 <li>Form submissions will be saved to your dashboard</li>
//                             </ul>
//                         </div>
//                     </div>
//                     <div className="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
//                         <Button onClick={() => setShowEmbed(false)} className="bg-gray-600 hover:bg-gray-700">
//                             Close
//                         </Button>
//                         <Button 
//                             onClick={() => {
//                                 embedRef.current.select();
//                                 document.execCommand('copy');
//                                 // Show copied notification
//                             }}
//                             className="bg-blue-600 hover:bg-blue-700"
//                         >
//                             Copy Code
//                         </Button>
//                     </div>
//                 </div>
//             </Modal>
//         </>
//     );
// }