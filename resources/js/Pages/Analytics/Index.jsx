// resources/js/Pages/Analytics/Index.jsx
import React from 'react';
import AuthenticatedLayout from '@/Layouts/WaAuthLayout';
import { Head, Link } from '@inertiajs/react';

// Enhanced StatCard with better visuals
const StatCard = ({ title, value, colorClass, icon, description, trend }) => (
    <div className={`p-6 bg-white rounded-2xl shadow-lg border-l-4 ${colorClass} hover:shadow-xl transition-all duration-300 hover:-translate-y-1`}>
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

export default function AnalyticsIndex({ auth, scheduledData, responderData, campaignData }) {
    
    // Calculate AutoResponder Reply Success Rate
    const totalReplies = Object.values(responderData.reply_status).reduce((sum, count) => sum + count, 0);
    const successfulReplies = responderData.reply_status.sent || 0;
    const failedReplies = responderData.reply_status.failed || 0;
    const successRate = totalReplies > 0 ? ((successfulReplies / totalReplies) * 100).toFixed(1) : 0;
    
    // Icons for better visual consistency
    const icons = {
        schedule: <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>,
        success: <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>,
        failure: <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>,
        trigger: <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9 9 0 01-9-8 9 9 0 019-8c4.97 0 9 3.582 9 8z"></path></svg>,
        broadcast: <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h10a2 2 0 012 2v2M7 7a2 2 0 012-2h6a2 2 0 012 2v2"></path></svg>,
        delivery: <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-2 4v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7"></path></svg>
    };
    
    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Analytics Dashboard 📊</h2>
                        <p className="text-gray-600 mt-1">Monitor your WhatsApp campaign performance</p>
                    </div>
                    <div className="flex space-x-3 mt-4 sm:mt-0">
                        <Link 
                            href={route('scheduler.index')} 
                            className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            View Scheduler
                        </Link>
                        <Link 
                            href={route('campaigns.create')} 
                            className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            New Campaign
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
                                href={route('scheduler.index')}
                                color="border-indigo-500"
                                icon={<svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>}
                            />
                            <NavCard 
                                title="Auto Responders"
                                description="Manage automated replies"
                                href={route('auto-responders.index')}
                                color="border-blue-500"
                                icon={<svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>}
                            />
                            <NavCard 
                                title="Broadcast Campaigns"
                                description="Create new broadcasts"
                                href={route('campaigns.create')}
                                color="border-purple-500"
                                icon={<svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h10a2 2 0 012 2v2M7 7a2 2 0 012-2h6a2 2 0 012 2v2"></path></svg>}
                            />
                        </div>
                    </section>

                    {/* --- 1. Scheduled Messages Analytics --- */}
                    <section className="space-y-6">
                        <div className="flex items-center justify-between">
                            <h3 className="text-xl font-bold text-gray-900">Scheduled Messages</h3>
                            <span className="text-sm text-gray-500">Real-time performance</span>
                        </div>
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <StatCard 
                                title="Total Processed" 
                                value={scheduledData.total.toLocaleString()} 
                                colorClass="border-indigo-500" 
                                icon={icons.schedule}
                                description="All scheduled messages"
                            />
                            <StatCard 
                                title="Successful Sends" 
                                value={scheduledData.success.toLocaleString()} 
                                colorClass="border-green-500" 
                                icon={icons.success}
                                description="Delivered successfully"
                                trend={{ isPositive: true, value: '+12%' }}
                            />
                            <StatCard 
                                title="Failed Sends" 
                                value={scheduledData.failure.toLocaleString()} 
                                colorClass="border-red-500" 
                                icon={icons.failure}
                                description="Delivery failures"
                                trend={{ isPositive: false, value: '-5%' }}
                            />
                        </div>
                    </section>
                    
                    {/* --- 2. Auto Responder Analytics --- */}
                    <section className="space-y-6">
                        <div className="flex items-center justify-between">
                            <h3 className="text-xl font-bold text-gray-900">Auto Responder Performance</h3>
                            <span className="text-sm text-gray-500">Trigger-based analytics</span>
                        </div>
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <StatCard 
                                title="Total Times Triggered" 
                                value={responderData.total_hits.toLocaleString()} 
                                colorClass="border-blue-500" 
                                icon={icons.trigger}
                                description="Auto-responder activations"
                            />
                            <StatCard 
                                title="Successful Replies" 
                                value={successfulReplies.toLocaleString()}
                                colorClass="border-green-500" 
                                icon={icons.success}
                                description="Replies sent successfully"
                            />
                            <StatCard 
                                title="Success Rate" 
                                value={`${successRate}%`}
                                colorClass="border-emerald-500" 
                                icon={<svg className="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>}
                                description="Overall performance"
                            />
                        </div>
                    </section>
                    
                    {/* --- 3. Campaign (Broadcast) Analytics --- */}
                    <section className="space-y-6">
                        <div className="flex items-center justify-between">
                            <h3 className="text-xl font-bold text-gray-900">Broadcast Campaigns</h3>
                            <span className="text-sm text-gray-500">Campaign performance</span>
                        </div>
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <StatCard 
                                title="Total Broadcast Attempts" 
                                value={campaignData.total.toLocaleString()} 
                                colorClass="border-purple-500" 
                                icon={icons.broadcast}
                                description="All broadcast campaigns"
                            />
                            <StatCard 
                                title="Successful Deliveries" 
                                value={campaignData.success.toLocaleString()} 
                                colorClass="border-green-500" 
                                icon={icons.delivery}
                                description="Messages delivered"
                            />
                            <StatCard 
                                title="Campaign Failures" 
                                value={campaignData.failure.toLocaleString()} 
                                colorClass="border-red-500" 
                                icon={icons.failure}
                                description="Delivery failures"
                            />
                        </div>
                    </section>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}