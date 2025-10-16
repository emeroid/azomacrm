// resources/js/Components/Pagination.jsx
import { Link } from '@inertiajs/react';

export default function Pagination({ links, className = '' }) {
    if (links.length <= 3) return null;

    return (
        <div className={`flex items-center space-x-1 ${className}`}>
            {links.map((link, index) => (
                <Link
                    key={index}
                    href={link.url || '#'}
                    className={`px-4 py-2 rounded-md text-sm ${
                        link.active
                            ? 'bg-indigo-500 text-white'
                            : 'text-gray-700 hover:bg-gray-100'
                    } ${!link.url ? 'text-gray-400 cursor-not-allowed' : ''}`}
                    disabled={!link.url}
                >
                    {link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                </Link>
            ))}
        </div>
    );
}