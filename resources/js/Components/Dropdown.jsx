// resources/js/Components/Dropdown.jsx
import { useState } from 'react';

export const Dropdown = ({ children }) => {
    const [open, setOpen] = useState(false);

    return (
        <div className="relative">
            <div onClick={() => setOpen(!open)}>
                {children[0]}
            </div>
            {open && (
                <div className="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                    {children[1]}
                </div>
            )}
        </div>
    );
};

Dropdown.Trigger = ({ children }) => children;
Dropdown.Content = ({ children }) => (
    <div className="py-1" role="menu">
        {children}
    </div>
);
Dropdown.Link = ({ href, method = 'get', as = 'a', children, ...props }) => (
    <a
        href={href}
        method={method}
        as={as}
        className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
        {...props}
    >
        {children}
    </a>
);