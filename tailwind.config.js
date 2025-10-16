import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    safelist: [
        // Form container
        'embedded-form-container',
        
        // Labels
        'block', 'text-sm', 'font-medium', 'text-gray-700',
        
        // Inputs
        'mt-1', 'block', 'w-full', 'rounded-md', 'border-gray-300', 
        'shadow-sm', 'focus:border-indigo-500', 'focus:ring-indigo-500', 
        'sm:text-sm', 'border-red-500',
        
        // Select
        'py-2', 'pl-3', 'pr-10', 'text-base', 'focus:outline-none',
        
        // Radio/Checkbox
        'h-4', 'w-4', 'text-indigo-600', 'focus:ring-indigo-500', 
        'border-gray-300', 'ml-2',
        
        // Options container
        'flex', 'flex-col', 'gap-2', 'items-center', 'space-x-4',
        
        // Product selector
        'space-y-4', 'p-3', 'border', 'rounded-md', 'justify-between',
        
        // Submit button
        'px-4', 'py-2', 'bg-blue-600', 'text-white', 'rounded-md', 
        'hover:bg-blue-700', 'focus:outline-none', 'focus:ring-2', 
        'focus:ring-blue-500', 'focus:ring-offset-2',
        
        // Error messages
        'error-message', 'text-red-600',
        
        // Success/error alerts
        'p-4', 'mb-4', 'text-sm', 'text-green-700', 'bg-green-100', 
        'rounded-lg', 'text-red-700', 'bg-red-100'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
