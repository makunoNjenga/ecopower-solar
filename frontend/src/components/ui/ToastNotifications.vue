<template>
    <div class="fixed top-24 md:top-20 right-4 z-[100] space-y-4 max-w-md">
        <TransitionGroup name="toast" tag="div">
            <div
                v-for="notification in appStore.notifications"
                :key="notification.id"
                :class="[
                    'w-full bg-white shadow-2xl rounded-lg pointer-events-auto border-l-4',
                    toastClasses[notification.type],
                ]"
            >
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <component
                                :is="getIcon(notification.type)"
                                :class="[
                                    'h-6 w-6',
                                    iconClasses[notification.type],
                                ]"
                            />
                        </div>
                        <div class="ml-3 flex-1 pt-0.5">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ notification.title }}
                            </p>
                            <p class="mt-1 text-sm text-gray-600 break-words">
                                {{ notification.message }}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button
                                @click="
                                    appStore.removeNotification(notification.id)
                                "
                                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-600 focus:outline-none"
                            >
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
import { useAppStore } from "@/stores/app";
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XCircleIcon,
    InformationCircleIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";

const appStore = useAppStore();

const toastClasses = {
    success: "border-green-500 bg-green-50",
    error: "border-red-500 bg-red-50",
    warning: "border-yellow-500 bg-yellow-50",
    info: "border-blue-500 bg-blue-50",
};

const iconClasses = {
    success: "text-green-600",
    error: "text-red-600",
    warning: "text-yellow-600",
    info: "text-blue-600",
};

const getIcon = (type) => {
    const icons = {
        success: CheckCircleIcon,
        error: XCircleIcon,
        warning: ExclamationTriangleIcon,
        info: InformationCircleIcon,
    };
    return icons[type] || InformationCircleIcon;
};
</script>

<style scoped>
.toast-enter-active {
    transition: all 0.3s ease-out;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-active {
    transition: all 0.3s ease-in;
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>
