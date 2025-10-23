// resources/js/Pages/Campaigns/History.jsx
import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/WaAuthLayout';

export default function CampaignHistory({ auth, campaigns }) {
    const { post } = useForm();

    const handlePause = (campaignId) => {
        post(route('campaigns.pause', campaignId));
    };

    const handleResume = (campaignId) => {
        post(route('campaigns.resume', campaignId));
    };

    const getStatusBadge = (campaign) => {
        const now = new Date();
        const queuedAt = new Date(campaign.queued_at);
        const isRecent = (now - queuedAt) < 5 * 60 * 1000;
        
        if (campaign.status === 'paused') {
            return { 
                text: 'PAUSED', 
                class: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                icon: '⏸️'
            };
        } else if (campaign.sent_count + campaign.failed_count >= campaign.total_recipients) {
            return { 
                text: 'COMPLETED', 
                class: 'bg-green-100 text-green-800 border border-green-200',
                icon: '✅'
            };
        } else if (campaign.sent_count > 0 || campaign.failed_count > 0) {
            return { 
                text: 'IN PROGRESS', 
                class: 'bg-blue-100 text-blue-800 border border-blue-200',
                icon: '🔄'
            };
        } else if (isRecent) {
            return { 
                text: 'QUEUED', 
                class: 'bg-purple-100 text-purple-800 border border-purple-200',
                icon: '⏳'
            };
        } else {
            return { 
                text: 'STALLED', 
                class: 'bg-red-100 text-red-800 border border-red-200',
                icon: '⚠️'
            };
        }
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = Math.abs(now - date);
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
        const diffMinutes = Math.floor(diffTime / (1000 * 60));

        if (diffMinutes < 1) return 'Just now';
        if (diffMinutes < 60) return `${diffMinutes}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return `${diffDays}d ago`;
        
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    };

    const getProgressPercentage = (campaign) => {
        return campaign.total_recipients > 0 
            ? ((campaign.sent_count + campaign.failed_count) / campaign.total_recipients) * 100
            : 0;
    };

    const canPause = (campaign) => {
        const status = getStatusBadge(campaign);
        return status.text === 'IN PROGRESS' || status.text === 'QUEUED';
    };

    const canResume = (campaign) => {
        return campaign.status === 'paused';
    };

    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Campaign History 📊</h2>
                        <p className="text-gray-600 mt-1">Track and manage your broadcast campaigns</p>
                    </div>
                    <div className="flex space-x-3 mt-4 sm:mt-0">
                        <Link 
                            href={route('campaigns.create')} 
                            className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            Create New Campaign
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
            <Head title="Campaign History" />
            
            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Stats Overview */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-indigo-400">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">Total Campaigns</p>
                                    <p className="text-2xl font-bold text-gray-900">{campaigns.total}</p>
                                </div>
                                <div className="p-3 bg-indigo-100 rounded-lg">
                                    <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h10a2 2 0 012 2v2M7 7a2 2 0 012-2h6a2 2 0 012 2v2"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-green-400">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">Completed</p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {campaigns.data.filter(c => 
                                            c.sent_count + c.failed_count >= c.total_recipients
                                        ).length}
                                    </p>
                                </div>
                                <div className="p-3 bg-green-100 rounded-lg">
                                    <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-blue-400">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">In Progress</p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {campaigns.data.filter(c => 
                                            (c.sent_count > 0 || c.failed_count > 0) && 
                                            (c.sent_count + c.failed_count < c.total_recipients) &&
                                            c.status !== 'paused'
                                        ).length}
                                    </p>
                                </div>
                                <div className="p-3 bg-blue-100 rounded-lg">
                                    <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-yellow-400">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">Paused</p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {campaigns.data.filter(c => c.status === 'paused').length}
                                    </p>
                                </div>
                                <div className="p-3 bg-yellow-100 rounded-lg">
                                    <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Campaigns Table */}
                    <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center space-x-3">
                                    <div className="p-2 bg-purple-100 rounded-lg">
                                        <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 className="text-lg font-bold text-gray-900">All Campaigns</h3>
                                        <p className="text-gray-600">Manage and track your broadcast campaigns</p>
                                    </div>
                                </div>
                                <span className="bg-indigo-100 text-indigo-800 text-sm font-medium px-3 py-1 rounded-full">
                                    {campaigns.total} total
                                </span>
                            </div>
                        </div>

                        {campaigns.data.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Campaign
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Progress
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Device
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {campaigns.data.map((campaign) => {
                                            const status = getStatusBadge(campaign);
                                            const progressPercentage = getProgressPercentage(campaign);
                                            const showPause = canPause(campaign);
                                            const showResume = canResume(campaign);
                                            
                                            return (
                                                <tr key={campaign.id} className="hover:bg-gray-50 transition-colors">
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="flex items-center">
                                                            <div className="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                                <svg className="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                                                </svg>
                                                            </div>
                                                            <div className="ml-4">
                                                                <div className="text-sm font-medium text-gray-900 max-w-xs truncate">
                                                                    {campaign.message.length > 50 
                                                                        ? campaign.message.substring(0, 50) + '...' 
                                                                        : campaign.message
                                                                    }
                                                                </div>
                                                                <div className="text-sm text-gray-500">
                                                                    {campaign.total_recipients} recipients • {campaign.sent_count} sent
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${status.class}`}>
                                                            {status.icon} {status.text}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="w-32">
                                                            <div className="flex justify-between text-xs text-gray-500 mb-1">
                                                                <span>Progress</span>
                                                                <span>{Math.round(progressPercentage)}%</span>
                                                            </div>
                                                            <div className="w-full bg-gray-200 rounded-full h-2">
                                                                <div 
                                                                    className={`h-2 rounded-full transition-all duration-300 ${
                                                                        progressPercentage === 100 ? 'bg-green-600' : 
                                                                        progressPercentage > 0 ? 'bg-blue-600' : 'bg-gray-400'
                                                                    }`} 
                                                                    style={{ width: `${progressPercentage}%` }}
                                                                ></div>
                                                            </div>
                                                            <div className="flex justify-between text-xs text-gray-500 mt-1">
                                                                <span>{campaign.sent_count + campaign.failed_count}/{campaign.total_recipients}</span>
                                                                <span>{campaign.failed_count} failed</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-medium text-gray-900">
                                                            {campaign.whatsapp_device?.name || 'Unknown Device'}
                                                        </div>
                                                        <div className="text-xs text-gray-500">
                                                            {campaign.whatsapp_device?.phone_number || 'No number'}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <div className="font-medium">{formatDate(campaign.queued_at)}</div>
                                                        <div className="text-xs text-gray-400">
                                                            {new Date(campaign.queued_at).toLocaleTimeString('en-US', {
                                                                hour: '2-digit',
                                                                minute: '2-digit'
                                                            })}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <div className="flex space-x-2">
                                                            {showPause && (
                                                                <button
                                                                    onClick={() => handlePause(campaign.id)}
                                                                    className="text-yellow-600 hover:text-yellow-900 bg-yellow-50 hover:bg-yellow-100 px-3 py-1 rounded-lg text-xs font-medium transition-colors flex items-center space-x-1"
                                                                >
                                                                    <span>⏸️</span>
                                                                    <span>Pause</span>
                                                                </button>
                                                            )}
                                                            {showResume && (
                                                                <button
                                                                    onClick={() => handleResume(campaign.id)}
                                                                    className="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-lg text-xs font-medium transition-colors flex items-center space-x-1"
                                                                >
                                                                    <span>▶️</span>
                                                                    <span>Resume</span>
                                                                </button>
                                                            )}
                                                            {(campaign.status === 'paused' && !showResume) && (
                                                                <span className="text-gray-400 text-xs px-3 py-1">Completed</span>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <svg className="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h10a2 2 0 012 2v2M7 7a2 2 0 012-2h6a2 2 0 012 2v2"></path>
                                </svg>
                                <p className="text-gray-500 text-lg mb-2">No campaigns yet</p>
                                <p className="text-gray-400 text-sm mb-6">Create your first broadcast campaign to reach your audience</p>
                                <Link
                                    href={route('campaigns.create')}
                                    className="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors"
                                >
                                    Create First Campaign
                                </Link>
                            </div>
                        )}

                        {/* Pagination */}
                        {campaigns.data.length > 0 && (
                            <div className="px-6 py-4 border-t border-gray-200">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm text-gray-700">
                                        Showing <span className="font-medium">{campaigns.from}</span> to <span className="font-medium">{campaigns.to}</span> of{' '}
                                        <span className="font-medium">{campaigns.total}</span> campaigns
                                    </div>
                                    <div className="flex space-x-2">
                                        {campaigns.links.map((link, index) => (
                                            <Link
                                                key={index}
                                                href={link.url || '#'}
                                                className={`px-3 py-1 rounded-lg text-sm font-medium ${
                                                    link.active
                                                        ? 'bg-indigo-600 text-white'
                                                        : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                                                } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
