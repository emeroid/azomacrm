import React from 'react';
import AuthenticatedLayout from '@/Layouts/WaAuthLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Edit({ auth, device }) {
    // Initialize the form data with the current device values
    const { data, setData, put, processing, errors } = useForm({
        name: device.name || '',
        min_delay: device.min_delay || 10, // Default min_delay if null
        max_delay: device.max_delay || 60, // Default max_delay if null
    });

    const submit = (e) => {
        e.preventDefault();
        // Use Inertia's PUT method to submit the form
        put(route('devices.update', device.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Optionally show a success message or redirect
                // Inertia usually handles redirecting to the index page upon successful update
            }
        });
    };

    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div>
                    <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Edit WhatsApp Device</h2>
                    <p className="text-gray-600 mt-1">
                        Customize settings for: <span className="font-bold">{device.name || `Device #${device.id}`}</span>
                    </p>
                </div>
            }
        >
            <Head title={`Edit Device ${device.id}`} />

            <div className="py-8">
                <div className="max-w-xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white rounded-2xl shadow-lg p-8">
                        <form onSubmit={submit}>
                            
                            {/* Read-Only Phone Number */}
                            <div className="mb-6">
                                <label className="block text-sm font-medium text-gray-700">Phone Number (Read Only)</label>
                                <div className="mt-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-lg font-mono text-gray-900">
                                    +{device.phone_number}
                                </div>
                                <p className="mt-1 text-sm text-gray-500">This number cannot be changed.</p>
                            </div>
                            
                            {/* Device Name */}
                            <div className="mb-6">
                                <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                    Device Name
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="e.g., Team Alpha WhatsApp"
                                />
                                {errors.name && <p className="text-sm text-red-600 mt-1">{errors.name}</p>}
                            </div>

                            {/* Min/Max Delay */}
                            <div className="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label htmlFor="min_delay" className="block text-sm font-medium text-gray-700">
                                        Minimum Delay (seconds)
                                    </label>
                                    <input
                                        id="min_delay"
                                        type="number"
                                        min="1"
                                        step="1"
                                        value={data.min_delay}
                                        onChange={(e) => setData('min_delay', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                    {errors.min_delay && <p className="text-sm text-red-600 mt-1">{errors.min_delay}</p>}
                                </div>
                                <div>
                                    <label htmlFor="max_delay" className="block text-sm font-medium text-gray-700">
                                        Maximum Delay (seconds)
                                    </label>
                                    <input
                                        id="max_delay"
                                        type="number"
                                        min="1"
                                        step="1"
                                        value={data.max_delay}
                                        onChange={(e) => setData('max_delay', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                    {errors.max_delay && <p className="text-sm text-red-600 mt-1">{errors.max_delay}</p>}
                                </div>
                            </div>
                            
                            <div className="flex justify-end space-x-3 mt-8">
                                <Link 
                                    href={route('devices.index')} 
                                    className="px-4 py-2 text-sm font-semibold rounded-lg text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className={`px-4 py-2 text-sm font-semibold rounded-lg shadow-md transition-colors ${
                                        processing 
                                            ? 'bg-indigo-400 text-white cursor-not-allowed'
                                            : 'bg-indigo-600 text-white hover:bg-indigo-700'
                                    }`}
                                >
                                    {processing ? 'Saving...' : 'Save Changes'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}