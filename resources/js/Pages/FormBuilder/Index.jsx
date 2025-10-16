import { Head, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function TemplateIndex({ templates }) {
    return (
        <div className="min-h-screen bg-gray-50">
            <Head title="Form Templates" />
            
            <div className="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                <div className="mb-8">
                    <h1 className="text-2xl font-bold text-gray-900">Start with a Template</h1>
                    <p className="mt-2 text-sm text-gray-600">
                        Choose a template to create your custom form
                    </p>
                </div>
                {console.log("TEMP >>> ", templates)}

                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {templates.map((template) => (
                        <TemplateCard key={template.id} template={template} />
                    ))}
                </div>
            </div>
        </div>
    );
}

function TemplateCard({ template }) {
    const [isCreating, setIsCreating] = useState(false);
    const {data: formData, setData: setFormData, post, errors} = useForm({
        name: '',
        redirect_url: ''
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        setIsCreating(true);
        post(route('template.create', template.id), {
            onFinish: () => setIsCreating(false),
        });
    };

    return (
        <div className="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
            <div className="px-4 py-5 sm:p-6">
                <h3 className="text-lg font-medium text-gray-900">{template.name}</h3>
                <p className="mt-1 text-sm text-gray-500">{template.description}</p>
                
                <form onSubmit={handleSubmit} className="mt-6 space-y-4">
                    <div>
                        <label htmlFor={`name-${template.id}`} className="block text-sm font-medium text-gray-700">
                            Form Name *
                        </label>
                        <input
                            type="text"
                            id={`name-${template.id}`}
                            required
                            value={formData.name}
                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                        {errors.name && (<p className='text-red-500'>{errors.name}</p>)}
                    </div>
                    
                    <div>
                        <label htmlFor={`redirect-${template.id}`} className="block text-sm font-medium text-gray-700">
                            Redirect URL After Submission
                        </label>
                        <input
                            type="url"
                            id={`redirect-${template.id}`}
                            value={formData.redirect_url}
                            onChange={(e) => setFormData({ ...formData, redirect_url: e.target.value })}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="https://example.com/thank-you"
                        />
                        {errors.redirect_url && (<p className='text-red-500'>{errors.redirect_url}</p>)}
                    </div>
                    
                    <div className="pt-2">
                        <button
                            type="submit"
                            disabled={isCreating}
                            className="w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        >
                            {isCreating ? (
                                <>
                                    <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Creating...
                                </>
                            ) : 'Use This Template'}
                        </button>
                    </div>
                </form>
            </div>
            
            <div className="bg-gray-50 px-4 py-4 sm:px-6">
                <div className="text-xs text-gray-500">
                    {template.fields_count} fields • Last updated {new Date(template.updated_at).toLocaleDateString()}
                </div>
            </div>
        </div>
    );
}