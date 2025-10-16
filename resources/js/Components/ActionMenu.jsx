import { useState, useRef, useEffect } from 'react';

export default function ActionMenu({ 
    onCall, 
    onWhatsApp,
    onTemplate,
    onOutcome,
    onStatusUpdate,
    outcomes
}) {
    const [isOpen, setIsOpen] = useState(false);
    const [showOutcomes, setShowOutcomes] = useState(false);
    const menuRef = useRef(null);
    const [isMobile, setIsMobile] = useState(false);

    useEffect(() => {
        const handleResize = () => {
            setIsMobile(window.innerWidth < 768);
        };
        
        handleResize(); // Set initial value
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    // Close menu when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (menuRef.current && !menuRef.current.contains(event.target)) {
                setIsOpen(false);
                setShowOutcomes(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    // const handleOutcomeClick = () => {
    //     if (outcomes && outcomes.length > 0) {
    //         setShowOutcomes(true);
    //     } else {
    //         onOutcome();
    //         setIsOpen(false);
    //     }
    // };

    return (
        <div className="relative" ref={menuRef}>
            <button
                type="button"
                onClick={() => setIsOpen(!isOpen)}
                className="p-1 sm:p-2 rounded-xl bg-blue-500 text-white hover:bg-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                aria-label="Open action menu"
                aria-expanded={isOpen}
            >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clipRule="evenodd" />
                </svg>
            </button>

            {/* Desktop Dropdown */}
            {!isMobile && isOpen && (
                <div className="absolute right-0 bottom-full mb-2 w-56 bg-white rounded-md shadow-lg z-50 ring-1 ring-black ring-opacity-5">
                    <div className="py-1">
                        <ActionMenuItem 
                            icon={<CallIcon />}
                            label="Call Customer"
                            onClick={() => {
                                onCall();
                                setIsOpen(false);
                            }}
                        />
                        {/* <ActionMenuItem 
                            icon={<WhatsAppIcon />}
                            label="Chat on WhatsApp"
                            onClick={() => {
                                onWhatsApp();
                                setIsOpen(false);
                            }}
                        /> */}
                        <ActionMenuItem 
                            icon={<TemplateIcon />}
                            label="WhatsApp Template"
                            onClick={() => {
                                onTemplate();
                                setIsOpen(false);
                            }}
                        />
                        {/* <ActionMenuItem 
                            icon={<OutcomeIcon />}
                            label="Add Outcome"
                            onClick={handleOutcomeClick}
                        /> */}
                        <ActionMenuItem 
                            icon={<StatusIcon />}
                            label="Update Order Status"
                            onClick={() => {
                                onStatusUpdate();
                                setIsOpen(false);
                            }}
                        />
                    </div>
                </div>
            )}

            {/* Mobile Bottom Sheet Modal */}
            {isMobile && isOpen && (
                <div className="fixed inset-0 z-50">
                    {/* Overlay */}
                    <div 
                        className="absolute inset-0 bg-black bg-opacity-50"
                        onClick={() => setIsOpen(false)}
                    />
                    
                    {/* Modal Content */}
                    <div className="absolute bottom-0 left-0 right-0 bg-white rounded-t-lg shadow-xl">
                        <div className="p-2 border-b border-gray-200 flex justify-center">
                            <div className="h-1 w-8 bg-gray-300 rounded-full"></div>
                        </div>
                        <div className="max-h-[70vh] overflow-y-auto">
                            <ActionMenuItem 
                                icon={<CallIcon />}
                                label="Call Customer"
                                onClick={() => {
                                    onCall();
                                    setIsOpen(false);
                                }}
                                mobile
                            />
                            {/* <ActionMenuItem 
                                icon={<WhatsAppIcon />}
                                label="Chat on WhatsApp"
                                onClick={() => {
                                    onWhatsApp();
                                    setIsOpen(false);
                                }}
                                mobile
                            /> */}
                            <ActionMenuItem 
                                icon={<TemplateIcon />}
                                label="WhatsApp Template"
                                onClick={() => {
                                    onTemplate();
                                    setIsOpen(false);
                                }}
                                mobile
                            />
                            {/* <ActionMenuItem 
                                icon={<OutcomeIcon />}
                                label="Add Outcome"
                                onClick={handleOutcomeClick}
                                mobile
                            /> */}
                            <ActionMenuItem 
                                icon={<StatusIcon />}
                                label="Update Order Status"
                                onClick={() => {
                                    onStatusUpdate();
                                    setIsOpen(false);
                                }}
                                mobile
                            />
                        </div>
                    </div>
                </div>
            )}

            {/* Outcomes Submenu */}
            {showOutcomes && (
                <div className={`${isMobile ? 'fixed inset-0 z-50' : 'absolute right-0 bottom-full mb-2'} w-56 bg-white rounded-md shadow-lg z-50 ring-1 ring-black ring-opacity-5`}>
                    {isMobile && (
                        <>
                            <div 
                                className="absolute inset-0 bg-black bg-opacity-50"
                                onClick={() => setShowOutcomes(false)}
                            />
                            <div className="absolute bottom-0 left-0 right-0 bg-white rounded-t-lg shadow-xl">
                                <div className="p-2 border-b border-gray-200 flex justify-center">
                                    <div className="h-1 w-8 bg-gray-300 rounded-full"></div>
                                </div>
                            </div>
                        </>
                    )}
                    {/* <div className={`${isMobile ? 'max-h-[70vh] overflow-y-auto p-2' : 'py-1'}`}>
                        <div className="px-3 py-2 text-sm font-medium text-gray-700 border-b">
                            Select Outcome
                        </div>
                        {outcomes.map((outcome) => (
                            <button
                                key={outcome.key}
                                type="button"
                                onClick={() => {
                                    onOutcome(outcome);
                                    setIsOpen(false);
                                    setShowOutcomes(false);
                                }}
                                className="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left focus:outline-none focus:bg-gray-100"
                            >
                                {outcome?.name?.replace('_', ' ')}
                            </button>
                        ))}
                        <button
                            type="button"
                            onClick={() => {
                                onOutcome();
                                setIsOpen(false);
                                setShowOutcomes(false);
                            }}
                            className="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left border-t focus:outline-none focus:bg-gray-100"
                        >
                            + Add New Outcome
                        </button>
                    </div> */}
                    
                </div>
            )}
        </div>
    );
}

// Reusable Action Menu Item Component
function ActionMenuItem({ icon, label, onClick, mobile = false }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`flex items-center w-full text-left focus:outline-none ${
                mobile ? 'p-4 hover:bg-gray-50' : 'px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:bg-gray-100'
            }`}
        >
            <span className={`${mobile ? 'mr-4' : 'mr-2'} text-blue-500`}>
                {icon}
            </span>
            <span className={mobile ? 'text-base' : ''}>{label}</span>
        </button>
    );
}

// Icon components
function CallIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
        </svg>
    );
}

// function WhatsAppIcon() {
//     return (
//         <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
//             <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
//         </svg>
//     );
// }

function TemplateIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
        </svg>
    );
}

// function OutcomeIcon() {
//     return (
//         <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
//             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
//         </svg>
//     );
// }

function StatusIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
        </svg>
    );
}