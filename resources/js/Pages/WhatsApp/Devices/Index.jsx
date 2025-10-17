// resources/js/Pages/Devices/Index.jsx
import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/WaAuthLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, devices }) {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [deviceToDelete, setDeviceToDelete] = useState(null);
    const [activeTab, setActiveTab] = useState('all');

    const getStatusInfo = (status) => {
        switch (status) {
            case 'connected':
                return { 
                    text: 'Connected', 
                    color: 'bg-green-100 text-green-800 border-green-300',
                    icon: '🟢',
                    description: 'Ready to send messages'
                };
            case 'pending-qr':
            case 'qr-received':
                return { 
                    text: 'Waiting for Scan', 
                    color: 'bg-yellow-100 text-yellow-800 border-yellow-300',
                    icon: '🟡',
                    description: 'Scan QR code to connect'
                };
            case 'connecting':
                return { 
                    text: 'Connecting', 
                    color: 'bg-blue-100 text-blue-800 border-blue-300',
                    icon: '🔵',
                    description: 'Establishing connection...'
                };
            case 'disconnected':
            case 'error':
                return { 
                    text: 'Disconnected', 
                    color: 'bg-red-100 text-red-800 border-red-300',
                    icon: '🔴',
                    description: 'Reconnect to use this device'
                };
            default:
                return { 
                    text: 'Unknown', 
                    color: 'bg-gray-100 text-gray-800 border-gray-300',
                    icon: '⚫',
                    description: 'Status unknown'
                };
        }
    };

    // Filter devices based on active tab
    const filteredDevices = devices.filter(device => {
        if (activeTab === 'all') return true;
        if (activeTab === 'connected') return device.status === 'connected';
        if (activeTab === 'disconnected') return device.status !== 'connected';
        return true;
    });

    const connectedCount = devices.filter(d => d.status === 'connected').length;
    const disconnectedCount = devices.filter(d => d.status !== 'connected').length;

    function confirmDelete(device) {
        setDeviceToDelete(device);
        setIsModalOpen(true);
    }

    function deleteDevice() {
        if (deviceToDelete) {
            router.delete(route('devices.destroy', { id: deviceToDelete.id }), { 
                onFinish: () => {
                    setIsModalOpen(false);
                    setDeviceToDelete(null);
                }
            });
        }
    }

    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">WhatsApp Devices</h2>
                        <p className="text-gray-600 mt-1">Manage your connected WhatsApp accounts</p>
                    </div>
                    <div className="flex space-x-3 mt-4 sm:mt-0">
                        <Link 
                            href={route('analytics.index')} 
                            className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            View Analytics
                        </Link>
                        <Link 
                            href={route('devices.start')} 
                            className="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition-colors flex items-center space-x-2"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Add New Device</span>
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Connected Devices" />

            <div className="py-8">
                <div className="max-w-6xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* Stats Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-indigo-500">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Total Devices</p>
                                    <p className="text-3xl font-bold text-gray-900 mt-1">{devices.length}</p>
                                </div>
                                <div className="p-3 bg-indigo-100 rounded-xl">
                                    <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-green-500">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Connected</p>
                                    <p className="text-3xl font-bold text-gray-900 mt-1">{connectedCount}</p>
                                </div>
                                <div className="p-3 bg-green-100 rounded-xl">
                                    <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-red-500">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Disconnected</p>
                                    <p className="text-3xl font-bold text-gray-900 mt-1">{disconnectedCount}</p>
                                </div>
                                <div className="p-3 bg-red-100 rounded-xl">
                                    <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Tab Navigation */}
                    <div className="bg-white rounded-2xl shadow-sm mb-8">
                        <div className="border-b border-gray-200">
                            <nav className="flex -mb-px">
                                <button
                                    onClick={() => setActiveTab('all')}
                                    className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                                        activeTab === 'all'
                                            ? 'border-indigo-500 text-indigo-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                                >
                                    All Devices ({devices.length})
                                </button>
                                <button
                                    onClick={() => setActiveTab('connected')}
                                    className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                                        activeTab === 'connected'
                                            ? 'border-indigo-500 text-indigo-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                                >
                                    Connected ({connectedCount})
                                </button>
                                <button
                                    onClick={() => setActiveTab('disconnected')}
                                    className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                                        activeTab === 'disconnected'
                                            ? 'border-indigo-500 text-indigo-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                                >
                                    Disconnected ({disconnectedCount})
                                </button>
                            </nav>
                        </div>
                    </div>

                    {/* Devices List */}
                    <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div className="p-8">
                            {filteredDevices.length > 0 ? (
                                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    {filteredDevices.map((device) => {
                                        const statusInfo = getStatusInfo(device.status);
                                        const isConnected = device.status === 'connected';

                                        return (
                                            <div key={device.id} className="border border-gray-200 rounded-2xl p-6 bg-white hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                                <div className="flex items-start justify-between mb-4">
                                                    <div className="flex items-center space-x-3">
                                                        <div className="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                                                            {device.name ? device.name.charAt(0).toUpperCase() : 'D'}
                                                        </div>
                                                        <div>
                                                            <h3 className="font-bold text-lg text-gray-900">
                                                                {device.name || `Device #${device.id}`}
                                                            </h3>
                                                            <p className="text-sm text-gray-600">+{device.phone}</p>
                                                        </div>
                                                    </div>
                                                    <div className="text-right">
                                                        <span className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border ${statusInfo.color}`}>
                                                            <span className="mr-1">{statusInfo.icon}</span>
                                                            {statusInfo.text}
                                                        </span>
                                                        <p className="text-xs text-gray-500 mt-1">{statusInfo.description}</p>
                                                    </div>
                                                </div>

                                                <div className="mb-4">
                                                    <p className="text-sm text-gray-600 mb-2">
                                                        Session ID: <span className="font-mono text-gray-800 bg-gray-100 px-2 py-1 rounded text-xs">{device.session_id}</span>
                                                    </p>
                                                    <p className="text-xs text-gray-500">
                                                        Last updated: {new Date(device.updated_at).toLocaleString()}
                                                    </p>
                                                </div>

                                                <div className="flex space-x-3">
                                                    <Link
                                                        href={route('campaigns.create', { session_id: device.session_id })}
                                                        className={`flex-1 px-4 py-2 text-sm font-semibold rounded-lg shadow-sm transition-all duration-200 flex items-center justify-center space-x-2 ${
                                                            isConnected 
                                                                ? 'bg-gradient-to-r from-emerald-500 to-green-600 text-white hover:from-emerald-600 hover:to-green-700' 
                                                                : 'bg-gray-200 text-gray-500 cursor-not-allowed'
                                                        }`}
                                                        disabled={!isConnected}
                                                        title={isConnected ? 'Start a new campaign with this device' : 'Device must be connected to send campaigns'}
                                                    >
                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                        </svg>
                                                        <span>Send Campaign</span>
                                                    </Link>

                                                    <button 
                                                        onClick={() => confirmDelete(device)} 
                                                        className="px-4 py-2 text-sm text-red-600 border border-red-300 rounded-lg hover:bg-red-50 transition-colors font-semibold flex items-center space-x-2"
                                                        title="Disconnect this device"
                                                    >
                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        <span>Disconnect</span>
                                                    </button>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            ) : (
                                <div className="text-center py-12">
                                    <div className="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <h3 className="text-lg font-semibold text-gray-900 mb-2">No devices found</h3>
                                    <p className="text-gray-500 mb-6">
                                        {activeTab === 'connected' 
                                            ? "You don't have any connected devices yet."
                                            : "Get started by adding your first WhatsApp device."
                                        }
                                    </p>
                                    <Link 
                                        href={route('devices.start')} 
                                        className="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors inline-flex items-center space-x-2"
                                    >
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        <span>Add Your First Device</span>
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Custom Confirmation Modal */}
            {isModalOpen && deviceToDelete && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
                        <div className="p-6">
                            <div className="flex items-center space-x-3 mb-4">
                                <div className="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                                    <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.3 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 className="text-xl font-bold text-gray-900">Disconnect Device</h3>
                                    <p className="text-sm text-gray-600">This action cannot be undone</p>
                                </div>
                            </div>
                            
                            <div className="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                                <p className="text-red-800 font-medium">
                                    Are you sure you want to disconnect:
                                </p>
                                <p className="text-red-700 font-semibold text-lg mt-1">
                                    {deviceToDelete.name || `Device #${deviceToDelete.id}`}
                                </p>
                                <p className="text-red-600 text-sm mt-2">
                                    Phone: +{deviceToDelete.phone}
                                </p>
                            </div>
                            
                            <p className="text-sm text-gray-600 mb-6">
                                This will log out the WhatsApp session and require a new QR scan to reconnect. 
                                Any ongoing campaigns using this device will be affected.
                            </p>
                            
                            <div className="flex justify-end space-x-3">
                                <button
                                    onClick={() => setIsModalOpen(false)}
                                    className="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    onClick={deleteDevice}
                                    className="px-6 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-2"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    <span>Disconnect Permanently</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}