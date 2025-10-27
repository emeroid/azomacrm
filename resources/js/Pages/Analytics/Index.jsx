// resources/js/Pages/Analytics/Index.jsx
import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/WaAuthLayout';
import { Head, Link } from '@inertiajs/react';

// Enhanced StatCard with better visuals
const StatCard = ({ title, value, colorClass, icon, description, trend, onClick }) => (
    <div 
        onClick={onClick}
        className={`p-6 bg-white rounded-2xl shadow-lg border-l-4 ${colorClass} hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer ${onClick ? 'hover:border-indigo-400' : ''}`}
    >
        <div className="flex items-center justify-between">
            <div className="flex-1">
                <p className="text-sm font-medium text-gray-500 mb-1">{title}</p>
                <p className="text-3xl font-bold text-gray-900 mb-2">{value}</p>
                {description && (
                    <p className="text-xs text-gray-400">{description}</p>
                )}
                {trend && (
                    <div className={`inline-flex items-center text-xs font-medium px-2 py-1 rounded-full ${trend.isPositive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                        {trend.isPositive ? '↗' : '↘'} {trend.value}
                    </div>
                )}
            </div>
            <div className={`p-4 rounded-2xl ${colorClass.replace('border-', 'bg-').replace('-500', '-100')}`}>
                {icon}
            </div>
        </div>
    </div>
);

// Navigation Cards for quick access
const NavCard = ({ title, description, icon, href, color }) => (
    <Link 
        href={href} 
        className={`p-6 bg-white rounded-2xl shadow-lg border-l-4 ${color} hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group block`}
    >
        <div className="flex items-center justify-between">
            <div className="flex-1">
                <h3 className="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{title}</h3>
                <p className="text-sm text-gray-500 mt-1">{description}</p>
            </div>
            <div className={`p-3 rounded-xl ${color.replace('border-', 'bg-').replace('-500', '-100')} group-hover:scale-110 transition-transform`}>
                {icon}
            </div>
        </div>
    </Link>
);

// Simple Bar Chart Component
const BarChart = ({ data, title, onBarClick }) => {
    const maxValue = Math.max(...data.map(item => item.value));
    
    return (
        <div className="bg-white rounded-2xl shadow-lg p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">{title}</h3>
            <div className="space-y-3">
                {data.map((item, index) => (
                    <div key={index} className="flex items-center space-x-3">
                        <div className="w-24 text-sm text-gray-600 truncate">{item.label}</div>
                        <div 
                            className="flex-1 h-8 bg-gray-100 rounded-lg overflow-hidden cursor-pointer hover:opacity-80 transition-opacity"
                            onClick={() => onBarClick && onBarClick(item)}
                        >
                            <div 
                                className={`h-full rounded-lg transition-all duration-500 ${item.color}`}
                                style={{ width: `${(item.value / maxValue) * 100}%` }}
                            ></div>
                        </div>
                        <div className="w-16 text-right text-sm font-medium text-gray-900">
                            {item.value}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

// Message Status Breakdown Component
const StatusBreakdown = ({ data, title, onStatusClick }) => {
    const total = data.reduce((sum, item) => sum + item.value, 0);
    
    return (
        <div className="bg-white rounded-2xl shadow-lg p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">{title}</h3>
            <div className="space-y-3">
                {data.map((item, index) => {
                    const percentage = total > 0 ? ((item.value / total) * 100).toFixed(1) : 0;
                    return (
                        <div 
                            key={index}
                            className="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors"
                            onClick={() => onStatusClick && onStatusClick(item)}
                        >
                            <div className="flex items-center space-x-3">
                                <div className={`w-3 h-3 rounded-full ${item.color}`}></div>
                                <span className="text-sm font-medium text-gray-700">{item.label}</span>
                            </div>
                            <div className="text-right">
                                <div className="text-sm font-bold text-gray-900">{item.value}</div>
                                <div className="text-xs text-gray-500">{percentage}%</div>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

// Message List Modal
const MessageListModal = ({ isOpen, onClose, messages, title }) => {
    if (!isOpen) return null;

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div className="bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
                <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 className="text-lg font-semibold text-gray-900">{title}</h3>
                    <button
                        onClick={onClose}
                        className="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div className="overflow-y-auto max-h-[60vh]">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {messages.map((message, index) => (
                                <tr key={index} className="hover:bg-gray-50">
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {message.recipient_number}
                                    </td>
                                    <td className="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                        {message.message || 'Media Message'}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                            message.status === 'sent' ? 'bg-green-100 text-green-800' :
                                            message.status === 'delivered' ? 'bg-blue-100 text-blue-800' :
                                            message.status === 'failed' ? 'bg-red-100 text-red-800' :
                                            'bg-yellow-100 text-yellow-800'
                                        }`}>
                                            {message.status}
                                        </span>
                                        {message.failure_reason && (
                                            <div className="text-xs text-red-600 mt-1">{message.failure_reason}</div>
                                        )}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {formatDate(message.sent_at || message.created_at)}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {message.source}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

export default function AnalyticsIndex({ auth, analyticsData }) {
    const [activeView, setActiveView] = useState('overview');
    const [selectedStatus, setSelectedStatus] = useState(null);
    const [selectedTimeRange, setSelectedTimeRange] = useState('today');
    
    // Destructure the enhanced analytics data
    const { 
        overview, 
        messageTrends, 
        failureBreakdown, 
        sourceBreakdown,
        recentMessages 
    } = analyticsData;

    // Icons for better visual consistency
    const icons = {
        schedule: <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>,
        success: <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>,
        failure: <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>,
        trigger: <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9 9 0 01-9-8 9 9 0 019-8c4.97 0 9 3.582 9 8z"></path></svg>,
        broadcast: <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h10a2 2 0 012 2v2M7 7a2 2 0 012-2h6a2 2 0 012 2v2"></path></svg>,
        delivery: <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-2 4v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7"></path></svg>
    };

    // Handle status clicks to show detailed messages
    const handleStatusClick = (statusData) => {
        setSelectedStatus({
            type: statusData.type,
            title: `${statusData.label} Messages`,
            messages: statusData.messages || []
        });
    };

    const handleBarClick = (barData) => {
        setSelectedStatus({
            type: barData.type,
            title: `${barData.label} Messages`,
            messages: barData.messages || []
        });
    };

    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Analytics Dashboard 📊</h2>
                        <p className="text-gray-600 mt-1">Comprehensive WhatsApp message performance analytics</p>
                    </div>
                    <div className="flex space-x-3 mt-4 sm:mt-0">
                        <select 
                            value={selectedTimeRange}
                            onChange={(e) => setSelectedTimeRange(e.target.value)}
                            className="px-3 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                        <Link 
                            href={route('schedulers.index')} 
                            className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            View Scheduler
                        </Link>
                        <Link 
                            href={route('devices.index')} 
                            className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            View Devices
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Analytics Dashboard" />

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                    
                    {/* Quick Navigation Cards */}
                    <section>
                        <h3 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <NavCard 
                                title="Message Scheduler"
                                description="Schedule automated messages"
                                href={route('schedulers.index')}
                                color="border-indigo-500"
                                icon={icons.schedule}
                            />
                            <NavCard 
                                title="Auto Responders"
                                description="Manage automated replies"
                                href={route('auto-responders.index')}
                                color="border-blue-500"
                                icon={icons.trigger}
                            />
                            <NavCard 
                                title="Broadcast Campaigns"
                                description="Create new broadcasts"
                                href={route('campaigns.index')}
                                color="border-purple-500"
                                icon={icons.broadcast}
                            />
                        </div>
                    </section>

                    {/* Overview Stats */}
                    <section className="space-y-6">
                        <div className="flex items-center justify-between">
                            <h3 className="text-xl font-bold text-gray-900">Performance Overview</h3>
                            <span className="text-sm text-gray-500">Last 24 hours</span>
                        </div>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <StatCard 
                                title="Total Messages" 
                                value={overview.total_messages.toLocaleString()} 
                                colorClass="border-indigo-500" 
                                icon={icons.schedule}
                                description="All message types"
                                onClick={() => handleStatusClick({ type: 'all', label: 'All', messages: recentMessages })}
                            />
                            <StatCard 
                                title="Successful" 
                                value={overview.successful_messages.toLocaleString()} 
                                colorClass="border-green-500" 
                                icon={icons.success}
                                description="Delivered successfully"
                                trend={{ isPositive: true, value: '+12%' }}
                                onClick={() => handleStatusClick({ 
                                    type: 'success', 
                                    label: 'Successful', 
                                    messages: recentMessages.filter(m => m.status === 'sent' || m.status === 'delivered') 
                                })}
                            />
                            <StatCard 
                                title="Failed" 
                                value={overview.failed_messages.toLocaleString()} 
                                colorClass="border-red-500" 
                                icon={icons.failure}
                                description="Delivery failures"
                                trend={{ isPositive: false, value: '-5%' }}
                                onClick={() => handleStatusClick({ 
                                    type: 'failed', 
                                    label: 'Failed', 
                                    messages: recentMessages.filter(m => m.status === 'failed') 
                                })}
                            />
                            <StatCard 
                                title="Success Rate" 
                                value={`${overview.success_rate}%`}
                                colorClass="border-emerald-500" 
                                icon={<svg className="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>}
                                description="Overall performance"
                            />
                        </div>
                    </section>

                    {/* Charts Section */}
                    <section className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {/* Message Trends Chart */}
                        <BarChart 
                            title="Message Trends by Source"
                            data={[
                                { label: 'Broadcasts', value: sourceBreakdown.broadcasts, color: 'bg-purple-500', type: 'broadcasts', messages: recentMessages.filter(m => m.source === 'broadcast') },
                                { label: 'Scheduled', value: sourceBreakdown.scheduled, color: 'bg-indigo-500', type: 'scheduled', messages: recentMessages.filter(m => m.source === 'scheduled') },
                                { label: 'Auto Replies', value: sourceBreakdown.auto_replies, color: 'bg-blue-500', type: 'auto_replies', messages: recentMessages.filter(m => m.source === 'auto_responder') },
                            ]}
                            onBarClick={handleBarClick}
                        />

                        {/* Status Breakdown */}
                        <StatusBreakdown 
                            title="Message Status Breakdown"
                            data={[
                                { label: 'Sent', value: messageTrends.sent, color: 'bg-green-500', type: 'sent', messages: recentMessages.filter(m => m.status === 'sent') },
                                { label: 'Delivered', value: messageTrends.delivered, color: 'bg-blue-500', type: 'delivered', messages: recentMessages.filter(m => m.status === 'delivered') },
                                { label: 'Failed', value: messageTrends.failed, color: 'bg-red-500', type: 'failed', messages: recentMessages.filter(m => m.status === 'failed') },
                                { label: 'Pending', value: messageTrends.pending, color: 'bg-yellow-500', type: 'pending', messages: recentMessages.filter(m => m.status === 'pending') },
                            ]}
                            onStatusClick={handleStatusClick}
                        />
                    </section>

                    {/* Failure Analysis */}
                    <section className="space-y-6">
                        <div className="flex items-center justify-between">
                            <h3 className="text-xl font-bold text-gray-900">Failure Analysis</h3>
                            <span className="text-sm text-gray-500">Common failure reasons</span>
                        </div>
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <BarChart 
                                title="Top Failure Reasons"
                                data={[
                                    { label: 'Invalid Number', value: failureBreakdown.invalid_number, color: 'bg-red-500', type: 'invalid_number' },
                                    { label: 'Network Issue', value: failureBreakdown.network_error, color: 'bg-orange-500', type: 'network_error' },
                                    { label: 'Device Offline', value: failureBreakdown.device_offline, color: 'bg-yellow-500', type: 'device_offline' },
                                    { label: 'Rate Limited', value: failureBreakdown.rate_limited, color: 'bg-purple-500', type: 'rate_limited' },
                                    { label: 'Other', value: failureBreakdown.other, color: 'bg-gray-500', type: 'other' },
                                ]}
                                onBarClick={handleBarClick}
                            />
                            
                            {/* Failure Rate by Source */}
                            <StatusBreakdown 
                                title="Failure Rate by Source"
                                data={[
                                    { label: 'Broadcasts', value: Math.round((sourceBreakdown.broadcasts_failed / sourceBreakdown.broadcasts) * 100) || 0, color: 'bg-purple-500' },
                                    { label: 'Scheduled', value: Math.round((sourceBreakdown.scheduled_failed / sourceBreakdown.scheduled) * 100) || 0, color: 'bg-indigo-500' },
                                    { label: 'Auto Replies', value: Math.round((sourceBreakdown.auto_replies_failed / sourceBreakdown.auto_replies) * 100) || 0, color: 'bg-blue-500' },
                                ]}
                            />
                        </div>
                    </section>

                </div>
            </div>

            {/* Message List Modal */}
            <MessageListModal 
                isOpen={selectedStatus !== null}
                onClose={() => setSelectedStatus(null)}
                messages={selectedStatus?.messages || []}
                title={selectedStatus?.title || 'Messages'}
            />
        </AuthenticatedLayout>
    );
}




// // resources/js/Pages/Analytics/Index.jsx
// import React from 'react';
// import AuthenticatedLayout from '@/Layouts/WaAuthLayout';
// import { Head, Link } from '@inertiajs/react';

// // Enhanced StatCard with better visuals
// const StatCard = ({ title, value, colorClass, icon, description, trend }) => (
//     <div className={`p-6 bg-white rounded-2xl shadow-lg border-l-4 ${colorClass} hover:shadow-xl transition-all duration-300 hover:-translate-y-1`}>
//         <div className="flex items-center justify-between">
//             <div className="flex-1">
//                 <p className="text-sm font-medium text-gray-500 mb-1">{title}</p>
//                 <p className="text-3xl font-bold text-gray-900 mb-2">{value}</p>
//                 {description && (
//                     <p className="text-xs text-gray-400">{description}</p>
//                 )}
//                 {trend && (
//                     <div className={`inline-flex items-center text-xs font-medium px-2 py-1 rounded-full ${trend.isPositive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
//                         {trend.isPositive ? '↗' : '↘'} {trend.value}
//                     </div>
//                 )}
//             </div>
//             <div className={`p-4 rounded-2xl ${colorClass.replace('border-', 'bg-').replace('-500', '-100')}`}>
//                 {icon}
//             </div>
//         </div>
//     </div>
// );

// // Navigation Cards for quick access
// const NavCard = ({ title, description, icon, href, color }) => (
//     <Link 
//         href={href} 
//         className={`p-6 bg-white rounded-2xl shadow-lg border-l-4 ${color} hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group block`}
//     >
//         <div className="flex items-center justify-between">
//             <div className="flex-1">
//                 <h3 className="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{title}</h3>
//                 <p className="text-sm text-gray-500 mt-1">{description}</p>
//             </div>
//             <div className={`p-3 rounded-xl ${color.replace('border-', 'bg-').replace('-500', '-100')} group-hover:scale-110 transition-transform`}>
//                 {icon}
//             </div>
//         </div>
//     </Link>
// );

// export default function AnalyticsIndex({ auth, scheduledData, responderData, campaignData }) {
    
//     // Calculate AutoResponder Reply Success Rate
//     const totalReplies = Object.values(responderData.reply_status).reduce((sum, count) => sum + count, 0);
//     const successfulReplies = responderData.reply_status.sent || 0;
//     const failedReplies = responderData.reply_status.failed || 0;
//     const successRate = totalReplies > 0 ? ((successfulReplies / totalReplies) * 100).toFixed(1) : 0;
    
//     // Icons for better visual consistency
//     const icons = {
//         schedule: <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>,
//         success: <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>,
//         failure: <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>,
//         trigger: <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9 9 0 01-9-8 9 9 0 019-8c4.97 0 9 3.582 9 8z"></path></svg>,
//         broadcast: <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h10a2 2 0 012 2v2M7 7a2 2 0 012-2h6a2 2 0 012 2v2"></path></svg>,
//         delivery: <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-2 4v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7"></path></svg>
//     };
    
//     return (
//         <AuthenticatedLayout
//             auth={auth}
//             header={
//                 <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
//                     <div>
//                         <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Analytics Dashboard 📊</h2>
//                         <p className="text-gray-600 mt-1">Monitor your WhatsApp campaign performance</p>
//                     </div>
//                     <div className="flex space-x-3 mt-4 sm:mt-0">
//                         <Link 
//                             href={route('schedulers.index')} 
//                             className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
//                         >
//                             View Scheduler
//                         </Link>
//                         <Link 
//                             href={route('devices.index')} 
//                             className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
//                         >
//                             View Devices
//                         </Link>
//                     </div>
//                 </div>
//             }
//         >
//             <Head title="Analytics Dashboard" />

//             <div className="py-8">
//                 <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                    
//                     {/* Quick Navigation Cards */}
//                     <section>
//                         <h3 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
//                         <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
//                             <NavCard 
//                                 title="Message Scheduler"
//                                 description="Schedule automated messages"
//                                 href={route('schedulers.index')}
//                                 color="border-indigo-500"
//                                 icon={<svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>}
//                             />
//                             <NavCard 
//                                 title="Auto Responders"
//                                 description="Manage automated replies"
//                                 href={route('auto-responders.index')}
//                                 color="border-blue-500"
//                                 icon={<svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>}
//                             />
//                             <NavCard 
//                                 title="Broadcast Campaigns"
//                                 description="Create new broadcasts"
//                                 href={route('campaigns.create')}
//                                 color="border-purple-500"
//                                 icon={<svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h10a2 2 0 012 2v2M7 7a2 2 0 012-2h6a2 2 0 012 2v2"></path></svg>}
//                             />
//                         </div>
//                     </section>

//                     {/* --- 1. Scheduled Messages Analytics --- */}
//                     <section className="space-y-6">
//                         <div className="flex items-center justify-between">
//                             <h3 className="text-xl font-bold text-gray-900">Scheduled Messages</h3>
//                             <span className="text-sm text-gray-500">Real-time performance</span>
//                         </div>
//                         <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
//                             <StatCard 
//                                 title="Total Processed" 
//                                 value={scheduledData.total.toLocaleString()} 
//                                 colorClass="border-indigo-500" 
//                                 icon={icons.schedule}
//                                 description="All scheduled messages"
//                             />
//                             <StatCard 
//                                 title="Successful Sends" 
//                                 value={scheduledData.success.toLocaleString()} 
//                                 colorClass="border-green-500" 
//                                 icon={icons.success}
//                                 description="Delivered successfully"
//                                 trend={{ isPositive: true, value: '+12%' }}
//                             />
//                             <StatCard 
//                                 title="Failed Sends" 
//                                 value={scheduledData.failure.toLocaleString()} 
//                                 colorClass="border-red-500" 
//                                 icon={icons.failure}
//                                 description="Delivery failures"
//                                 trend={{ isPositive: false, value: '-5%' }}
//                             />
//                         </div>
//                     </section>
                    
//                     {/* --- 2. Auto Responder Analytics --- */}
//                     <section className="space-y-6">
//                         <div className="flex items-center justify-between">
//                             <h3 className="text-xl font-bold text-gray-900">Auto Responder Performance</h3>
//                             <span className="text-sm text-gray-500">Trigger-based analytics</span>
//                         </div>
//                         <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
//                             <StatCard 
//                                 title="Total Times Triggered" 
//                                 value={responderData.total_hits.toLocaleString()} 
//                                 colorClass="border-blue-500" 
//                                 icon={icons.trigger}
//                                 description="Auto-responder activations"
//                             />
//                             <StatCard 
//                                 title="Successful Replies" 
//                                 value={successfulReplies.toLocaleString()}
//                                 colorClass="border-green-500" 
//                                 icon={icons.success}
//                                 description="Replies sent successfully"
//                             />
//                             <StatCard 
//                                 title="Success Rate" 
//                                 value={`${successRate}%`}
//                                 colorClass="border-emerald-500" 
//                                 icon={<svg className="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>}
//                                 description="Overall performance"
//                             />
//                         </div>
//                     </section>
                    
//                     {/* --- 3. Campaign (Broadcast) Analytics --- */}
//                     <section className="space-y-6">
//                         <div className="flex items-center justify-between">
//                             <h3 className="text-xl font-bold text-gray-900">Broadcast Campaigns</h3>
//                             <span className="text-sm text-gray-500">Campaign performance</span>
//                         </div>
//                         <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
//                             <StatCard 
//                                 title="Total Broadcast Attempts" 
//                                 value={campaignData.total.toLocaleString()} 
//                                 colorClass="border-purple-500" 
//                                 icon={icons.broadcast}
//                                 description="All broadcast campaigns"
//                             />
//                             <StatCard 
//                                 title="Successful Deliveries" 
//                                 value={campaignData.success.toLocaleString()} 
//                                 colorClass="border-green-500" 
//                                 icon={icons.delivery}
//                                 description="Messages delivered"
//                             />
//                             <StatCard 
//                                 title="Campaign Failures" 
//                                 value={campaignData.failure.toLocaleString()} 
//                                 colorClass="border-red-500" 
//                                 icon={icons.failure}
//                                 description="Delivery failures"
//                             />
//                         </div>
//                     </section>

//                 </div>
//             </div>
//         </AuthenticatedLayout>
//     );
// }