// resources/js/Pages/Scheduler/History.jsx
import React from 'react';
import AuthenticatedLayout from '@/Layouts/WaAuthLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, messages, orderStatuses, userFormTemplates }) {
    
    const handleDelete = (message) => {
        if (confirm('Are you sure you want to delete this scheduled message?')) {
            router.delete(route('scheduler.destroy', message.id));
        }
    };
    
    const getActionText = (type) => {
        switch (type) {
            case 'App\\Models\\Order': return 'Order Follow-up';
            case 'App\\Models\\FormSubmission': return 'Form Submission';
            default: return 'Unknown Action';
        }
    }
    
    const getCriteriaText = (message) => {
        const criteria = message.target_criteria;
        
        if (message.action_type === 'App\\Models\\Order') {
            const statusName = criteria.status 
                ? (Object.entries(orderStatuses).find(([key]) => key === criteria.status)?.[1] || criteria.status) 
                : '';
                
            if (criteria.type === 'ALL') return 'All Orders';
            if (criteria.type === 'STATUS' && criteria.status) return `Status: ${statusName}`;
            if (criteria.type === 'SPECIFIC_ORDER' && criteria.order_id) return `Order #${criteria.order_id}`;
        }
        
        if (message.action_type === 'App\\Models\\FormSubmission') {
            const template = userFormTemplates.find(t => t.id === criteria.form_template_id);
            const templateName = template ? template.name : 'Unknown Form';

            if (criteria.type === 'ALL') return `${templateName} (All)`;
            if (criteria.type === 'SPECIFIC_SUBMISSION' && criteria.form_submission_id) return `Submission #${criteria.form_submission_id}`;
        }
        
        return 'No criteria';
    }

    const getStatusBadge = (message) => {
        if (message.sent_at) {
            return { 
                text: 'SENT', 
                class: 'bg-green-100 text-green-800 border border-green-200',
                icon: '✅'
            };
        } else if (new Date(message.send_at) < new Date() && message.sent_count === 0) {
            return { 
                text: 'EXPIRED', 
                class: 'bg-gray-100 text-gray-800 border border-gray-200',
                icon: '⏰'
            };
        } else {
            return { 
                text: 'PENDING', 
                class: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                icon: '⏳'
            };
        }
    }

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const getStats = () => {
        const total = messages.length;
        const sent = messages.filter(m => m.sent_at).length;
        const pending = messages.filter(m => !m.sent_at && new Date(m.send_at) > new Date()).length;
        const expired = messages.filter(m => !m.sent_at && new Date(m.send_at) < new Date()).length;

        return { total, sent, pending, expired };
    };

    const stats = getStats();

    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Scheduled Messages History 📅</h2>
                        <p className="text-gray-600 mt-1">Track and manage your automated message schedules</p>
                    </div>
                    <div className="flex space-x-3 mt-4 sm:mt-0">
                        <Link 
                            href={route('schedulers.create')} 
                            className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            Schedule New Message
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
            <Head title="Scheduled Messages History" />

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* Stats Overview */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-indigo-400">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">Total Scheduled</p>
                                    <p className="text-2xl font-bold text-gray-900">{stats.total}</p>
                                </div>
                                <div className="p-3 bg-indigo-100 rounded-lg">
                                    <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-green-400">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">Sent</p>
                                    <p className="text-2xl font-bold text-gray-900">{stats.sent}</p>
                                </div>
                                <div className="p-3 bg-green-100 rounded-lg">
                                    <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-yellow-400">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">Pending</p>
                                    <p className="text-2xl font-bold text-gray-900">{stats.pending}</p>
                                </div>
                                <div className="p-3 bg-yellow-100 rounded-lg">
                                    <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-gray-400">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">Expired</p>
                                    <p className="text-2xl font-bold text-gray-900">{stats.expired}</p>
                                </div>
                                <div className="p-3 bg-gray-100 rounded-lg">
                                    <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Messages Table */}
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
                                        <h3 className="text-lg font-bold text-gray-900">All Scheduled Messages</h3>
                                        <p className="text-gray-600">Manage your automated message schedules</p>
                                    </div>
                                </div>
                                <span className="bg-indigo-100 text-indigo-800 text-sm font-medium px-3 py-1 rounded-full">
                                    {messages.length} total
                                </span>
                            </div>
                        </div>

                        {messages.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Schedule
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Trigger
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Criteria
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Message
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {messages.map((message) => {
                                            const status = getStatusBadge(message);
                                            return (
                                                <tr key={message.id} className="hover:bg-gray-50 transition-colors">
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-medium text-gray-900">
                                                            {formatDate(message.send_at)}
                                                        </div>
                                                        <div className="text-xs text-gray-500">
                                                            {message.sent_at ? `Sent: ${formatDate(message.sent_at)}` : 'Not sent yet'}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {getActionText(message.action_type)}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {getCriteriaText(message)}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${status.class}`}>
                                                            {status.icon} {status.text}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <div className="text-sm text-gray-900 max-w-xs truncate">
                                                            {message.message}
                                                        </div>
                                                        <div className="text-xs text-gray-500 mt-1">
                                                            {message.sent_count || 0} sent • {message.failed_count || 0} failed
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        {!message.sent_at && (
                                                            <button 
                                                                onClick={() => handleDelete(message)} 
                                                                className="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-lg text-xs font-medium transition-colors flex items-center space-x-1"
                                                            >
                                                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                </svg>
                                                                <span>Delete</span>
                                                            </button>
                                                        )}
                                                        {message.sent_at && (
                                                            <span className="text-gray-400 text-xs">Completed</span>
                                                        )}
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
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p className="text-gray-500 text-lg mb-2">No scheduled messages</p>
                                <p className="text-gray-400 text-sm mb-6">Create your first scheduled message to automate customer communication</p>
                                <Link
                                    href={route('schedulers.create')}
                                    className="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors"
                                >
                                    Schedule First Message
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}