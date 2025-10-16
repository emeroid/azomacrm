import { useForm } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import _ from 'lodash'

const fieldTypeProperties = {
    text: [
        { name: 'placeholder', label: 'Placeholder', type: 'text' },
        { name: 'maxlength', label: 'Max Length', type: 'number' },
    ],
    number: [
        { name: 'placeholder', label: 'Placeholder', type: 'text' },
        { name: 'min', label: 'Minimum Value', type: 'number' },
        { name: 'max', label: 'Maximum Value', type: 'number' },
    ],
    email: [
        { name: 'placeholder', label: 'Placeholder', type: 'text' },
    ],
    tel: [
        { name: 'placeholder', label: 'Placeholder', type: 'text' },
    ],
    textarea: [
        { name: 'placeholder', label: 'Placeholder', type: 'text' },
        { name: 'rows', label: 'Rows', type: 'number' },
    ],
    select: [
        { name: 'placeholder', label: 'Placeholder', type: 'text' },
        { name: 'options', label: 'Options (comma-separated)', type: 'text' },
    ],
    radio: [
        { name: 'placeholder', label: 'Placeholder', type: 'text' },
        { name: 'options', label: 'Options (comma-separated)', type: 'text' },
    ],
    checkbox: [
        { name: 'text', label: 'Checkbox Label', type: 'text' },
    ],
    date: [
        { name: 'format', label: 'Date Format (e.g. YYYY-MM-DD)', type: 'text' },
    ],
    product_selector: [
        { name: 'products', label: 'Products', type: 'product_selector' },
    ],
};

export default function FieldPropertiesForm({ field, onUpdate, products }) {
    // Initialize form data based on the current field
    const initializeFormData = () => {
        const baseData = {
            name: field.name,
            label: field.label,
            type: field.type,
            is_required: field.is_required,
            ...(field.properties || {}) // Safely include properties
        };

        // Ensure products array exists for product_selector
        if (field.type === 'product_selector') {
            baseData.products = field.properties?.products || [{ product: '', price: '', note: '' }];
        }

        return baseData;
    };

    const { data, setData, errors, processing, reset } = useForm(initializeFormData());

    const [expandedSections, setExpandedSections] = useState(
        field.type === 'product_selector' 
            ? (field.properties?.products || [{}]).map((_, i) => i === 0)
            : []
    );

    // Reset form when field changes
    useEffect(() => {
        const newData = initializeFormData();
        reset(newData);
        
        if (field.type === 'product_selector') {
            setExpandedSections(
                (newData.products || [{}]).map((_, i) => i === 0)
            );
        } else {
            setExpandedSections([]);
        }
    }, [field.id, field.type]); // Reset when field ID or type changes

    const handleSubmit = (e) => {
        e.preventDefault();
        
        // Prepare the data to send, excluding the type field
        const updateData = {
            label: data.label,
            is_required: data.is_required,
            properties: _.omit(data, ['name', 'label', 'type', 'is_required'])
        };
        
        onUpdate(field.id, updateData);
    };

    const handleChange = (e, productIndex = null) => {
        const { name, value, type, checked } = e.target;
        
        if (field.type === 'product_selector' && productIndex !== null) {
            const updatedProducts = [...(data.products || [])];
            updatedProducts[productIndex] = {
                ...updatedProducts[productIndex],
                [name]: type === 'checkbox' ? checked : value
            };
            setData('products', updatedProducts);
        } else {
            setData(name, type === 'checkbox' ? checked : value);
        }
    };

    const addProduct = () => {
        const newProduct = { product: '', price: '', note: '' };
        setData('products', [...(data.products || []), newProduct]);
        setExpandedSections([...expandedSections, true]);
    };

    const removeProduct = (index) => {
        if (!data.products || data.products.length <= 1) return;
        
        const updatedProducts = data.products.filter((_, i) => i !== index);
        setData('products', updatedProducts);
        
        const updatedExpanded = [...expandedSections];
        updatedExpanded.splice(index, 1);
        setExpandedSections(updatedExpanded);
    };

    const toggleSection = (index) => {
        const updatedExpanded = [...expandedSections];
        updatedExpanded[index] = !updatedExpanded[index];
        setExpandedSections(updatedExpanded);
    };

    const renderProductSelector = () => {
        if (!Array.isArray(data.products)) return null;

        return data.products.map((product, index) => (
            <div key={index} className="mb-6 border border-gray-200 rounded-lg p-4">
                <div className="flex justify-between items-center cursor-pointer" onClick={() => toggleSection(index)}>
                    <h3 className="text-lg font-medium text-gray-900">
                        Product #{index + 1} {product.product ? `(${products.find(p => p.id == product.product)?.name || product.product})` : ''}
                    </h3>
                    <div className="flex items-center">
                        <button
                            type="button"
                            onClick={(e) => {
                                e.stopPropagation();
                                removeProduct(index);
                            }}
                            className="text-red-600 hover:text-red-800 mr-4"
                            disabled={!data.products || data.products.length <= 1}
                        >
                            Remove
                        </button>
                        <svg
                            className={`h-5 w-5 text-gray-500 transform transition-transform ${expandedSections[index] ? 'rotate-180' : ''}`}
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                        </svg>
                    </div>
                </div>

                {expandedSections[index] && (
                    <div className="mt-4 space-y-4">
                        <div>
                            <label htmlFor={`product-${index}`} className="block text-sm font-medium text-gray-700 mb-1">
                                Select Product
                            </label>
                            <select
                                id={`product-${index}`}
                                name="product"
                                value={product.product || ''}
                                onChange={(e) => handleChange(e, index)}
                                className="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Select a product</option>
                                {products.map((prod) => (
                                    <option key={prod.id} value={prod.id}>
                                        {prod.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label htmlFor={`price-${index}`} className="block text-sm font-medium text-gray-700 mb-1">
                                Price
                            </label>
                            <input
                                id={`price-${index}`}
                                name="price"
                                type="number"
                                value={product.price || ''}
                                onChange={(e) => handleChange(e, index)}
                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>

                        <div>
                            <label htmlFor={`note-${index}`} className="block text-sm font-medium text-gray-700 mb-1">
                                Note
                            </label>
                            <input
                                id={`note-${index}`}
                                name="note"
                                type="text"
                                value={product.note || ''}
                                onChange={(e) => handleChange(e, index)}
                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                    </div>
                )}
            </div>
        ));
    };

    const renderDynamicProperties = () => {
        if (field.type === 'product_selector') {
            return (
                <div>
                    {renderProductSelector()}
                    <button
                        type="button"
                        onClick={addProduct}
                        className="mt-2 inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg className="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Another Product
                    </button>
                </div>
            );
        }

        const props = fieldTypeProperties[field.type] || [];
        return props.map((prop) => (
            <div key={prop.name} className="mb-4">
                <label htmlFor={prop.name} className="block text-sm font-medium text-gray-700 mb-1">
                    {prop.label}
                </label>
                
                {prop.type === 'textarea' ? (
                    <textarea
                        id={prop.name}
                        name={prop.name}
                        rows={3}
                        value={data[prop.name] || ''}
                        onChange={handleChange}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                ) : prop.type === 'checkbox' ? (
                    <div className="flex items-center">
                        <input
                            id={prop.name}
                            name={prop.name}
                            type="checkbox"
                            checked={data[prop.name] || false}
                            onChange={handleChange}
                            className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                    </div>
                ) : prop.type === 'select' ? (
                    <select
                        id={prop.name}
                        name={prop.name}
                        value={data[prop.name] || ''}
                        onChange={handleChange}
                        className="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="">Select an option</option>
                        {prop.name === 'product' && products?.map((prod) => (
                            <option key={prod.id} value={prod.id}>{prod.name}</option>
                        ))}
                    </select>
                ) : (
                    <input
                        id={prop.name}
                        name={prop.name}
                        type={prop.type}
                        value={data[prop.name] || ''}
                        onChange={handleChange}
                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                )}
                
                {errors[prop.name] && (
                    <p className="mt-1 text-sm text-red-600">{errors[prop.name]}</p>
                )}
            </div>
        ));
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div className="mb-4">
                <label htmlFor="label" className="block text-sm font-medium text-gray-700 mb-1">
                    Field Label
                </label>
                <input
                    id="label"
                    name="label"
                    type="text"
                    value={data.label || ''}
                    onChange={handleChange}
                    className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
                {errors.label && <p className="mt-1 text-sm text-red-600">{errors.label}</p>}
            </div>

            <div className="flex items-center mb-4">
                <input
                    id="is_required"
                    name="is_required"
                    type="checkbox"
                    checked={data.is_required || false}
                    onChange={handleChange}
                    className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
                <label htmlFor="is_required" className="ml-2 block text-sm text-gray-700">
                    Required Field
                </label>
            </div>

            {renderDynamicProperties()}

            <div className="flex justify-end">
                <button
                    type="submit"
                    disabled={processing}
                    className="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {processing ? 'Updating...' : 'Update Field'}
                </button>
            </div>
        </form>
    );
}


// import { useForm } from '@inertiajs/react';
// import { useState } from 'react';

// const fieldTypeProperties = {
//     text: [
//         { name: 'placeholder', label: 'Placeholder', type: 'text' },
//         { name: 'maxlength', label: 'Max Length', type: 'number' },
//     ],
//     number: [
//         { name: 'min', label: 'Minimum Value', type: 'number' },
//         { name: 'max', label: 'Maximum Value', type: 'number' },
//         { name: 'step', label: 'Step', type: 'number' },
//         { name: 'placeholder', label: 'Placeholder', type: 'text' },
//     ],
//     email: [
//         { name: 'placeholder', label: 'Placeholder', type: 'text' },
//     ],
//     tel: [
//         { name: 'placeholder', label: 'Placeholder', type: 'text' },
//     ],
//     textarea: [
//         { name: 'placeholder', label: 'Placeholder', type: 'text' },
//         { name: 'rows', label: 'Rows', type: 'number' },
//     ],
//     select: [
//         { name: 'placeholder', label: 'Placeholder', type: 'text' },
//         { name: 'options', label: 'Options (comma-separated)', type: 'text' },
//     ],
//     radio: [
//         { name: 'placeholder', label: 'Placeholder', type: 'text' },
//         { name: 'options', label: 'Options (comma-separated)', type: 'text' },
//     ],
//     checkbox: [
//         { name: 'text', label: 'Checkbox Label', type: 'text' },
//     ],
//     date: [
//         { name: 'format', label: 'Date Format (e.g. YYYY-MM-DD)', type: 'text' },
//     ],
//     product_selector: [
//         { name: 'product', label: 'Select Product', type: 'select' },
//         { name: 'price', label: 'Price', type: 'number' },
//         { name: 'note', label: 'Note', type: 'text' },
//     ],
// };

// export default function FieldPropertiesForm({ field, onUpdate, products }) {
//     const initialProductData = field.properties?.products || [
//         { product: '', price: '', note: '' }
//     ];

//     const { data, setData, errors, processing } = useForm({
//         name: field.name,
//         label: field.label,
//         type: field.type,
//         is_required: field.is_required,
//         products: initialProductData,
//         ...field.properties,
//     });

//     const [expandedSections, setExpandedSections] = useState(
//         initialProductData.map((_, index) => index === 0) // First one expanded by default
//     );

//     const handleSubmit = (e) => {
//         e.preventDefault();
//         onUpdate(field.id, data);
//     };

//     const handleChange = (e, productIndex) => {
//         const { name, value, type, checked } = e.target;
        
//         if (field.type === 'product_selector') {
//             const updatedProducts = [...data.products];
//             updatedProducts[productIndex][name] = type === 'checkbox' ? checked : value;
//             setData('products', updatedProducts);
//         } else {
//             setData(name, type === 'checkbox' ? checked : value);
//         }
//     };

//     const addProduct = () => {
//         const newProduct = { product: '', price: '', note: '' };
//         setData('products', [...data.products, newProduct]);
//         setExpandedSections([...expandedSections, true]);
//     };

//     const removeProduct = (index) => {
//         if (data.products.length <= 1) return; // Keep at least one product
        
//         const updatedProducts = data.products.filter((_, i) => i !== index);
//         setData('products', updatedProducts);
        
//         const updatedExpanded = [...expandedSections];
//         updatedExpanded.splice(index, 1);
//         setExpandedSections(updatedExpanded);
//     };

//     const toggleSection = (index) => {
//         const updatedExpanded = [...expandedSections];
//         updatedExpanded[index] = !updatedExpanded[index];
//         setExpandedSections(updatedExpanded);
//     };

//     const renderProductSelector = () => {
//         return data.products.map((product, index) => (
//             <div key={index} className="mb-6 border border-gray-200 rounded-lg p-4">
//                 <div className="flex justify-between items-center cursor-pointer" onClick={() => toggleSection(index)}>
//                     <h3 className="text-lg font-medium text-gray-900">
//                         Product #{index + 1} {product.product ? `(${product.product})` : ''}
//                     </h3>
//                     <div className="flex items-center">
//                         <button
//                             type="button"
//                             onClick={(e) => {
//                                 e.stopPropagation();
//                                 removeProduct(index);
//                             }}
//                             className="text-red-600 hover:text-red-800 mr-4"
//                             disabled={data.products.length <= 1}
//                         >
//                             Remove
//                         </button>
//                         <svg
//                             className={`h-5 w-5 text-gray-500 transform transition-transform ${expandedSections[index] ? 'rotate-180' : ''}`}
//                             viewBox="0 0 20 20"
//                             fill="currentColor"
//                         >
//                             <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
//                         </svg>
//                     </div>
//                 </div>

//                 {expandedSections[index] && (
//                     <div className="mt-4 space-y-4">
//                         <div>
//                             <label htmlFor={`product-${index}`} className="block text-sm font-medium text-gray-700 mb-1">
//                                 Select Product
//                             </label>
//                             <select
//                                 id={`product-${index}`}
//                                 name="product"
//                                 value={product.product}
//                                 onChange={(e) => handleChange(e, index)}
//                                 className="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
//                             >
//                                 <option value="">Select a product</option>
//                                 {products.map((prod) => (
//                                     <option key={prod.id} value={prod.id}>
//                                         {prod.name}
//                                     </option>
//                                 ))}
//                             </select>
//                         </div>

//                         <div>
//                             <label htmlFor={`price-${index}`} className="block text-sm font-medium text-gray-700 mb-1">
//                                 Price
//                             </label>
//                             <input
//                                 id={`price-${index}`}
//                                 name="price"
//                                 type="number"
//                                 value={product.price}
//                                 onChange={(e) => handleChange(e, index)}
//                                 className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
//                             />
//                         </div>

//                         <div>
//                             <label htmlFor={`note-${index}`} className="block text-sm font-medium text-gray-700 mb-1">
//                                 Note
//                             </label>
//                             <input
//                                 id={`note-${index}`}
//                                 name="note"
//                                 type="text"
//                                 value={product.note}
//                                 onChange={(e) => handleChange(e, index)}
//                                 className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
//                             />
//                         </div>
//                     </div>
//                 )}
//             </div>
//         ));
//     };

//     const renderDynamicProperties = () => {
//         if (field.type === 'product_selector') {
//             return (
//                 <div>
//                     {renderProductSelector()}
//                     <button
//                         type="button"
//                         onClick={addProduct}
//                         className="mt-2 inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
//                     >
//                         <svg className="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
//                         </svg>
//                         Add Another Product
//                     </button>
//                 </div>
//             );
//         }
//         const props = fieldTypeProperties[field.type] || [];
//         return props.map((prop) => (
//             <div key={prop.name} className="mb-4">
//                 <label htmlFor={prop.name} className="block text-sm font-medium text-gray-700 mb-1">
//                     {prop.label}
//                 </label>
                
//                 {prop.type === 'textarea' ? (
//                     <textarea
//                         id={prop.name}
//                         name={prop.name}
//                         rows={3}
//                         value={data[prop.name] || ''}
//                         onChange={handleChange}
//                         placeholder={data[prop.placeholder] || ''}
//                         className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
//                     />
//                 ) : prop.type === 'checkbox' ? (
//                     <div className="flex items-center">
//                         <input
//                             id={prop.name}
//                             name={prop.name}
//                             type="checkbox"
//                             placeholder={data[prop.placeholder] || ''}
//                             checked={data[prop.name] || false}
//                             onChange={handleChange}
//                             className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
//                         />
//                     </div>
//                 ) : prop.type === 'select' ? (
//                     <select
//                         id={prop.name}
//                         name={prop.name}
//                         value={data[prop.name] || ''}
//                         onChange={handleChange}
//                         placeholder={data[prop.placeholder] || ''}
//                         className="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
//                     >
//                         <option value=""> Select </option>
//                         {products.map((item) => (<option value={item.id}> {item.name} </option>))}
//                     </select>
//                 ) : (
//                     <input
//                         id={prop.name}
//                         name={prop.name}
//                         type={prop.type}
//                         value={data[prop.name] || ''}
//                         placeholder={data[prop.placeholder] || ''}
//                         onChange={handleChange}
//                         className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
//                     />
//                 )}
                
//                 {errors[prop.name] && (
//                     <p className="mt-1 text-sm text-red-600">{errors[prop.name]}</p>
//                 )}
//             </div>
//         ));
//     };

//     return (
//         <form onSubmit={handleSubmit} className="space-y-6">
//             {renderDynamicProperties()}

//             <div className="flex justify-end">
//                 <button
//                     type="submit"
//                     disabled={processing}
//                     className="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
//                 >
//                     {processing ? 'Updating...' : 'Update Field'}
//                 </button>
//             </div>
//         </form>
//     );
// }