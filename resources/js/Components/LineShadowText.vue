<script setup>
import { computed } from 'vue';

const props = defineProps({
    text: {
        type: String,
        default: ''
    },
    shadowColor: {
        type: String,
        default: '#2554ed'
    },
    as: {
        type: String,
        default: 'span'
    },
    className: {
        type: String,
        default: ''
    }
});

const slots = defineSlots();

const displayText = computed(() => {
    if (props.text) return props.text;
    const defaultSlot = slots.default ? slots.default() : [];
    if (defaultSlot.length > 0 && typeof defaultSlot[0].children === 'string') {
        return defaultSlot[0].children.trim();
    }
    return '';
});
</script>

<template>
    <component
        :is="as"
        :style="{ '--shadow-color': shadowColor }"
        :data-text="displayText"
        :class="[
            'relative z-0 inline-flex font-extrabold tracking-tight',
            'after:absolute after:left-[0.04em] after:top-[0.04em] after:content-[attr(data-text)]',
            'after:z-[-1] after:bg-[linear-gradient(45deg,transparent_45%,var(--shadow-color)_45%,var(--shadow-color)_55%,transparent_55%)]',
            'after:bg-[length:0.06em_0.06em] after:bg-clip-text after:text-transparent',
            'after:animate-line-shadow',
            'after:whitespace-nowrap',
            className
        ]"
    >
        <template v-if="text">{{ text }}</template>
        <slot v-else />
    </component>
</template>
