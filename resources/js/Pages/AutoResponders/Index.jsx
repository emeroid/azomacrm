// resources/js/Pages/AutoResponders/Index.jsx
import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/WaAuthLayout';

export default function Index({ auth, responders }) {
    
    const deleteResponder = (id) => {
        if (confirm('Are you sure you want to delete this auto-responder?')) {
            router.delete(route('auto-responders.destroy', id));
        }
    };

    const toggleResponderStatus = (id, currentStatus) => {
        router.patch(route('auto-responders.update-status', id), {
            is_active: !currentStatus
        });
    };

    const getStats = () => {
        const total = responders.length;
        const active = responders.filter(r => r.is_active).length;
        const inactive = responders.filter(r => !r.is_active).length;
        const totalHits = responders.reduce((sum, r) => sum + (r.trigger_count || 0), 0);

        return { total, active, inactive, totalHits };
    };

    const stats = getStats();

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    };

    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Auto Responders 🤖</h2>
                        <p className="text-gray-600 mt-1">Manage automated response rules</p>
                    </div>
                    <div className="flex space-x-3 mt-4 sm:mt-0">
                        <Link 
                            href={route('auto-responders.create')} 
                            className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            Create New Rule
                        </Link>
                        <Link 
                            href={route('analytics.index')} 
                            className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            View Analytics
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Auto Responders" />

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* Stats Overview */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-indigo-400">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">Total Rules</p>
                                    <p className="text-2xl font-bold text-gray-900">{stats.total}</p>
                                </div>
                                <div className="p-3 bg-indigo-100 rounded-lg">
                                    <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-green-400">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">Active</p>
                                    <p className="text-2xl font-bold text-gray-900">{stats.active}</p>
                                </div>
                                <div className="p-3 bg-green-100 rounded-lg">
                                    <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-gray-400">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">Inactive</p>
                                    <p className="text-2xl font-bold text-gray-900">{stats.inactive}</p>
                                </div>
                                <div className="p-3 bg-gray-100 rounded-lg">
                                    <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-purple-400">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">Total Triggers</p>
                                    <p className="text-2xl font-bold text-gray-900">{stats.totalHits}</p>
                                </div>
                                <div className="p-3 bg-purple-100 rounded-lg">
                                    <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Rules Table */}
                    <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center space-x-3">
                                    <div className="p-2 bg-blue-100 rounded-lg">
                                        <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 className="text-lg font-bold text-gray-900">All Auto Responder Rules</h3>
                                        <p className="text-gray-600">Manage your automated response triggers</p>
                                    </div>
                                </div>
                                <span className="bg-indigo-100 text-indigo-800 text-sm font-medium px-3 py-1 rounded-full">
                                    {responders.length} rules
                                </span>
                            </div>
                        </div>

                        {responders.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Keyword
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Response Preview
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Triggers
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Created
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {responders.map((responder) => (
                                            <tr key={responder.id} className="hover:bg-gray-50 transition-colors">
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex items-center space-x-2">
                                                        <span className="font-mono bg-blue-100 text-blue-800 px-3 py-1 rounded-lg text-sm font-medium border border-blue-200">
                                                            {responder.keyword}
                                                        </span>
                                                        {responder.case_sensitive && (
                                                            <span className="text-xs text-gray-500" title="Case sensitive">Aa</span>
                                                        )}
                                                        {responder.exact_match && (
                                                            <span className="text-xs text-gray-500" title="Exact match">✓</span>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="text-sm text-gray-900 max-w-md truncate">
                                                        {responder.response_message}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <button
                                                        onClick={() => toggleResponderStatus(responder.id, responder.is_active)}
                                                        className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium cursor-pointer transition-colors ${
                                                            responder.is_active
                                                                ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                                                : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
                                                        }`}
                                                    >
                                                        {responder.is_active ? 'ACTIVE' : 'INACTIVE'}
                                                    </button>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {responder.trigger_count || 0}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {formatDate(responder.created_at)}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div className="flex space-x-2">
                                                        <button 
                                                            onClick={() => deleteResponder(responder.id)}
                                                            className="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-lg text-xs font-medium transition-colors flex items-center space-x-1"
                                                        >
                                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                            <span>Delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <svg className="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p className="text-gray-500 text-lg mb-2">No auto-responder rules yet</p>
                                <p className="text-gray-400 text-sm mb-6">Create your first rule to start automating responses</p>
                                <Link
                                    href={route('auto-responders.create')}
                                    className="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors"
                                >
                                    Create First Rule
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}




// // resources/js/Pages/AutoResponders/Index.jsx
// import React, { useState } from 'react';
// import { Head, useForm, router, Link } from '@inertiajs/react';
// import AuthenticatedLayout from '@/Layouts/WaAuthLayout';

// export default function Index({ auth, responders }) {
//     const [activeTab, setActiveTab] = useState('create');
//     const { data, setData, post, processing, errors, reset } = useForm({
//         keyword: '',
//         response_message: '',
//     });

//     function handleSubmit(e) {
//         e.preventDefault();
//         post(route('auto-responders.store'), {
//             preserveScroll: true,
//             onSuccess: () => {
//                 reset();
//                 setActiveTab('list');
//             },
//         });
//     }

//     function deleteResponder(id) {
//         if (confirm('Are you sure you want to delete this auto-responder?')) {
//             router.delete(route('auto-responders.destroy', id), {
//                 preserveScroll: true,
//             });
//         }
//     }

//     return (
//         <AuthenticatedLayout
//             auth={auth}
//             header={
//                 <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
//                     <div>
//                         <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Auto Responders 🤖</h2>
//                         <p className="text-gray-600 mt-1">Manage automated response rules</p>
//                     </div>
//                     <div className="flex space-x-3 mt-4 sm:mt-0">
//                         <Link 
//                             href={route('analytics.index')} 
//                             className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
//                         >
//                             View Analytics
//                         </Link>
//                     </div>
//                 </div>
//             }
//         >
//             <Head title="Auto Responders" />

//             <div className="py-8">
//                 <div className="max-w-6xl mx-auto sm:px-6 lg:px-8">
                    
//                     {/* Tab Navigation */}
//                     <div className="bg-white rounded-2xl shadow-sm mb-6">
//                         <div className="border-b border-gray-200">
//                             <nav className="flex -mb-px">
//                                 <button
//                                     onClick={() => setActiveTab('create')}
//                                     className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
//                                         activeTab === 'create'
//                                             ? 'border-indigo-500 text-indigo-600'
//                                             : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
//                                     }`}
//                                 >
//                                     <div className="flex items-center space-x-2">
//                                         <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
//                                         </svg>
//                                         <span>Create New Rule</span>
//                                     </div>
//                                 </button>
//                                 <button
//                                     onClick={() => setActiveTab('list')}
//                                     className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
//                                         activeTab === 'list'
//                                             ? 'border-indigo-500 text-indigo-600'
//                                             : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
//                                     }`}
//                                 >
//                                     <div className="flex items-center space-x-2">
//                                         <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
//                                         </svg>
//                                         <span>Existing Rules ({responders.length})</span>
//                                     </div>
//                                 </button>
//                             </nav>
//                         </div>
//                     </div>

//                     <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
//                         {/* CREATE NEW FORM - Only show when active */}
//                         {activeTab === 'create' && (
//                             <div className="bg-white rounded-2xl shadow-lg p-6 lg:p-8">
//                                 <div className="flex items-center space-x-3 mb-6">
//                                     <div className="p-2 bg-indigo-100 rounded-lg">
//                                         <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
//                                         </svg>
//                                     </div>
//                                     <h3 className="text-xl font-bold text-gray-900">Create New Rule</h3>
//                                 </div>
                                
//                                 <form onSubmit={handleSubmit} className="space-y-6">
//                                     <div>
//                                         <label htmlFor="keyword" className="block text-sm font-medium text-gray-700 mb-2">
//                                             Trigger Keyword
//                                         </label>
//                                         <input
//                                             type="text"
//                                             id="keyword"
//                                             value={data.keyword}
//                                             onChange={(e) => setData('keyword', e.target.value)}
//                                             className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
//                                             placeholder="e.g., price, info, hours, support"
//                                         />
//                                         {errors.keyword && <p className="text-red-600 text-sm mt-2">{errors.keyword}</p>}
//                                         <p className="text-gray-500 text-xs mt-2">When someone sends this keyword, the auto-response will trigger</p>
//                                     </div>
                                    
//                                     <div>
//                                         <label htmlFor="response_message" className="block text-sm font-medium text-gray-700 mb-2">
//                                             Response Message
//                                         </label>
//                                         <textarea
//                                             id="response_message"
//                                             value={data.response_message}
//                                             onChange={(e) => setData('response_message', e.target.value)}
//                                             className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
//                                             rows="5"
//                                             placeholder="Enter the automated response message..."
//                                         ></textarea>
//                                         {errors.response_message && <p className="text-red-600 text-sm mt-2">{errors.response_message}</p>}
//                                     </div>

//                                     <div className="flex justify-end space-x-4 pt-4">
//                                         <button
//                                             type="button"
//                                             onClick={() => setActiveTab('list')}
//                                             className="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
//                                         >
//                                             Cancel
//                                         </button>
//                                         <button 
//                                             type="submit" 
//                                             className="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors disabled:opacity-50"
//                                             disabled={processing}
//                                         >
//                                             {processing ? 'Saving...' : 'Save Rule'}
//                                         </button>
//                                     </div>
//                                 </form>
//                             </div>
//                         )}

//                         {/* EXISTING RULES LIST - Always visible but highlighted when active */}
//                         <div className={`bg-white rounded-2xl shadow-lg p-6 lg:p-8 transition-all duration-300 ${
//                             activeTab === 'list' ? 'ring-2 ring-indigo-500' : ''
//                         }`}>
//                             <div className="flex items-center justify-between mb-6">
//                                 <div className="flex items-center space-x-3">
//                                     <div className="p-2 bg-blue-100 rounded-lg">
//                                         <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
//                                         </svg>
//                                     </div>
//                                     <h3 className="text-xl font-bold text-gray-900">Active Rules</h3>
//                                 </div>
//                                 <span className="bg-indigo-100 text-indigo-800 text-sm font-medium px-3 py-1 rounded-full">
//                                     {responders.length} rules
//                                 </span>
//                             </div>
                            
//                             {responders.length > 0 ? (
//                                 <div className="space-y-4">
//                                     {responders.map((responder) => (
//                                         <div key={responder.id} className="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow group">
//                                             <div className="flex justify-between items-start">
//                                                 <div className="flex-1">
//                                                     <div className="flex items-center space-x-3 mb-2">
//                                                         <span className="font-semibold text-gray-900">Keyword:</span>
//                                                         <span className="font-mono bg-gray-100 px-3 py-1 rounded-lg text-sm border">
//                                                             {responder.keyword}
//                                                         </span>
//                                                     </div>
//                                                     <p className="text-gray-600 text-sm leading-relaxed bg-gray-50 p-3 rounded-lg">
//                                                         {responder.response_message}
//                                                     </p>
//                                                 </div>
//                                                 <button 
//                                                     onClick={() => deleteResponder(responder.id)}
//                                                     className="ml-4 p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors opacity-0 group-hover:opacity-100"
//                                                     title="Delete rule"
//                                                 >
//                                                     <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                                         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
//                                                     </svg>
//                                                 </button>
//                                             </div>
//                                         </div>
//                                     ))}
//                                 </div>
//                             ) : (
//                                 <div className="text-center py-12">
//                                     <svg className="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//                                         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
//                                     </svg>
//                                     <p className="text-gray-500 text-lg mb-2">No auto-responder rules yet</p>
//                                     <p className="text-gray-400 text-sm mb-6">Create your first rule to start automating responses</p>
//                                     <button
//                                         onClick={() => setActiveTab('create')}
//                                         className="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors"
//                                     >
//                                         Create First Rule
//                                     </button>
//                                 </div>
//                             )}
//                         </div>

//                     </div>
//                 </div>
//             </div>
//         </AuthenticatedLayout>
//     );
// }