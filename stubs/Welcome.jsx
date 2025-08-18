import { useState, useRef } from "react";
import { Head } from "@inertiajs/react";
import { useCounterStore } from "@/Stores/counter";

export default function Welcome({
    laravelVersion = "11.x",
    phpVersion = "8.2",
}) {
    // State
    const [counter, setCounter] = useState(0);
    const [isDark, setIsDark] = useState(false);
    const [loading, setLoading] = useState({
        health: false,
        public: false,
    });
    const [responses, setResponses] = useState({
        health: null,
        public: null,
    });
    const routesSection = useRef(null);

    // Computed values
    const vueVersion = "3.4.x";
    const reactVersion = "19.x";

    // Store usage example
    const counterStore = useCounterStore();

    // API Routes data
    const publicRoutes = [
        {
            method: "GET",
            path: "/api/health",
            description: "Health check endpoint for monitoring",
            rateLimit: "No limit",
        },
        {
            method: "POST",
            path: "/api/v1/auth/login",
            description: "User authentication login",
            rateLimit: "5/min",
        },
        {
            method: "POST",
            path: "/api/v1/auth/signup",
            description: "User registration",
            rateLimit: "3/min",
        },
        {
            method: "GET",
            path: "/api/v1/public/info",
            description: "Public information endpoint",
            rateLimit: "60/min",
        },
        {
            method: "GET",
            path: "/api/v1/search",
            description: "Public search functionality",
            rateLimit: "30/min",
        },
    ];

    const protectedRoutes = [
        {
            method: "GET",
            path: "/api/v1/auth/me",
            description: "Get current user information",
            rateLimit: "60/min",
        },
        {
            method: "POST",
            path: "/api/v1/auth/logout",
            description: "User logout",
            rateLimit: "10/min",
        },
        {
            method: "GET",
            path: "/api/v1/profile",
            description: "User profile data",
            rateLimit: "60/min",
        },
        {
            method: "GET",
            path: "/api/v1/search/advanced",
            description: "Advanced search for authenticated users",
            rateLimit: "30/min",
        },
        {
            method: "POST",
            path: "/api/v1/upload",
            description: "File upload endpoint",
            rateLimit: "10/min",
        },
        {
            method: "GET",
            path: "/api/v1/admin/dashboard",
            description: "Admin dashboard data",
            rateLimit: "30/min",
        },
    ];

    // Methods
    const incrementCounter = () => {
        setCounter((prev) => prev + 1);
        counterStore.increment();
    };

    const toggleTheme = () => {
        setIsDark((prev) => !prev);
        // In a real app, you'd persist this to localStorage and apply theme classes
    };

    const getMethodColor = (method) => {
        const colors = {
            GET: "text-green-600",
            POST: "text-blue-600",
            PUT: "text-yellow-600",
            DELETE: "text-red-600",
            PATCH: "text-purple-600",
        };
        return colors[method] || "text-gray-600";
    };

    const testHealthEndpoint = async () => {
        setLoading((prev) => ({ ...prev, health: true }));
        try {
            const response = await fetch("/api/health");
            const data = await response.json();
            setResponses((prev) => ({ ...prev, health: data }));
        } catch (error) {
            setResponses((prev) => ({
                ...prev,
                health: {
                    error: "Failed to fetch health data",
                    message: error.message,
                },
            }));
        } finally {
            setLoading((prev) => ({ ...prev, health: false }));
        }
    };

    const testPublicEndpoint = async () => {
        setLoading((prev) => ({ ...prev, public: true }));
        try {
            const response = await fetch("/api/v1/public/info");
            const data = await response.json();
            setResponses((prev) => ({ ...prev, public: data }));
        } catch (error) {
            setResponses((prev) => ({
                ...prev,
                public: {
                    error: "Failed to fetch public data",
                    message: error.message,
                },
            }));
        } finally {
            setLoading((prev) => ({ ...prev, public: false }));
        }
    };

    return (
        <>
            <Head title="Laravel API Boilerplate" />

            <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50">
                {/* Navigation */}
                <nav className="bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between items-center h-16">
                            <div className="flex items-center space-x-3">
                                <div className="w-8 h-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                    <span className="text-white font-bold text-sm">
                                        L
                                    </span>
                                </div>
                                <span className="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                    Laravel API Boilerplate
                                </span>
                            </div>
                            <div className="flex items-center space-x-4">
                                <button
                                    onClick={toggleTheme}
                                    className="p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200"
                                >
                                    <span className="text-xl">
                                        {isDark ? "☀️" : "🌙"}
                                    </span>
                                </button>
                                <a
                                    href="https://github.com/alaa-nabawy/laravel-solid-api"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-gray-600 hover:text-gray-900 transition-colors duration-200"
                                >
                                    <svg
                                        className="w-6 h-6"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </nav>

                {/* Hero Section */}
                <section className="relative overflow-hidden py-20 sm:py-32">
                    <div className="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-purple-500/10"></div>
                    <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center">
                            <div className="inline-flex items-center px-4 py-2 rounded-full bg-indigo-100 text-indigo-800 text-sm font-medium mb-8">
                                <span className="animate-pulse mr-2">🚀</span>
                                Production-Ready Laravel API Boilerplate
                            </div>
                            <h1 className="text-5xl sm:text-7xl font-bold bg-gradient-to-r from-indigo-600 via-purple-600 to-cyan-600 bg-clip-text text-transparent mb-6">
                                Modern API
                                <br />
                                <span className="text-4xl sm:text-6xl">
                                    Development
                                </span>
                            </h1>
                            <p className="text-xl text-gray-600 mb-12 max-w-3xl mx-auto leading-relaxed">
                                A comprehensive Laravel boilerplate with React,
                                Inertia.js, authentication, rate limiting, and
                                modern development tools. Built with SOLID
                                principles and best practices.
                            </p>
                            <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
                                <a
                                    href="/docs/api"
                                    className="group px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold hover:from-indigo-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl inline-flex"
                                >
                                    <span className="flex items-center">
                                        Explore API Routes
                                        <svg
                                            className="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M13 7l5 5m0 0l-5 5m5-5H6"
                                            ></path>
                                        </svg>
                                    </span>
                                </a>
                                <button
                                    onClick={incrementCounter}
                                    className="px-8 py-4 border-2 border-indigo-600 text-indigo-600 rounded-xl font-semibold hover:bg-indigo-600 hover:text-white transition-all duration-200"
                                >
                                    Interactive Demo ({counter})
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Features Section */}
                <section className="py-20 bg-white">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                                Built for Modern Development
                            </h2>
                            <p className="text-xl text-gray-600 max-w-2xl mx-auto">
                                Everything you need to build scalable APIs with
                                confidence
                            </p>
                        </div>
                        <div className="grid md:grid-cols-3 gap-8">
                            <div className="group p-8 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 transition-all duration-300 transform hover:-translate-y-2">
                                <div className="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <span className="text-2xl">⚡</span>
                                </div>
                                <h3 className="text-xl font-bold text-gray-900 mb-3">
                                    Lightning Fast
                                </h3>
                                <p className="text-gray-600 leading-relaxed">
                                    Vite-powered development with hot module
                                    replacement, optimized builds, and instant
                                    feedback loops.
                                </p>
                            </div>
                            <div className="group p-8 rounded-2xl bg-gradient-to-br from-purple-50 to-pink-50 hover:from-purple-100 hover:to-pink-100 transition-all duration-300 transform hover:-translate-y-2">
                                <div className="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <span className="text-2xl">🛡️</span>
                                </div>
                                <h3 className="text-xl font-bold text-gray-900 mb-3">
                                    Secure by Default
                                </h3>
                                <p className="text-gray-600 leading-relaxed">
                                    Built-in authentication, rate limiting, CORS
                                    protection, and security best practices out
                                    of the box.
                                </p>
                            </div>
                            <div className="group p-8 rounded-2xl bg-gradient-to-br from-green-50 to-emerald-50 hover:from-green-100 hover:to-emerald-100 transition-all duration-300 transform hover:-translate-y-2">
                                <div className="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <span className="text-2xl">🔧</span>
                                </div>
                                <h3 className="text-xl font-bold text-gray-900 mb-3">
                                    Developer Experience
                                </h3>
                                <p className="text-gray-600 leading-relaxed">
                                    Code quality tools, testing setup, Docker
                                    support, and comprehensive documentation.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Tech Stack Section */}
                <section className="py-20 bg-gray-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                                Powered by Modern Technologies
                            </h2>
                            <p className="text-xl text-gray-600">
                                Industry-leading tools and frameworks
                            </p>
                        </div>
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div className="group p-6 bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                                <div className="text-center">
                                    <div className="w-16 h-16 bg-gradient-to-r from-red-500 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                                        <span className="text-white font-bold text-xl">
                                            L
                                        </span>
                                    </div>
                                    <div className="font-bold text-red-600 text-lg">
                                        Laravel {laravelVersion}
                                    </div>
                                    <div className="text-sm text-gray-600 mt-1">
                                        Backend Framework
                                    </div>
                                </div>
                            </div>
                            <div className="group p-6 bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                                <div className="text-center">
                                    <div className="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                                        <span className="text-white font-bold text-xl">
                                            R
                                        </span>
                                    </div>
                                    <div className="font-bold text-blue-600 text-lg">
                                        React {reactVersion}
                                    </div>
                                    <div className="text-sm text-gray-600 mt-1">
                                        Frontend Framework
                                    </div>
                                </div>
                            </div>
                            <div className="group p-6 bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                                <div className="text-center">
                                    <div className="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                                        <span className="text-white font-bold text-xl">
                                            I
                                        </span>
                                    </div>
                                    <div className="font-bold text-purple-600 text-lg">
                                        Inertia.js
                                    </div>
                                    <div className="text-sm text-gray-600 mt-1">
                                        SPA Framework
                                    </div>
                                </div>
                            </div>
                            <div className="group p-6 bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                                <div className="text-center">
                                    <div className="w-16 h-16 bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                                        <span className="text-white font-bold text-xl">
                                            T
                                        </span>
                                    </div>
                                    <div className="font-bold text-cyan-600 text-lg">
                                        Tailwind CSS
                                    </div>
                                    <div className="text-sm text-gray-600 mt-1">
                                        Styling Framework
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* API Endpoints Section */}
                <section ref={routesSection} className="py-20 bg-white">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                                API Endpoints
                            </h2>
                            <p className="text-xl text-gray-600">
                                Explore the available API routes and test them
                                directly
                            </p>
                        </div>

                        {/* Public Routes */}
                        <div className="mb-12">
                            <h3 className="text-2xl font-bold text-gray-900 mb-6">
                                Public Routes
                            </h3>
                            <div className="space-y-4">
                                {publicRoutes.map((route, index) => (
                                    <div
                                        key={index}
                                        className="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors duration-200"
                                    >
                                        <div className="flex flex-col md:flex-row md:items-center md:justify-between">
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-3 mb-2">
                                                    <span
                                                        className={`px-3 py-1 rounded-full text-xs font-bold ${getMethodColor(route.method)} bg-white`}
                                                    >
                                                        {route.method}
                                                    </span>
                                                    <code className="text-sm font-mono text-gray-800">
                                                        {route.path}
                                                    </code>
                                                </div>
                                                <p className="text-gray-600 text-sm">
                                                    {route.description}
                                                </p>
                                                <p className="text-gray-500 text-xs mt-1">
                                                    Rate limit:{" "}
                                                    {route.rateLimit}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Protected Routes */}
                        <div className="mb-12">
                            <h3 className="text-2xl font-bold text-gray-900 mb-6">
                                Protected Routes
                            </h3>
                            <div className="space-y-4">
                                {protectedRoutes.map((route, index) => (
                                    <div
                                        key={index}
                                        className="bg-yellow-50 rounded-lg p-6 hover:bg-yellow-100 transition-colors duration-200"
                                    >
                                        <div className="flex flex-col md:flex-row md:items-center md:justify-between">
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-3 mb-2">
                                                    <span
                                                        className={`px-3 py-1 rounded-full text-xs font-bold ${getMethodColor(route.method)} bg-white`}
                                                    >
                                                        {route.method}
                                                    </span>
                                                    <code className="text-sm font-mono text-gray-800">
                                                        {route.path}
                                                    </code>
                                                    <span className="px-2 py-1 bg-yellow-200 text-yellow-800 text-xs rounded-full">
                                                        🔒 Auth Required
                                                    </span>
                                                </div>
                                                <p className="text-gray-600 text-sm">
                                                    {route.description}
                                                </p>
                                                <p className="text-gray-500 text-xs mt-1">
                                                    Rate limit:{" "}
                                                    {route.rateLimit}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* API Testing Section */}
                        <div className="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl p-8">
                            <h3 className="text-2xl font-bold text-gray-900 mb-6 text-center">
                                Test API Endpoints
                            </h3>
                            <div className="grid md:grid-cols-2 gap-6">
                                {/* Health Check Test */}
                                <div className="bg-white rounded-lg p-6 shadow-sm">
                                    <h4 className="font-bold text-lg mb-4">
                                        Health Check
                                    </h4>
                                    <button
                                        onClick={testHealthEndpoint}
                                        disabled={loading.health}
                                        className="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                                    >
                                        {loading.health
                                            ? "Testing..."
                                            : "Test /api/health"}
                                    </button>
                                    {responses.health && (
                                        <div className="mt-4 p-3 bg-gray-100 rounded-lg">
                                            <pre className="text-xs overflow-x-auto">
                                                {JSON.stringify(
                                                    responses.health,
                                                    null,
                                                    2,
                                                )}
                                            </pre>
                                        </div>
                                    )}
                                </div>

                                {/* Public Info Test */}
                                <div className="bg-white rounded-lg p-6 shadow-sm">
                                    <h4 className="font-bold text-lg mb-4">
                                        Public Info
                                    </h4>
                                    <button
                                        onClick={testPublicEndpoint}
                                        disabled={loading.public}
                                        className="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                                    >
                                        {loading.public
                                            ? "Testing..."
                                            : "Test /api/v1/public/info"}
                                    </button>
                                    {responses.public && (
                                        <div className="mt-4 p-3 bg-gray-100 rounded-lg">
                                            <pre className="text-xs overflow-x-auto">
                                                {JSON.stringify(
                                                    responses.public,
                                                    null,
                                                    2,
                                                )}
                                            </pre>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="bg-gray-900 text-white py-12">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="grid md:grid-cols-4 gap-8">
                            <div className="md:col-span-2">
                                <div className="flex items-center space-x-3 mb-4">
                                    <div className="w-8 h-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                        <span className="text-white font-bold text-sm">
                                            L
                                        </span>
                                    </div>
                                    <span className="text-xl font-bold">
                                        Laravel API Boilerplate
                                    </span>
                                </div>
                                <p className="text-gray-400 mb-4">
                                    A production-ready Laravel API boilerplate
                                    with modern frontend integration.
                                </p>
                                <div className="flex space-x-4">
                                    <a
                                        href="https://github.com/alaa-nabawy/laravel-solid-api"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-gray-400 hover:text-white transition-colors duration-200"
                                    >
                                        Documentation
                                    </a>
                                    <a
                                        href="https://github.com/alaa-nabawy/laravel-solid-api"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-gray-400 hover:text-white transition-colors duration-200"
                                    >
                                        GitHub
                                    </a>
                                    <a
                                        href="https://github.com/alaa-nabawy/laravel-solid-api/issues"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-gray-400 hover:text-white transition-colors duration-200"
                                    >
                                        Issues
                                    </a>
                                </div>
                            </div>
                            <div>
                                <h3 className="font-bold mb-4">Framework</h3>
                                <ul className="space-y-2 text-gray-400">
                                    <li>Laravel {laravelVersion}</li>
                                    <li>React {reactVersion}</li>
                                    <li>Inertia.js</li>
                                    <li>Tailwind CSS</li>
                                </ul>
                            </div>
                            <div>
                                <h3 className="font-bold mb-4">Features</h3>
                                <ul className="space-y-2 text-gray-400">
                                    <li>Authentication</li>
                                    <li>Rate Limiting</li>
                                    <li>API Documentation</li>
                                    <li>Testing Suite</li>
                                </ul>
                            </div>
                        </div>
                        <div className="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                            <p>
                                © {new Date().getFullYear()} Laravel API
                                Boilerplate. Built with Laravel & React by Alaa
                                Nabawy.
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
