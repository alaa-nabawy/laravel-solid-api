import { Link, usePage } from "@inertiajs/react";
import { ChevronDownIcon } from "@heroicons/react/24/outline";
import NavLink from "@/Components/NavLink";
import Dropdown from "@/Components/Dropdown";
import DropdownLink from "@/Components/DropdownLink";

export default function AppLayout({ children, header, appName = "Laravel" }) {
    const { props } = usePage();
    const { auth, flash } = props;

    return (
        <div className="min-h-screen bg-gray-100">
            {/* Navigation */}
            <nav className="bg-white shadow">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            {/* Logo */}
                            <div className="flex-shrink-0 flex items-center">
                                <Link
                                    href="/"
                                    className="text-xl font-bold text-gray-800"
                                >
                                    {appName}
                                </Link>
                            </div>

                            {/* Navigation Links */}
                            <div className="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink href="/" active={props.url === "/"}>
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    href="/about"
                                    active={props.url === "/about"}
                                >
                                    About
                                </NavLink>
                            </div>
                        </div>

                        {/* User Menu */}
                        <div className="hidden sm:flex sm:items-center sm:ml-6">
                            {auth.user ? (
                                <div className="ml-3 relative">
                                    <Dropdown align="right" width="48">
                                        <Dropdown.Trigger>
                                            <button className="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                                <div>{auth.user.name}</div>
                                                <ChevronDownIcon className="ml-1 h-4 w-4" />
                                            </button>
                                        </Dropdown.Trigger>

                                        <Dropdown.Content>
                                            <DropdownLink href="/profile">
                                                Profile
                                            </DropdownLink>
                                            <DropdownLink
                                                href="/logout"
                                                method="post"
                                                as="button"
                                            >
                                                Log Out
                                            </DropdownLink>
                                        </Dropdown.Content>
                                    </Dropdown>
                                </div>
                            ) : (
                                <div className="space-x-4">
                                    <Link
                                        href="/login"
                                        className="text-gray-500 hover:text-gray-700"
                                    >
                                        Login
                                    </Link>
                                    <Link
                                        href="/register"
                                        className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Register
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </nav>

            {/* Page Heading */}
            {header && (
                <header className="bg-white shadow">
                    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            {/* Flash Messages */}
            {flash.success && (
                <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mx-4 mt-4">
                    {flash.success}
                </div>
            )}
            {flash.error && (
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mx-4 mt-4">
                    {flash.error}
                </div>
            )}

            {/* Page Content */}
            <main>{children}</main>

            {/* Footer */}
            <footer className="bg-white border-t border-gray-200 mt-12">
                <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <div className="text-center text-gray-500 text-sm">
                        © {new Date().getFullYear()} {appName}. Built with
                        Laravel & React.
                    </div>
                </div>
            </footer>
        </div>
    );
}
