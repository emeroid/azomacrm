// resources/js/Pages/Campaigns/Create.jsx
import React, { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/WaAuthLayout';

export default function CreateCampaign({ auth, devices, campaigns }) {
    const [activeStep, setActiveStep] = useState(1);
    const [activeTab, setActiveTab] = useState('create');
    const { data, setData, post, processing, errors, progress } = useForm({
        session_id: devices.length > 0 ? devices[0].session_id : '',
        input_method: 'manual',
        phone_numbers: '',
        contacts_file: null,
        message: '',
        delay: 10,
    });

    function handleSubmit(e) {
        e.preventDefault();
        post(route('campaigns.store', { sessionId: data.session_id }), {
            forceFormData: data.input_method === 'file',
            onSuccess: () => {
                setActiveTab('list');
            }
        });
    }

    const steps = [
        { number: 1, title: 'Device', description: 'Select sending device' },
        { number: 2, title: 'Audience', description: 'Choose recipients' },
        { number: 3, title: 'Message', description: 'Compose content' },
        { number: 4, title: 'Settings', description: 'Configure options' },
    ];

    // Calculate campaign statistics
    const getCampaignStats = (campaign) => {
        const successRate = campaign.total_recipients > 0 
            ? ((campaign.sent_count / campaign.total_recipients) * 100).toFixed(1)
            : 0;
        
        return {
            successRate,
            pending: campaign.total_recipients - campaign.sent_count - campaign.failed_count,
        };
    };

    const getStatusBadge = (campaign) => {
        const now = new Date();
        const queuedAt = new Date(campaign.queued_at);
        const isRecent = (now - queuedAt) < 5 * 60 * 1000; // Less than 5 minutes ago
        
        if (campaign.sent_count + campaign.failed_count >= campaign.total_recipients) {
            return { text: 'COMPLETED', class: 'bg-green-100 text-green-800' };
        } else if (campaign.sent_count > 0 || campaign.failed_count > 0) {
            return { text: 'IN PROGRESS', class: 'bg-blue-100 text-blue-800' };
        } else if (isRecent) {
            return { text: 'QUEUED', class: 'bg-yellow-100 text-yellow-800' };
        } else {
            return { text: 'STALLED', class: 'bg-red-100 text-red-800' };
        }
    };

    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Broadcast Campaigns 📢</h2>
                        <p className="text-gray-600 mt-1">Send bulk WhatsApp messages to your audience</p>
                    </div>
                    <div className="flex space-x-3 mt-4 sm:mt-0">
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
            <Head title="Campaigns" />
            
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
                                        <span>Create New Campaign</span>
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
                                        <span>Campaign History ({campaigns.length})</span>
                                    </div>
                                </button>
                            </nav>
                        </div>
                    </div>

                    {/* CREATE NEW CAMPAIGN */}
                    {activeTab === 'create' && (
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            {/* Progress Steps and Form */}
                            <div className="lg:col-span-2">
                                {/* Progress Steps */}
                                <div className="bg-white rounded-2xl shadow-sm p-6 mb-8">
                                    <div className="flex items-center justify-between">
                                        {steps.map((step, index) => (
                                            <React.Fragment key={step.number}>
                                                <div className="flex flex-col items-center">
                                                    <div className={`w-12 h-12 rounded-full flex items-center justify-center border-2 font-semibold text-lg ${
                                                        activeStep >= step.number
                                                            ? 'bg-indigo-600 border-indigo-600 text-white'
                                                            : 'border-gray-300 text-gray-500'
                                                    }`}>
                                                        {step.number}
                                                    </div>
                                                    <div className="mt-2 text-center">
                                                        <div className={`text-sm font-medium ${
                                                            activeStep >= step.number ? 'text-indigo-600' : 'text-gray-500'
                                                        }`}>
                                                            {step.title}
                                                        </div>
                                                        <div className="text-xs text-gray-400">{step.description}</div>
                                                    </div>
                                                </div>
                                                {index < steps.length - 1 && (
                                                    <div className={`flex-1 h-1 mx-4 ${
                                                        activeStep > step.number ? 'bg-indigo-600' : 'bg-gray-200'
                                                    }`}></div>
                                                )}
                                            </React.Fragment>
                                        ))}
                                    </div>
                                </div>

                                <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                                    <form onSubmit={handleSubmit} className="p-8">
                                        
                                        {/* Step 1: Device Selection */}
                                        <div className={`space-y-6 ${activeStep !== 1 ? 'hidden' : ''}`}>
                                            <div className="flex items-center space-x-3 mb-6">
                                                <div className="p-2 bg-indigo-100 rounded-lg">
                                                    <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 className="text-xl font-bold text-gray-900">Select Sending Device</h3>
                                                    <p className="text-gray-600">Choose which WhatsApp device to send from</p>
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label htmlFor="session_id" className="block text-sm font-medium text-gray-700 mb-2">
                                                    From Device
                                                </label>
                                                <select
                                                    id="session_id"
                                                    value={data.session_id}
                                                    onChange={(e) => setData('session_id', e.target.value)}
                                                    className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                                    disabled={devices.length === 0}
                                                >
                                                    {devices.length > 0 ? (
                                                        devices.map(device => (
                                                            <option key={device.id} value={device.session_id}>
                                                                {device.name} ({device.session_id})
                                                            </option>
                                                        ))
                                                    ) : (
                                                        <option>No devices connected. Please add one first.</option>
                                                    )}
                                                </select>
                                                {errors.session_id && <p className="text-red-600 text-sm mt-2">{errors.session_id}</p>}
                                            </div>
                                            
                                            <div className="flex justify-end">
                                                <button
                                                    type="button"
                                                    onClick={() => setActiveStep(2)}
                                                    className="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors"
                                                    disabled={devices.length === 0}
                                                >
                                                    Next: Audience
                                                </button>
                                            </div>
                                        </div>

                                        {/* Step 2: Audience Selection */}
                                        <div className={`space-y-6 ${activeStep !== 2 ? 'hidden' : ''}`}>
                                            <div className="flex items-center space-x-3 mb-6">
                                                <div className="p-2 bg-purple-100 rounded-lg">
                                                    <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 className="text-xl font-bold text-gray-900">Target Audience</h3>
                                                    <p className="text-gray-600">Choose how to import your recipients</p>
                                                </div>
                                            </div>

                                            {/* Tab Navigation */}
                                            <div className="border-b border-gray-200">
                                                <nav className="flex -mb-px">
                                                    <button
                                                        type="button"
                                                        onClick={() => setData('input_method', 'manual')}
                                                        className={`py-3 px-4 text-center border-b-2 font-medium text-sm ${
                                                            data.input_method === 'manual'
                                                                ? 'border-indigo-500 text-indigo-600'
                                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                                        }`}
                                                    >
                                                        Manual Entry
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => setData('input_method', 'file')}
                                                        className={`py-3 px-4 text-center border-b-2 font-medium text-sm ${
                                                            data.input_method === 'file'
                                                                ? 'border-indigo-500 text-indigo-600'
                                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                                        }`}
                                                    >
                                                        Upload File
                                                    </button>
                                                </nav>
                                            </div>

                                            {data.input_method === 'manual' ? (
                                                <div>
                                                    <label htmlFor="phone_numbers" className="block text-sm font-medium text-gray-700 mb-2">
                                                        Phone Numbers (one per line)
                                                    </label>
                                                    <textarea 
                                                        id="phone_numbers" 
                                                        value={data.phone_numbers} 
                                                        onChange={(e) => setData('phone_numbers', e.target.value)} 
                                                        className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" 
                                                        rows="6" 
                                                        placeholder="2348012345678&#10;2349012345679&#10;2347012345678"
                                                    ></textarea>
                                                    {errors.phone_numbers && <p className="text-red-600 text-sm mt-2">{errors.phone_numbers}</p>}
                                                    <p className="text-gray-500 text-xs mt-2">Enter phone numbers in international format without + sign</p>
                                                </div>
                                            ) : (
                                                <div>
                                                    <label htmlFor="contacts_file" className="block text-sm font-medium text-gray-700 mb-2">
                                                        Upload Contact File
                                                    </label>
                                                    <div className="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-indigo-400 transition-colors">
                                                        <input 
                                                            type="file" 
                                                            id="contacts_file" 
                                                            onChange={(e) => setData('contacts_file', e.target.files[0])} 
                                                            className="hidden" 
                                                        />
                                                        <label htmlFor="contacts_file" className="cursor-pointer">
                                                            <svg className="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                            </svg>
                                                            <p className="text-gray-600 mb-2">Click to upload or drag and drop</p>
                                                            <p className="text-gray-400 text-sm">CSV, XLS, XLSX files (max 10MB)</p>
                                                        </label>
                                                    </div>
                                                    {progress && (
                                                        <div className="mt-4">
                                                            <div className="w-full bg-gray-200 rounded-full h-2">
                                                                <div 
                                                                    className="bg-indigo-600 h-2 rounded-full transition-all duration-300" 
                                                                    style={{ width: `${progress.percentage}%` }}
                                                                ></div>
                                                            </div>
                                                            <p className="text-sm text-gray-600 mt-2">Uploading: {progress.percentage}%</p>
                                                        </div>
                                                    )}
                                                    {errors.contacts_file && <p className="text-red-600 text-sm mt-2">{errors.contacts_file}</p>}
                                                </div>
                                            )}

                                            <div className="flex justify-between pt-6">
                                                <button
                                                    type="button"
                                                    onClick={() => setActiveStep(1)}
                                                    className="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
                                                >
                                                    ← Back
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => setActiveStep(3)}
                                                    className="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors"
                                                >
                                                    Next: Message
                                                </button>
                                            </div>
                                        </div>

                                        {/* Step 3: Message Composition */}
                                        <div className={`space-y-6 ${activeStep !== 3 ? 'hidden' : ''}`}>
                                            <div className="flex items-center space-x-3 mb-6">
                                                <div className="p-2 bg-green-100 rounded-lg">
                                                    <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 className="text-xl font-bold text-gray-900">Compose Message</h3>
                                                    <p className="text-gray-600">Write your broadcast message content</p>
                                                </div>
                                            </div>

                                            <div>
                                                <label htmlFor="message" className="block text-sm font-medium text-gray-700 mb-2">
                                                    Message Content
                                                </label>
                                                <textarea 
                                                    id="message" 
                                                    value={data.message} 
                                                    onChange={(e) => setData('message', e.target.value)} 
                                                    className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" 
                                                    rows="8" 
                                                    placeholder="Hello! This is a broadcast message from our company..."
                                                ></textarea>
                                                {errors.message && <p className="text-red-600 text-sm mt-2">{errors.message}</p>}
                                                <div className="flex justify-between items-center mt-2">
                                                    <p className="text-gray-500 text-xs">Write your message in a friendly, engaging tone</p>
                                                    <p className="text-gray-400 text-xs">{data.message.length} characters</p>
                                                </div>
                                            </div>

                                            <div className="flex justify-between pt-6">
                                                <button
                                                    type="button"
                                                    onClick={() => setActiveStep(2)}
                                                    className="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
                                                >
                                                    ← Back
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => setActiveStep(4)}
                                                    className="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors"
                                                >
                                                    Next: Settings
                                                </button>
                                            </div>
                                        </div>

                                        {/* Step 4: Settings */}
                                        <div className={`space-y-6 ${activeStep !== 4 ? 'hidden' : ''}`}>
                                            <div className="flex items-center space-x-3 mb-6">
                                                <div className="p-2 bg-yellow-100 rounded-lg">
                                                    <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 className="text-xl font-bold text-gray-900">Campaign Settings</h3>
                                                    <p className="text-gray-600">Configure sending options</p>
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    <label htmlFor="delay" className="block text-sm font-medium text-gray-700 mb-2">
                                                        Delay Between Messages
                                                    </label>
                                                    <div className="relative">
                                                        <input 
                                                            type="number" 
                                                            id="delay" 
                                                            value={data.delay} 
                                                            onChange={(e) => setData('delay', e.target.value)} 
                                                            className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" 
                                                            min="1"
                                                        />
                                                        <div className="absolute inset-y-0 right-0 flex items-center pr-3">
                                                            <span className="text-gray-500 text-sm">seconds</span>
                                                        </div>
                                                    </div>
                                                    {errors.delay && <p className="text-red-600 text-sm mt-2">{errors.delay}</p>}
                                                    <p className="text-gray-500 text-xs mt-2">Prevents rate limiting and appears more natural</p>
                                                </div>
                                                
                                                <div className="bg-blue-50 p-4 rounded-xl border border-blue-200">
                                                    <h4 className="font-semibold text-blue-900 mb-2">Campaign Summary</h4>
                                                    <div className="space-y-2 text-sm text-blue-800">
                                                        <div className="flex justify-between">
                                                            <span>Recipients:</span>
                                                            <span className="font-semibold">
                                                                {data.input_method === 'manual' 
                                                                    ? data.phone_numbers.split('\n').filter(n => n.trim()).length 
                                                                    : 'From file'}
                                                            </span>
                                                        </div>
                                                        <div className="flex justify-between">
                                                            <span>Device:</span>
                                                            <span className="font-semibold">
                                                                {devices.find(d => d.session_id === data.session_id)?.name || 'Not selected'}
                                                            </span>
                                                        </div>
                                                        <div className="flex justify-between">
                                                            <span>Message Length:</span>
                                                            <span className="font-semibold">{data.message.length} chars</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="flex justify-between pt-6">
                                                <button
                                                    type="button"
                                                    onClick={() => setActiveStep(3)}
                                                    className="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
                                                >
                                                    ← Back
                                                </button>
                                                <button
                                                    type="submit"
                                                    className="px-8 py-3 bg-green-600 text-white font-medium rounded-xl shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors disabled:opacity-50"
                                                    disabled={processing || devices.length === 0}
                                                >
                                                    {processing ? (
                                                        <div className="flex items-center space-x-2">
                                                            <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                                            <span>Starting Campaign...</span>
                                                        </div>
                                                    ) : (
                                                        'Launch Campaign 🚀'
                                                    )}
                                                </button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>

                            {/* Recent Campaigns Sidebar */}
                            <div className="lg:col-span-1">
                                <div className="bg-white rounded-2xl shadow-lg p-6 sticky top-6">
                                    <h3 className="text-lg font-bold text-gray-900 mb-4 flex items-center space-x-2">
                                        <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                        <span>Recent Campaigns</span>
                                    </h3>
                                    
                                    {campaigns.slice(0, 3).map((campaign) => {
                                        const stats = getCampaignStats(campaign);
                                        const status = getStatusBadge(campaign);
                                        return (
                                            <div key={campaign.id} className="border border-gray-200 rounded-lg p-4 mb-3 hover:shadow-md transition-shadow">
                                                <div className="flex justify-between items-start mb-2">
                                                    <span className={`text-xs font-medium px-2 py-1 rounded-full ${status.class}`}>
                                                        {status.text}
                                                    </span>
                                                    <span className="text-xs text-gray-500">
                                                        {new Date(campaign.queued_at).toLocaleDateString()}
                                                    </span>
                                                </div>
                                                <p className="text-sm text-gray-600 mb-2 line-clamp-2">
                                                    {campaign.message.length > 60 ? campaign.message.substring(0, 60) + '...' : campaign.message}
                                                </p>
                                                <div className="flex justify-between text-xs text-gray-500">
                                                    <span>Sent: {campaign.sent_count}/{campaign.total_recipients}</span>
                                                    <span>{stats.successRate}%</span>
                                                </div>
                                            </div>
                                        );
                                    })}
                                    
                                    {campaigns.length === 0 && (
                                        <div className="text-center py-6 text-gray-500">
                                            <svg className="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h10a2 2 0 012 2v2M7 7a2 2 0 012-2h6a2 2 0 012 2v2"></path>
                                            </svg>
                                            <p className="text-sm">No campaigns yet</p>
                                            <p className="text-xs mt-1">Create your first campaign to get started</p>
                                        </div>
                                    )}
                                    
                                    {campaigns.length > 3 && (
                                        <button
                                            onClick={() => setActiveTab('list')}
                                            className="w-full mt-4 px-4 py-2 text-sm text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors"
                                        >
                                            View All Campaigns →
                                        </button>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* CAMPAIGN HISTORY LIST */}
                    {activeTab === 'list' && (
                        <div className="bg-white rounded-2xl shadow-lg p-8">
                            <div className="flex items-center justify-between mb-6">
                                <div className="flex items-center space-x-3">
                                    <div className="p-2 bg-purple-100 rounded-lg">
                                        <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 className="text-xl font-bold text-gray-900">Campaign History</h3>
                                        <p className="text-gray-600">Track your broadcast campaign performance</p>
                                    </div>
                                </div>
                                <span className="bg-indigo-100 text-indigo-800 text-sm font-medium px-3 py-1 rounded-full">
                                    {campaigns.length} campaigns
                                </span>
                            </div>
                            
                            {campaigns && campaigns.length > 0 ? (
                                <div className="space-y-6">
                                    {campaigns.map((campaign) => {
                                        const stats = getCampaignStats(campaign);
                                        const status = getStatusBadge(campaign);
                                        
                                        return (
                                            <div key={campaign.id} className="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                                                <div className="flex justify-between items-start mb-4">
                                                    <div className="flex items-center space-x-4">
                                                        <span className={`text-sm font-medium px-3 py-1 rounded-full ${status.class}`}>
                                                            {status.text}
                                                        </span>
                                                        <span className="text-sm text-gray-500">
                                                            {new Date(campaign.queued_at).toLocaleString()}
                                                        </span>
                                                    </div>
                                                    <div className="text-right">
                                                        <div className="text-2xl font-bold text-gray-900">{stats.successRate}%</div>
                                                        <div className="text-sm text-gray-500">Success Rate</div>
                                                    </div>
                                                </div>
                                                
                                                <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                    <div className="text-center p-3 bg-blue-50 rounded-lg">
                                                        <div className="text-lg font-bold text-blue-700">{campaign.total_recipients}</div>
                                                        <div className="text-xs text-blue-600">Total Recipients</div>
                                                    </div>
                                                    <div className="text-center p-3 bg-green-50 rounded-lg">
                                                        <div className="text-lg font-bold text-green-700">{campaign.sent_count}</div>
                                                        <div className="text-xs text-green-600">Sent</div>
                                                    </div>
                                                    <div className="text-center p-3 bg-red-50 rounded-lg">
                                                        <div className="text-lg font-bold text-red-700">{campaign.failed_count}</div>
                                                        <div className="text-xs text-red-600">Failed</div>
                                                    </div>
                                                    <div className="text-center p-3 bg-yellow-50 rounded-lg">
                                                        <div className="text-lg font-bold text-yellow-700">{stats.pending}</div>
                                                        <div className="text-xs text-yellow-600">Pending</div>
                                                    </div>
                                                </div>
                                                
                                                <div className="bg-gray-50 p-4 rounded-lg border-l-4 border-purple-400">
                                                    <p className="text-gray-700 text-sm leading-relaxed">
                                                        {campaign.message}
                                                    </p>
                                                </div>
                                                
                                                <div className="flex justify-between items-center mt-4 text-sm text-gray-500">
                                                    <span>Device: {campaign.whatsapp_device?.name || 'Unknown'}</span>
                                                    <span>Delay: {campaign.delay}s between messages</span>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            ) : (
                                <div className="text-center py-12">
                                    <svg className="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h10a2 2 0 012 2v2M7 7a2 2 0 012-2h6a2 2 0 012 2v2"></path>
                                    </svg>
                                    <p className="text-gray-500 text-lg mb-2">No campaigns yet</p>
                                    <p className="text-gray-400 text-sm mb-6">Create your first broadcast campaign to reach your audience</p>
                                    <button
                                        onClick={() => setActiveTab('create')}
                                        className="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors"
                                    >
                                        Create First Campaign
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