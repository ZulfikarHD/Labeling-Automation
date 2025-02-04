<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (value) => [
            'default', 'primary', 'success',
            'warning', 'danger', 'info',
            'sky', 'fuchsia', 'yellow', 'amber'
        ].includes(value)
    },
    animate: {
        type: Boolean,
        default: false
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    rounded: {
        type: String,
        default: 'full',
        validator: (value) => ['none', 'sm', 'md', 'lg', 'full'].includes(value)
    },
    contrast: {
        type: String,
        default: 'normal',
        validator: (value) => ['low', 'normal', 'high'].includes(value)
    },
    bordered: {
        type: Boolean,
        default: true
    },
    gradient: {
        type: Boolean,
        default: true
    }
});

const badgeConfig = computed(() => ({
    default: {
        colors: {
            low: {
                bg: 'from-slate-50 to-slate-100 dark:from-slate-800/40 dark:to-slate-800/60',
                text: 'text-slate-600 dark:text-slate-300',
                border: 'border-slate-200/60 dark:border-slate-700/60',
                dot: 'bg-slate-400'
            },
            normal: {
                bg: 'from-slate-100 to-slate-200 dark:from-slate-900/40 dark:to-slate-900/60',
                text: 'text-slate-700 dark:text-slate-200',
                border: 'border-slate-300/60 dark:border-slate-600/60',
                dot: 'bg-slate-500'
            },
            high: {
                bg: 'from-slate-200 to-slate-300 dark:from-slate-950/40 dark:to-slate-950/60',
                text: 'text-slate-800 dark:text-slate-100',
                border: 'border-slate-400/60 dark:border-slate-500/60',
                dot: 'bg-slate-600'
            }
        }
    },
    primary: {
        colors: {
            low: {
                bg: 'from-blue-50 to-blue-100 dark:from-blue-800/40 dark:to-blue-800/60',
                text: 'text-blue-600 dark:text-blue-300',
                border: 'border-blue-200/60 dark:border-blue-700/60',
                dot: 'bg-blue-400'
            },
            normal: {
                bg: 'from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-900/60',
                text: 'text-blue-700 dark:text-blue-200',
                border: 'border-blue-300/60 dark:border-blue-600/60',
                dot: 'bg-blue-500'
            },
            high: {
                bg: 'from-blue-200 to-blue-300 dark:from-blue-950/40 dark:to-blue-950/60',
                text: 'text-blue-800 dark:text-blue-100',
                border: 'border-blue-400/60 dark:border-blue-500/60',
                dot: 'bg-blue-600'
            }
        }
    },
    success: {
        colors: {
            low: {
                bg: 'from-emerald-50 to-emerald-100 dark:from-emerald-800/40 dark:to-emerald-800/60',
                text: 'text-emerald-600 dark:text-emerald-300',
                border: 'border-emerald-200/60 dark:border-emerald-700/60',
                dot: 'bg-emerald-400'
            },
            normal: {
                bg: 'from-emerald-100 to-emerald-200 dark:from-emerald-900/40 dark:to-emerald-900/60',
                text: 'text-emerald-700 dark:text-emerald-200',
                border: 'border-emerald-300/60 dark:border-emerald-600/60',
                dot: 'bg-emerald-500'
            },
            high: {
                bg: 'from-emerald-200 to-emerald-300 dark:from-emerald-950/40 dark:to-emerald-950/60',
                text: 'text-emerald-800 dark:text-emerald-100',
                border: 'border-emerald-400/60 dark:border-emerald-500/60',
                dot: 'bg-emerald-600'
            }
        }
    },
    warning: {
        colors: {
            low: {
                bg: 'from-amber-50 to-amber-100 dark:from-amber-800/40 dark:to-amber-800/60',
                text: 'text-amber-600 dark:text-amber-300',
                border: 'border-amber-200/60 dark:border-amber-700/60',
                dot: 'bg-amber-400'
            },
            normal: {
                bg: 'from-amber-100 to-amber-200 dark:from-amber-900/40 dark:to-amber-900/60',
                text: 'text-amber-700 dark:text-amber-200',
                border: 'border-amber-300/60 dark:border-amber-600/60',
                dot: 'bg-amber-500'
            },
            high: {
                bg: 'from-amber-200 to-amber-300 dark:from-amber-950/40 dark:to-amber-950/60',
                text: 'text-amber-800 dark:text-amber-100',
                border: 'border-amber-400/60 dark:border-amber-500/60',
                dot: 'bg-amber-600'
            }
        }
    },
    danger: {
        colors: {
            low: {
                bg: 'from-red-50 to-red-100 dark:from-red-800/40 dark:to-red-800/60',
                text: 'text-red-600 dark:text-red-300',
                border: 'border-red-200/60 dark:border-red-700/60',
                dot: 'bg-red-400'
            },
            normal: {
                bg: 'from-red-100 to-red-200 dark:from-red-900/40 dark:to-red-900/60',
                text: 'text-red-700 dark:text-red-200',
                border: 'border-red-300/60 dark:border-red-600/60',
                dot: 'bg-red-500'
            },
            high: {
                bg: 'from-red-200 to-red-300 dark:from-red-950/40 dark:to-red-950/60',
                text: 'text-red-800 dark:text-red-100',
                border: 'border-red-400/60 dark:border-red-500/60',
                dot: 'bg-red-600'
            }
        }
    },
    info: {
        colors: {
            low: {
                bg: 'from-cyan-50 to-cyan-100 dark:from-cyan-800/40 dark:to-cyan-800/60',
                text: 'text-cyan-600 dark:text-cyan-300',
                border: 'border-cyan-200/60 dark:border-cyan-700/60',
                dot: 'bg-cyan-400'
            },
            normal: {
                bg: 'from-cyan-100 to-cyan-200 dark:from-cyan-900/40 dark:to-cyan-900/60',
                text: 'text-cyan-700 dark:text-cyan-200',
                border: 'border-cyan-300/60 dark:border-cyan-600/60',
                dot: 'bg-cyan-500'
            },
            high: {
                bg: 'from-cyan-200 to-cyan-300 dark:from-cyan-950/40 dark:to-cyan-950/60',
                text: 'text-cyan-800 dark:text-cyan-100',
                border: 'border-cyan-400/60 dark:border-cyan-500/60',
                dot: 'bg-cyan-600'
            }
        }
    },
    sky: {
        colors: {
            low: {
                bg: 'from-sky-50 to-sky-100 dark:from-sky-800/40 dark:to-sky-800/60',
                text: 'text-sky-600 dark:text-sky-300',
                border: 'border-sky-200/60 dark:border-sky-700/60',
                dot: 'bg-sky-400'
            },
            normal: {
                bg: 'from-sky-100 to-sky-200 dark:from-sky-900/40 dark:to-sky-900/60',
                text: 'text-sky-700 dark:text-sky-200',
                border: 'border-sky-300/60 dark:border-sky-600/60',
                dot: 'bg-sky-500'
            },
            high: {
                bg: 'from-sky-200 to-sky-300 dark:from-sky-950/40 dark:to-sky-950/60',
                text: 'text-sky-800 dark:text-sky-100',
                border: 'border-sky-400/60 dark:border-sky-500/60',
                dot: 'bg-sky-600'
            }
        }
    },
    fuchsia: {
        colors: {
            low: {
                bg: 'from-fuchsia-50 to-fuchsia-100 dark:from-fuchsia-800/40 dark:to-fuchsia-800/60',
                text: 'text-fuchsia-600 dark:text-fuchsia-300',
                border: 'border-fuchsia-200/60 dark:border-fuchsia-700/60',
                dot: 'bg-fuchsia-400'
            },
            normal: {
                bg: 'from-fuchsia-100 to-fuchsia-200 dark:from-fuchsia-900/40 dark:to-fuchsia-900/60',
                text: 'text-fuchsia-700 dark:text-fuchsia-200',
                border: 'border-fuchsia-300/60 dark:border-fuchsia-600/60',
                dot: 'bg-fuchsia-500'
            },
            high: {
                bg: 'from-fuchsia-200 to-fuchsia-300 dark:from-fuchsia-950/40 dark:to-fuchsia-950/60',
                text: 'text-fuchsia-800 dark:text-fuchsia-100',
                border: 'border-fuchsia-400/60 dark:border-fuchsia-500/60',
                dot: 'bg-fuchsia-600'
            }
        }
    }
}));

const sizeClasses = computed(() => ({
    xs: 'px-1.5 py-0.5 text-xs',
    sm: 'px-2 py-1 text-xs',
    md: 'px-3 py-1.5 text-sm',
    lg: 'px-4 py-2 text-base',
    xl: 'px-6 py-3 text-lg'
}));

const roundedClasses = computed(() => ({
    none: 'rounded-none',
    sm: 'rounded',
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full'
}));

const shadowClasses = computed(() =>
    props.contrast === 'high' ? 'shadow-md' : 'shadow-sm'
);

const gradientClass = computed(() =>
    props.gradient ? 'bg-gradient-to-r' : ''
);

const borderClass = computed(() =>
    props.bordered ? 'border' : ''
);
</script>

<template>
    <span
        class="inline-flex items-center gap-2
               font-medium tracking-wide
               transition-all duration-200"
        :class="[
            badgeConfig[variant].colors[contrast].bg,
            badgeConfig[variant].colors[contrast].text,
            props.bordered && badgeConfig[variant].colors[contrast].border,
            roundedClasses[rounded],
            shadowClasses,
            gradientClass,
            borderClass,
            sizeClasses[size]
        ]"
    >
        <span v-if="animate" class="relative flex h-2 w-2">
            <span
                class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                :class="badgeConfig[variant].colors[contrast].dot"
            />
            <span
                class="relative inline-flex rounded-full h-2 w-2"
                :class="badgeConfig[variant].colors[contrast].dot"
            />
        </span>
        <slot />
    </span>
</template>
