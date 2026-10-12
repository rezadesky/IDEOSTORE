<script setup>
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';

const props = defineProps({
    className: {
        type: String,
        default: ''
    },
    containerRef: Object,
    fromRef: Object,
    toRef: Object,
    curvature: {
        type: Number,
        default: 0
    },
    reverse: {
        type: Boolean,
        default: false
    },
    duration: {
        type: Number,
        default: 5
    },
    delay: {
        type: Number,
        default: 0
    },
    pathColor: {
        type: String,
        default: 'gray'
    },
    pathWidth: {
        type: Number,
        default: 2
    },
    pathOpacity: {
        type: Number,
        default: 0.2
    },
    gradientStartColor: {
        type: String,
        default: '#ffaa40'
    },
    gradientStopColor: {
        type: String,
        default: '#9c40ff'
    },
    startXOffset: {
        type: Number,
        default: 0
    },
    startYOffset: {
        type: Number,
        default: 0
    },
    endXOffset: {
        type: Number,
        default: 0
    },
    endYOffset: {
        type: Number,
        default: 0
    }
});

const id = `beam-${Math.random().toString(36).substring(2, 9)}`;
const pathD = ref('');
const svgDimensions = ref({ width: 0, height: 0 });

const getEl = (val) => {
    if (!val) return null;
    return val.$el || val;
};

const updatePath = () => {
    const container = getEl(props.containerRef?.value);
    const fromEl = getEl(props.fromRef?.value);
    const toEl = getEl(props.toRef?.value);

    if (container && fromEl && toEl) {
        const containerRect = container.getBoundingClientRect();
        const rectA = fromEl.getBoundingClientRect();
        const rectB = toEl.getBoundingClientRect();

        const svgWidth = containerRect.width;
        const svgHeight = containerRect.height;
        svgDimensions.value = { width: svgWidth, height: svgHeight };

        const startX = rectA.left - containerRect.left + rectA.width / 2 + props.startXOffset;
        const startY = rectA.top - containerRect.top + rectA.height / 2 + props.startYOffset;
        const endX = rectB.left - containerRect.left + rectB.width / 2 + props.endXOffset;
        const endY = rectB.top - containerRect.top + rectB.height / 2 + props.endYOffset;

        const controlY = startY - props.curvature;
        const d = `M ${startX},${startY} Q ${(startX + endX) / 2},${controlY} ${endX},${endY}`;
        pathD.value = d;
    }
};

let resizeObserver;

onMounted(() => {
    nextTick(() => {
        updatePath();
        setTimeout(updatePath, 150);
        setTimeout(updatePath, 400);

        const container = getEl(props.containerRef?.value);
        if (container) {
            resizeObserver = new ResizeObserver(() => updatePath());
            resizeObserver.observe(container);
        }
        window.addEventListener('resize', updatePath);
    });
});

onBeforeUnmount(() => {
    if (resizeObserver) resizeObserver.disconnect();
    window.removeEventListener('resize', updatePath);
});
</script>

<template>
    <svg
        fill="none"
        :width="svgDimensions.width"
        :height="svgDimensions.height"
        xmlns="http://www.w3.org/2000/svg"
        :class="['pointer-events-none absolute top-0 left-0 transform-gpu stroke-2', className]"
        :viewBox="`0 0 ${svgDimensions.width} ${svgDimensions.height}`"
    >
        <!-- Background static track path -->
        <path
            :d="pathD"
            :stroke="pathColor"
            :stroke-width="pathWidth"
            :stroke-opacity="pathOpacity"
            stroke-linecap="round"
        />

        <!-- Beam of light which travels along the path -->
        <path
            :d="pathD"
            :stroke-width="pathWidth"
            :stroke="`url(#${id})`"
            stroke-opacity="1"
            stroke-linecap="round"
        />

        <defs>
            <linearGradient
                :id="id"
                gradientUnits="userSpaceOnUse"
            >
                <!-- Animate x1, x2 persis seperti motion.linearGradient pada spesifikasi Magic UI -->
                <animate
                    attributeName="x1"
                    :values="reverse ? '90%; -10%' : '10%; 110%'"
                    :dur="`${duration}s`"
                    :begin="`${delay}s`"
                    keyTimes="0; 1"
                    keySplines="0.16 1 0.3 1"
                    calcMode="spline"
                    repeatCount="indefinite"
                />
                <animate
                    attributeName="x2"
                    :values="reverse ? '100%; 0%' : '0%; 100%'"
                    :dur="`${duration}s`"
                    :begin="`${delay}s`"
                    keyTimes="0; 1"
                    keySplines="0.16 1 0.3 1"
                    calcMode="spline"
                    repeatCount="indefinite"
                />
                <animate
                    attributeName="y1"
                    values="0%; 0%"
                    :dur="`${duration}s`"
                    repeatCount="indefinite"
                />
                <animate
                    attributeName="y2"
                    values="0%; 0%"
                    :dur="`${duration}s`"
                    repeatCount="indefinite"
                />

                <stop :stop-color="gradientStartColor" stop-opacity="0" />
                <stop :stop-color="gradientStartColor" />
                <stop offset="32.5%" :stop-color="gradientStopColor" />
                <stop offset="100%" :stop-color="gradientStopColor" stop-opacity="0" />
            </linearGradient>
        </defs>
    </svg>
</template>
