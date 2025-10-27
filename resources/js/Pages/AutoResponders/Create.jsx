// resources/js/Pages/AutoResponders/Create.jsx
import React, { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/WaAuthLayout';

export default function Create({ auth, recentResponders = [] }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        keyword: '',
        response_message: '',
        is_active: true,
        case_sensitive: false,
        exact_match: false,
    });

    const [characterCount, setCharacterCount] = useState(0);

    function handleSubmit(e) {
        e.preventDefault();
        post(route('auto-responders.store'), {
            onSuccess: () => {
                reset();
                setCharacterCount(0);
            },
        });
    }

    const handleMessageChange = (e) => {
        setData('response_message', e.target.value);
        setCharacterCount(e.target.value.length);
    };

    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Create Auto Responder 🤖</h2>
                        <p className="text-gray-600 mt-1">Set up automated response rules for incoming messages</p>
                    </div>
                    <div className="flex space-x-3 mt-4 sm:mt-0">
                        <Link 
                            href={route('auto-responders.index')} 
                            className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            View All Rules
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
            <Head title="Create Auto Responder" />

            <div className="py-8">
                <div className="max-w-6xl mx-auto sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {/* Main Form */}
                        <div className="lg:col-span-2">
                            <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                                <div className="px-8 py-6 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-white">
                                    <div className="flex items-center space-x-3">
                                        <div className="p-3 bg-indigo-100 rounded-xl">
                                            <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-bold text-gray-900">Create New Auto Responder</h3>
                                            <p className="text-gray-600">Set up triggers and automated responses</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <form onSubmit={handleSubmit} className="p-8 space-y-8">
                                    {/* Trigger Configuration */}
                                    <div className="bg-blue-50 border border-blue-200 rounded-2xl p-6">
                                        <h4 className="font-semibold text-blue-900 mb-4 flex items-center space-x-2">
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            <span>Trigger Configuration</span>
                                        </h4>
                                        
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label htmlFor="keyword" className="block text-sm font-medium text-gray-700 mb-2">
                                                    Trigger Keyword *
                                                </label>
                                                <input
                                                    type="text"
                                                    id="keyword"
                                                    value={data.keyword}
                                                    onChange={(e) => setData('keyword', e.target.value)}
                                                    className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                                    placeholder="e.g., price, info, support, hours"
                                                />
                                                {errors.keyword && <p className="text-red-600 text-sm mt-2">{errors.keyword}</p>}
                                                <p className="text-gray-500 text-xs mt-2">
                                                    Word or phrase that triggers the auto-response
                                                </p>
                                            </div>
                                            
                                            <div className="space-y-4">
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Matching Options
                                                </label>
                                                <div className="space-y-3">
                                                    <label className="flex items-center space-x-3 cursor-pointer">
                                                        <input 
                                                            type="checkbox" 
                                                            checked={data.case_sensitive}
                                                            onChange={(e) => setData('case_sensitive', e.target.checked)}
                                                            className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                                        />
                                                        <span className="text-sm text-gray-700">Case sensitive</span>
                                                    </label>
                                                    <label className="flex items-center space-x-3 cursor-pointer">
                                                        <input 
                                                            type="checkbox" 
                                                            checked={data.exact_match}
                                                            onChange={(e) => setData('exact_match', e.target.checked)}
                                                            className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                                        />
                                                        <span className="text-sm text-gray-700">Exact match only</span>
                                                    </label>
                                                    <label className="flex items-center space-x-3 cursor-pointer">
                                                        <input 
                                                            type="checkbox" 
                                                            checked={data.is_active}
                                                            onChange={(e) => setData('is_active', e.target.checked)}
                                                            className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                                        />
                                                        <span className="text-sm text-gray-700">Active rule</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {/* Response Message */}
                                    <div>
                                        <div className="flex items-center justify-between mb-2">
                                            <label htmlFor="response_message" className="block text-sm font-medium text-gray-700">
                                                Response Message *
                                            </label>
                                            <span className={`text-xs ${characterCount > 160 ? 'text-red-600' : 'text-gray-500'}`}>
                                                {characterCount}/160 characters
                                            </span>
                                        </div>
                                        <textarea
                                            id="response_message"
                                            value={data.response_message}
                                            onChange={handleMessageChange}
                                            className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                            rows="6"
                                            placeholder="Enter the automated response message that will be sent when the keyword is detected..."
                                        ></textarea>
                                        {errors.response_message && <p className="text-red-600 text-sm mt-2">{errors.response_message}</p>}
                                        <div className="flex justify-between items-center mt-2">
                                            <p className="text-gray-500 text-xs">
                                                Keep messages conversational and under 160 characters for better delivery
                                            </p>
                                            {characterCount > 160 && (
                                                <p className="text-red-600 text-xs font-medium">
                                                    Message is too long for optimal delivery
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    {/* Quick Tips */}
                                    <div className="bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
                                        <h4 className="font-semibold text-yellow-900 mb-3 flex items-center space-x-2">
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Best Practices</span>
                                        </h4>
                                        <ul className="space-y-2 text-sm text-yellow-800">
                                            <li className="flex items-start space-x-2">
                                                <span className="text-yellow-600 mt-0.5">•</span>
                                                <span>Use clear, distinct keywords to avoid false triggers</span>
                                            </li>
                                            <li className="flex items-start space-x-2">
                                                <span className="text-yellow-600 mt-0.5">•</span>
                                                <span>Keep responses friendly and helpful</span>
                                            </li>
                                            <li className="flex items-start space-x-2">
                                                <span className="text-yellow-600 mt-0.5">•</span>
                                                <span>Test with different message variations</span>
                                            </li>
                                            <li className="flex items-start space-x-2">
                                                <span className="text-yellow-600 mt-0.5">•</span>
                                                <span>Consider using multiple keywords for common queries</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    {/* Submit Button */}
                                    <div className="flex justify-end pt-6 border-t border-gray-200">
                                        <Link
                                            href={route('auto-responders.index')}
                                            className="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors mr-4"
                                        >
                                            Cancel
                                        </Link>
                                        <button 
                                            type="submit" 
                                            className="px-8 py-3 bg-green-600 text-white font-medium rounded-xl shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors disabled:opacity-50 flex items-center space-x-3"
                                            disabled={processing}
                                        >
                                            {processing ? (
                                                <>
                                                    <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                                    <span>Creating Rule...</span>
                                                </>
                                            ) : (
                                                <>
                                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span>Create Auto Responder</span>
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {/* Recent Rules Sidebar */}
                        <div className="lg:col-span-1">
                            <div className="bg-white rounded-2xl shadow-lg p-6 sticky top-6">
                                <h3 className="text-lg font-bold text-gray-900 mb-4 flex items-center space-x-2">
                                    <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                    <span>Recent Rules</span>
                                </h3>
                                
                                {recentResponders && recentResponders.length > 0 ? (
                                    recentResponders.map((responder) => (
                                        <div key={responder.id} className="border border-gray-200 rounded-lg p-4 mb-3 hover:shadow-md transition-shadow">
                                            <div className="flex justify-between items-start mb-2">
                                                <span className="font-mono bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                                    {responder.keyword}
                                                </span>
                                                <span className={`text-xs font-medium px-2 py-1 rounded-full ${
                                                    responder.is_active 
                                                        ? 'bg-green-100 text-green-800' 
                                                        : 'bg-gray-100 text-gray-800'
                                                }`}>
                                                    {responder.is_active ? 'ACTIVE' : 'INACTIVE'}
                                                </span>
                                            </div>
                                            <p className="text-sm text-gray-600 mb-2 line-clamp-2">
                                                {responder.response_message}
                                            </p>
                                            <div className="flex justify-between text-xs text-gray-500">
                                                <span>Hits: {responder.trigger_count || 0}</span>
                                                <span>{new Date(responder.created_at).toLocaleDateString()}</span>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center py-6 text-gray-500">
                                        <svg className="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p className="text-sm">No rules yet</p>
                                        <p className="text-xs mt-1">Create your first auto-responder rule</p>
                                    </div>
                                )}
                                
                                {recentResponders && recentResponders.length > 0 && (
                                    <Link
                                        href={route('auto-responders.index')}
                                        className="w-full mt-4 px-4 py-2 text-sm text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors text-center block"
                                    >
                                        View All Rules →
                                    </Link>
                                )}
                            </div>

                            {/* Quick Stats */}
                            <div className="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl shadow-lg p-6 mt-6 border border-purple-200">
                                <h3 className="text-lg font-bold text-purple-900 mb-3 flex items-center space-x-2">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <span>Quick Tips</span>
                                </h3>
                                <div className="space-y-3 text-sm text-purple-800">
                                    <div className="flex items-start space-x-2">
                                        <span className="text-purple-600 mt-0.5">💡</span>
                                        <span>Common keywords: help, price, info, support, hours, contact</span>
                                    </div>
                                    <div className="flex items-start space-x-2">
                                        <span className="text-purple-600 mt-0.5">🎯</span>
                                        <span>Use exact match for specific commands like "MENU" or "HELP"</span>
                                    </div>
                                    <div className="flex items-start space-x-2">
                                        <span className="text-purple-600 mt-0.5">⚡</span>
                                        <span>Shorter responses have better engagement rates</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
