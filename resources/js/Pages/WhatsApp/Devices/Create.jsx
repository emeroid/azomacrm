import React, { useEffect, useState, useRef } from 'react';
import { Head, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/WaAuthLayout';
import axios from 'axios';

export default function Create({ auth, initialDevice, sessionId }) {
    
    const [device, setDevice] = useState(initialDevice);
    const [status, setStatus] = useState(initialDevice?.status || 'pending-qr');
    const [qrCodeUrl, setQrCodeUrl] = useState(initialDevice?.qr_code_url || '');
    const [isRestarting, setIsRestarting] = useState(false);
    const [pollingCount, setPollingCount] = useState(0);
    
    // Use a ref for the interval to prevent issues with stale state in closures
    const pollingIntervalRef = useRef(null);

    const stopPolling = () => {
        if (pollingIntervalRef.current) {
            clearInterval(pollingIntervalRef.current);
            pollingIntervalRef.current = null;
        }
    };

    // New function to handle restarting the session
    const restartSession = () => {
        setIsRestarting(true);
        stopPolling();
        
        // This call re-triggers the whole process on the backend
        router.get(route('devices.start'), {}, {
            onSuccess: () => {
                // The page will reload via redirect from the controller,
                // so we don't need to do much here.
                console.log("Session restart initiated.");
            },
            onError: (errors) => {
                console.error("Failed to restart session:", errors);
                setStatus('failed');
                setIsRestarting(false);
            }
        });
    };

    useEffect(() => {
        const pollStatus = async () => {
            try {
                setPollingCount(prev => prev + 1);
                
                // NEW: Hitting the lightweight JSON endpoint
                const response = await axios.get(route('devices.get-status', { sessionId: device?.session_id || sessionId }));
                const updatedDevice = response.data;
                
                setDevice(updatedDevice);
                setQrCodeUrl(updatedDevice.qr_code_url);
                setStatus(updatedDevice.status);

            } catch (error) {
                console.error('Polling failed:', error);
                setStatus('failed'); // Set a failed state on API error
            }
        };

        // Start polling only if the process is not in a final state
        if (!['connected', 'failed', 'expired', 'disconnected'].includes(status)) {
            pollingIntervalRef.current = setInterval(pollStatus, 3000);
        } else {
            stopPolling();
        }

        // If connected, redirect after a short delay
        if (status === 'connected') {
            setTimeout(() => {
                router.visit(route('devices.index'), {
                    // Using Inertia's flash messages
                    with: { success: 'Device connected successfully!' } 
                });
            }, 1500);
        }

        // Cleanup function
        return () => stopPolling();
        
    }, [status, device?.session_id, sessionId]); // Re-run effect if status changes

    // --- Status Configuration with New States ---
    const statusConfig = {
        'pending-qr': {
            message: 'Starting session on gateway, awaiting QR code...',
            icon: '⏳',
            color: 'border-yellow-500',
            progress: 25
        },
        'scanning': {
            message: 'QR code generated. Please scan it with your phone.',
            icon: '📱',
            color: 'border-blue-500',
            progress: 50
        },
        'connecting': {
            message: 'QR code scanned. Connecting device...',
            icon: '🔗',
            color: 'border-purple-500',
            progress: 75
        },
        'connected': {
            message: 'Device connected successfully!',
            icon: '✅',
            color: 'border-green-500',
            progress: 100
        },
        'expired': {
            message: 'QR code expired. Please try again.',
            icon: '⌛',
            color: 'border-red-500',
            progress: 0
        },
        'failed': {
            message: 'Connection failed. Please try again.',
            icon: '❌',
            color: 'border-red-500',
            progress: 0
        },
        'disconnected': {
            message: 'Device disconnected.',
            icon: '🔌',
            color: 'border-red-500',
            progress: 0
        },
        'default': {
            message: 'Initializing session...',
            icon: '⚡',
            color: 'border-gray-500',
            progress: 10
        }
    };

    const currentStatus = statusConfig[status] || statusConfig.default;

    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Link New WhatsApp Device</h2>
                        <p className="text-gray-600 mt-1">Connect your WhatsApp account to start messaging</p>
                    </div>
                    <div className="flex space-x-3 mt-4 sm:mt-0">
                        <Link 
                            href={route('devices.index')} 
                            className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            View Devices
                        </Link>
                    </div>
                    {console.log("DEVICE LOG: ", device.qr_code_url)}
                </div>
            }
        >
            <Head title="Link New Device" />

            <div className="py-8">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* Progress Steps */}
                    <div className="bg-white rounded-2xl shadow-sm p-6 mb-8">
                        <div className="flex items-center justify-between mb-6">
                            {[1, 2, 3, 4].map((step) => (
                                <React.Fragment key={step}>
                                    <div className="flex flex-col items-center">
                                        <div className={`w-10 h-10 rounded-full flex items-center justify-center border-2 font-semibold text-lg ${
                                            currentStatus.progress >= (step * 25)
                                                ? 'bg-indigo-600 border-indigo-600 text-white'
                                                : 'border-gray-300 text-gray-500'
                                        }`}>
                                            {step}
                                        </div>
                                        <div className="mt-2 text-center">
                                            <div className="text-xs text-gray-500">
                                                {step === 1 && 'Start'}
                                                {step === 2 && 'QR Code'}
                                                {step === 3 && 'Scan'}
                                                {step === 4 && 'Connected'}
                                            </div>
                                        </div>
                                    </div>
                                    {step < 4 && (
                                        <div className={`flex-1 h-1 mx-2 ${
                                            currentStatus.progress >= (step * 25) ? 'bg-indigo-600' : 'bg-gray-200'
                                        }`}></div>
                                    )}
                                </React.Fragment>
                            ))}
                        </div>
                        
                        {/* Status Message */}
                        <div className="text-center">
                            <div className="text-3xl mb-2">{currentStatus.icon}</div>
                            <h3 className="text-lg font-semibold text-gray-900">{currentStatus.message}</h3>
                            <p className="text-sm text-gray-500 mt-1">
                                Polling for updates... ({pollingCount} checks)
                            </p>
                        </div>
                    </div>

                    {/* QR Code Section */}
                    <div className="bg-white rounded-2xl shadow-lg overflow-hidden border-t-8 border-indigo-500">
                        <div className="p-8">
                            <div className="text-center mb-6">
                                <h3 className="text-xl font-bold text-gray-900 mb-2">Scan QR Code</h3>
                                <p className="text-gray-600">
                                    Open WhatsApp on your phone, go to <strong>Settings → Linked Devices</strong>, 
                                    and scan the QR code below.
                                </p>
                            </div>
                            
                            <div className="flex justify-center min-h-[320px] items-center bg-gradient-to-br from-gray-50 to-gray-100 p-6 rounded-2xl shadow-inner">
                                {status === 'scanning' && qrCodeUrl ? (
                                    <div className="text-center">
                                        <img 
                                            src={qrCodeUrl} 
                                            alt="WhatsApp QR Code" 
                                            className="w-72 h-72 rounded-xl shadow-2xl border-8 border-white mx-auto"
                                        />
                                        <p className="mt-4 text-sm text-gray-500">
                                            The QR code will automatically refresh if needed
                                        </p>
                                    </div>
                                ) : (
                                    <div className="text-center p-6">
                                        <div className="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-full mb-4">
                                            {(isRestarting || status === 'pending-qr' || status === 'connecting') ? (
                                                <svg className="w-8 h-8 text-indigo-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 2a10 10 0 100 20 10 10 0 000-20z"></path>
                                                </svg>
                                            ) : (
                                                <span className="text-4xl">{currentStatus.icon}</span>
                                            )}
                                        </div>
                                        <p className="text-lg font-medium text-gray-700">{currentStatus.message}</p>
                                        <p className="mt-2 text-sm text-gray-500">
                                            {isRestarting ? 'Restarting session...' : 'Please wait while we prepare your connection'}
                                        </p>
                                        
                                        {/* Show restart button for error states */}
                                        {['expired', 'failed', 'disconnected'].includes(status) && (
                                            <button
                                                onClick={restartSession}
                                                disabled={isRestarting}
                                                className="mt-4 px-6 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:bg-gray-400 transition-colors"
                                            >
                                                {isRestarting ? 'Restarting...' : 'Try Again'}
                                            </button>
                                        )}
                                    </div>
                                )}
                            </div>
                            
                            {/* Session Info */}
                            <div className="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <div className="flex items-center justify-between text-sm">
                                    <span className="text-gray-600 font-medium">Session ID:</span>
                                    <span className="font-mono text-gray-800 bg-white px-3 py-1 rounded-lg border">
                                        {device?.session_id || sessionId}
                                    </span>
                                </div>
                                <p className="mt-3 text-xs text-gray-500 text-center">
                                    You will be redirected automatically once your phone is linked. 
                                    This usually takes 15-30 seconds after scanning.
                                </p>
                            </div>

                            {/* Help Tips */}
                            <div className="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div className="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg">
                                    <span className="text-blue-600 text-lg">📱</span>
                                    <div>
                                        <p className="font-medium text-blue-900">Use WhatsApp Mobile</p>
                                        <p className="text-blue-700 text-xs mt-1">Make sure you have WhatsApp installed on your phone</p>
                                    </div>
                                </div>
                                <div className="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                                    <span className="text-green-600 text-lg">⚡</span>
                                    <div>
                                        <p className="font-medium text-green-900">Stay on This Page</p>
                                        <p className="text-green-700 text-xs mt-1">Keep this page open until connection is complete</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Troubleshooting Section */}
                    <div className="mt-8 bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
                        <h4 className="font-semibold text-yellow-800 mb-3 flex items-center">
                            <svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                            </svg>
                            Having trouble connecting?
                        </h4>
                        <ul className="text-sm text-yellow-700 space-y-2">
                            <li>• Make sure your phone has internet connection</li>
                            <li>• Try refreshing the QR code if it expires</li>
                            <li>• Ensure you're using the latest version of WhatsApp</li>
                            <li>• Check that your phone's camera can scan QR codes properly</li>
                        </ul>
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}
