<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <Link
                                href="/"
                                class="text-xl font-bold text-gray-800"
                            >
                                {{ appName }}
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div
                            class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"
                        >
                            <NavLink href="/" :active="$page.url === '/'">
                                Dashboard
                            </NavLink>
                            <NavLink
                                href="/about"
                                :active="$page.url === '/about'"
                            >
                                About
                            </NavLink>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div v-if="$page.props.auth.user" class="ml-3 relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out"
                                    >
                                        <div>
                                            {{ $page.props.auth.user.name }}
                                        </div>
                                        <ChevronDownIcon class="ml-1 h-4 w-4" />
                                    </button>
                                </template>

                                <template #content>
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
                                </template>
                            </Dropdown>
                        </div>
                        <div v-else class="space-x-4">
                            <Link
                                href="/login"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Login
                            </Link>
                            <Link
                                href="/register"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Register
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Flash Messages -->
        <div
            v-if="$page.props.flash.success"
            class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mx-4 mt-4"
        >
            {{ $page.props.flash.success }}
        </div>
        <div
            v-if="$page.props.flash.error"
            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mx-4 mt-4"
        >
            {{ $page.props.flash.error }}
        </div>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="text-center text-gray-500 text-sm">
                    © {{ new Date().getFullYear() }} {{ appName }}. Built with
                    Laravel & Vue.js.
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { ChevronDownIcon } from "@heroicons/vue/24/outline";
import NavLink from "@/Components/NavLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

defineProps({
    appName: {
        type: String,
        default: "Laravel",
    },
});
</script>
