import React, { useState, useEffect, useCallback } from 'react';
import { Transition } from '@headlessui/react';
// 1. Uncomment this line in your Laravel/Inertia project:
import { usePage } from '@inertiajs/react'; 
import { X, CheckCircle, AlertTriangle, Info } from 'lucide-react'; 

// ----------------------------------------------------------------------
// 2. Configuration (Styles and Icons)
// ----------------------------------------------------------------------

/**
 * Maps flash type (key used in Laravel ->with()) to aesthetic styles and icons.
 */
const toastConfig = {
    success: {
        icon: CheckCircle,
        bgColor: 'bg-green-50',
        textColor: 'text-green-800',
        borderColor: 'border-green-500',
        iconColor: 'text-green-500',
        iconBgColor: 'bg-green-100',
    },
    error: {
        icon: X,
        bgColor: 'bg-red-50',
        textColor: 'text-red-800',
        borderColor: 'border-red-500',
        iconColor: 'text-red-500',
        iconBgColor: 'bg-red-100',
    },
    warning: {
        icon: AlertTriangle,
        bgColor: 'bg-yellow-50',
        textColor: 'text-yellow-800',
        borderColor: 'border-yellow-500',
        iconColor: 'text-yellow-600',
        iconBgColor: 'bg-yellow-100',
    },
    info: {
        icon: Info,
        bgColor: 'bg-blue-50',
        textColor: 'text-blue-800',
        borderColor: 'border-blue-500',
        iconColor: 'text-blue-500',
        iconBgColor: 'bg-blue-100',
    },
};

/**
 * Individual Toast Notification Component
 */
const ToastNotification = ({ type, message, id, onClose }) => {
    const config = toastConfig[type] || toastConfig.info;
    const IconComponent = config.icon;
    const [isVisible, setIsVisible] = useState(true);

    // Auto-dismiss logic
    useEffect(() => {
        const timer = setTimeout(() => {
            setIsVisible(false);
            // Wait for transition to finish before truly closing
            setTimeout(() => onClose(id), 300);
        }, 6000); // 6 seconds auto-dismiss

        return () => clearTimeout(timer);
    }, [id, onClose]);

    const handleClose = () => {
        setIsVisible(false);
        setTimeout(() => onClose(id), 300);
    };

    return (
        <Transition
            show={isVisible}
            as={React.Fragment}
            enter="transition ease-out duration-300 transform"
            enterFrom="translate-x-full opacity-0"
            enterTo="translate-x-0 opacity-100"
            leave="transition ease-in duration-300 transform"
            leaveFrom="translate-x-0 opacity-100"
            leaveTo="translate-x-full opacity-0"
        >
            {/* IMPROVED: Larger container with better spacing */}
            <div className={`mt-2 w-full max-w-md overflow-hidden rounded-lg border-l-4 shadow-xl ${config.bgColor} ${config.borderColor}`} role="alert">
                <div className="flex items-start p-4">
                    {/* IMPROVED: Larger icon with background */}
                    <div className={`flex-shrink-0 p-2 rounded-lg ${config.iconBgColor}`}>
                        <IconComponent className="h-6 w-6" aria-hidden="true" />
                    </div>
                    
                    {/* IMPROVED: Better text styling and spacing */}
                    <div className="ml-4 flex-1 min-w-0">
                        <p className={`text-base font-semibold leading-6 ${config.textColor}`}>
                            {type.charAt(0).toUpperCase() + type.slice(1)}
                        </p>
                        <p className={`mt-1 text-sm ${config.textColor} opacity-90 leading-5`}>
                            {message}
                        </p>
                    </div>
                    
                    {/* IMPROVED: Larger close button */}
                    <div className="ml-6 flex flex-shrink-0">
                        <button
                            onClick={handleClose}
                            className={`inline-flex rounded-lg p-2 ${config.bgColor} ${config.textColor} hover:bg-opacity-75 focus:outline-none focus:ring-2 focus:ring-offset-2 ${config.iconColor} transition-all duration-200 hover:scale-110`}
                        >
                            <span className="sr-only">Dismiss</span>
                            <X className="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                </div>
                
                {/* IMPROVED: Progress bar for auto-dismiss */}
                <div className="w-full bg-gray-200 h-1">
                    <div 
                        className={`h-1 ${config.borderColor} transition-all duration-6000 ease-linear`}
                        style={{ 
                            width: isVisible ? '0%' : '100%',
                            transition: 'width 6s linear'
                        }}
                    />
                </div>
            </div>
        </Transition>
    );
};

/**
 * Main component that connects to Inertia's flash messages.
 * This component handles the logic and positioning.
 */
const ToastMessages = () => { 
    // FIX: Safely access the flash object, defaulting to {} if it's null or undefined.
    // This prevents the TypeError when Object.entries is called.
    const flash = usePage().props.flash || {};

    // State to hold and manage currently displayed toasts.
    const [toasts, setToasts] = useState([]);

    const removeToast = useCallback((idToRemove) => {
        setToasts(prevToasts => prevToasts.filter(toast => toast.id !== idToRemove));
    }, []);

    // Effect to watch the 'flash' object and add new messages to the queue.
    useEffect(() => {
        let index = Date.now();
        const newToasts = [];
        
        // Iterate over the keys (success, error, info, warning) in the flash object
        Object.entries(flash).forEach(([type, message]) => {
            // Check for truthy message (Laravel sends null if not set)
            if (message) {
                // We only care about the keys defined in toastConfig
                if (toastConfig.hasOwnProperty(type)) {
                    // Prevent duplicate toasts if the component re-renders quickly
                    const isDuplicate = toasts.some(t => t.message === message && t.type === type);
                    
                    if (!isDuplicate) {
                         newToasts.push({
                            id: index++,
                            type,
                            message,
                        });
                    }
                }
            }
        });

        if (newToasts.length > 0) {
            // Append new toasts to the existing list
            setToasts(prevToasts => [...prevToasts, ...newToasts]);
        }
        
    }, [flash]); 

    return (
        // IMPROVED: Better positioning and sizing
        <div
            aria-live="assertive"
            className="fixed inset-0 flex items-start justify-end px-6 py-6 pointer-events-none sm:px-6 sm:py-8 z-[9999]"
        >
            <div className="w-full max-w-sm sm:max-w-md flex flex-col items-end space-y-4">
                {toasts.map(toast => (
                    <ToastNotification
                        key={toast.id}
                        id={toast.id}
                        type={toast.type}
                        message={toast.message}
                        onClose={removeToast}
                    />
                ))}
            </div>
        </div>
    );
};

export default ToastMessages;