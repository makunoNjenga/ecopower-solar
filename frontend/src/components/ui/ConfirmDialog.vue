<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        required: true,
    },
    title: {
        type: String,
        default: "Confirm Action",
    },
    message: {
        type: String,
        required: true,
    },
    confirmText: {
        type: String,
        default: "Confirm",
    },
    cancelText: {
        type: String,
        default: "Cancel",
    },
    type: {
        type: String,
        default: "danger", // danger, warning, info
        validator: (value) => ["danger", "warning", "info"].includes(value),
    },
});

const emit = defineEmits(["confirm", "cancel", "update:show"]);

const isOpen = ref(props.show);

watch(
    () => props.show,
    (newVal) => {
        isOpen.value = newVal;
    }
);

/**
 * Handle confirm action
 */
const handleConfirm = () => {
    emit("confirm");
    close();
};

/**
 * Handle cancel action
 */
const handleCancel = () => {
    emit("cancel");
    close();
};

/**
 * Close dialog
 */
const close = () => {
    isOpen.value = false;
    emit("update:show", false);
};
</script>

<template>
    <Transition name="modal">
        <div
            v-if="isOpen"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <!-- Background overlay -->
            <div
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                @click="handleCancel"
            ></div>

            <!-- Modal content -->
            <div
                class="flex min-h-full items-center justify-center p-4 text-center sm:p-0"
            >
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                >
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <!-- Icon -->
                            <div
                                :class="[
                                    'mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10',
                                    type === 'danger'
                                        ? 'bg-red-100'
                                        : type === 'warning'
                                        ? 'bg-yellow-100'
                                        : 'bg-blue-100',
                                ]"
                            >
                                <i
                                    :class="[
                                        'fa text-xl',
                                        type === 'danger'
                                            ? 'fa-exclamation-triangle text-red-600'
                                            : type === 'warning'
                                            ? 'fa-exclamation-circle text-yellow-600'
                                            : 'fa-info-circle text-blue-600',
                                    ]"
                                ></i>
                            </div>

                            <!-- Content -->
                            <div
                                class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left"
                            >
                                <h3
                                    class="text-base font-semibold leading-6 text-gray-900"
                                    id="modal-title"
                                >
                                    {{ title }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        {{ message }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
                    >
                        <button
                            type="button"
                            :class="[
                                'inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto',
                                type === 'danger'
                                    ? 'bg-red-600 hover:bg-red-500'
                                    : type === 'warning'
                                    ? 'bg-yellow-600 hover:bg-yellow-500'
                                    : 'bg-blue-600 hover:bg-blue-500',
                            ]"
                            @click="handleConfirm"
                        >
                            {{ confirmText }}
                        </button>
                        <button
                            type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                            @click="handleCancel"
                        >
                            {{ cancelText }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .transform,
.modal-leave-active .transform {
    transition: all 0.3s ease;
}

.modal-enter-from .transform,
.modal-leave-to .transform {
    opacity: 0;
    transform: scale(0.95);
}
</style>
