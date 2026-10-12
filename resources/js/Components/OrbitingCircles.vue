<script setup>
import { computed, useSlots } from 'vue';

const props = defineProps({
    className: {
        type: String,
        default: ''
    },
    reverse: {
        type: Boolean,
        default: false
    },
    duration: {
        type: Number,
        default: 20
    },
    delay: {
        type: Number,
        default: 10
    },
    radius: {
        type: Number,
        default: 160
    },
    path: {
        type: Boolean,
        default: true
    },
    iconSize: {
        type: Number,
        default: 30
    },
    speed: {
        type: Number,
        default: 1
    }
});

const slots = useSlots();

const calculatedDuration = computed(() => props.duration / props.speed);

// Ekstrak slot children untuk menghitung sudut rotasi tiap icon secara dinamis
const childrenCount = computed(() => {
    const defaultSlot = slots.default ? slots.default() : [];
    return defaultSlot.length || 1;
});

const getAngle = (index) => {
    return (360 / childrenCount.value) * index;
};
</script>

<template>
    <!-- SVG Circular Orbit Path -->
    <svg
        v-if="path"
        xmlns="http://www.w3.org/2000/svg"
        version="1.1"
        class="pointer-events-none absolute inset-0 size-full"
    >
        <circle
            class="stroke-slate-900/10 stroke-1"
            cx="50%"
            cy="50%"
            :r="radius"
            fill="none"
        />
    </svg>

    <!-- Orbiting Items Container -->
    <div
        v-for="(child, index) in (slots.default ? slots.default() : [])"
        :key="index"
        :style="{
            '--duration': calculatedDuration,
            '--radius': radius,
            '--angle': getAngle(index),
            '--icon-size': `${iconSize}px`
        }"
        :class="[
            'animate-orbit absolute flex size-[var(--icon-size)] transform-gpu items-center justify-center rounded-full',
            reverse ? '[animation-direction:reverse]' : '',
            className
        ]"
    >
        <component :is="child" />
    </div>
</template>
