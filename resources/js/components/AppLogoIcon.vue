<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 42"
        :class="className"
        v-bind="$attrs"
    >
        <!-- User silhouette -->
        <path
            fill="currentColor"
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M20 4C17.7909 4 16 5.79086 16 8C16 10.2091 17.7909 12 20 12C22.2091 12 24 10.2091 24 8C24 5.79086 22.2091 4 20 4ZM20 14C14.4772 14 10 17.5817 10 22V24H30V22C30 17.5817 25.5228 14 20 14Z"
        />
        <!-- Checkmark badge -->
        <circle cx="28" cy="32" r="8" fill="#22c55e" />
        <path
            fill="white"
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M25.5 30.5L23 33L28 38L30.5 35.5L28 33L25.5 30.5Z"
        />
    </svg>
</template>
