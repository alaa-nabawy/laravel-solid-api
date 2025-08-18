<template>
    <Link
        v-if="as === 'link'"
        :href="href"
        :method="method"
        :as="as"
        class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
    >
        <slot />
    </Link>
    <button
        v-else
        :type="as"
        class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
        @click="handleClick"
    >
        <slot />
    </button>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    href: {
        type: String,
        default: null,
    },
    method: {
        type: String,
        default: "get",
    },
    as: {
        type: String,
        default: "link",
    },
});

const handleClick = () => {
    if (props.href && props.method !== "get") {
        router.visit(props.href, {
            method: props.method,
        });
    }
};
</script>
