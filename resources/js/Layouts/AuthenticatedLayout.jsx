import { Head, Link, router } from '@inertiajs/react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import { Dropdown } from '@/Components/Dropdown';
import { NavLink } from '@/Components/NavLink';
import { useState } from 'react';
import { ResponsiveNavLink } from '@/Components/NavLink';
import ToastMessages from '@/Components/ToastMessages';

export default function AuthenticatedLayout({ auth, header, children }) {
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title={header} />

            {/* Navigation */}
            <nav className="bg-white shadow-sm">
                <div className="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
                    <div className="flex justify-between h-16">
                        {/* Left side - Logo and nav items */}
                        <div className="flex items-center flex-1">
                            {/* Mobile menu button */}
                            <div className="flex-shrink-0 flex items-center sm:hidden mr-2">
                                <button
                                    onClick={() => setShowingNavigationDropdown(!showingNavigationDropdown)}
                                    className="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                                    aria-expanded="false"
                                >
                                    <span className="sr-only">Open main menu</span>
                                    <svg
                                        className={`${showingNavigationDropdown ? 'hidden' : 'block'} h-6 w-6`}
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                    <svg
                                        className={`${showingNavigationDropdown ? 'block' : 'hidden'} h-6 w-6`}
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {/* Logo */}
                            <div className="flex-shrink-0 flex items-center">
                                <a href="/admin">
                                    <p className='text-lg'>Azoma CRM</p>
                                </a>
                            </div>

                            {/* Desktop navigation */}
                            <div className="hidden sm:ml-4 sm:flex sm:space-x-4">
                                <NavLink href={route('orders.chat')} active={route().current('orders.chat')}>
                                    Messages
                                </NavLink>
                            </div>
                        </div>

                        {/* Right side - User dropdown */}
                        <div className="hidden sm:ml-4 sm:flex sm:items-center">
                            <div className="ml-3 relative">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
                                            >
                                                {auth.user.name}
                                                <svg
                                                    className="ml-2 -mr-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Mobile menu */}
                <div className={`${showingNavigationDropdown ? 'block' : 'hidden'} sm:hidden`}>
                    <div className="pt-2 pb-3 space-y-1 px-2">
                        <ResponsiveNavLink href={'#'} active={route().current('dashboard')}>
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href={route('orders.chat')} active={route().current('orders.chat')}>
                            Messages
                        </ResponsiveNavLink>
                    </div>

                    <div className="pt-4 pb-3 border-t border-gray-200">
                        <div className="flex items-center px-4">
                            <div className="flex-shrink-0">
                                <svg className="h-10 w-10 rounded-full text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <div className="text-base font-medium text-gray-800">{auth.user.name}</div>
                                <div className="text-sm font-medium text-gray-500">{auth.user.email}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Main content */}
            <main className="py-4 sm:py-6 px-2 sm:px-4 lg:px-8">
                <div className="max-w-7xl mx-auto">
                    {header && (
                        <h1 className="text-xl sm:text-2xl font-semibold text-gray-900 mb-4 sm:mb-6">
                            {header}
                        </h1>
                    )}
                    {children}
                </div>
                <ToastMessages />
            </main>
        </div>
    );
}