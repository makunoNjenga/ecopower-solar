<script setup>
import { computed } from "vue";

const props = defineProps({
    currentPage: {
        type: Number,
        required: true,
    },
    totalPages: {
        type: Number,
        required: true,
    },
    totalItems: {
        type: Number,
        required: true,
    },
    itemsPerPage: {
        type: Number,
        required: true,
    },
    showInfo: {
        type: Boolean,
        default: true,
    },
    maxVisiblePages: {
        type: Number,
        default: 5,
    },
});

const emit = defineEmits(["page-changed"]);

/**
 * Calculate visible page numbers around current page
 */
const visiblePages = computed(() => {
    const pages = [];
    const totalPages = props.totalPages;
    const current = props.currentPage;
    const maxVisible = props.maxVisiblePages;

    if (totalPages <= maxVisible) {
        // Show all pages if total is less than max visible
        for (let i = 1; i <= totalPages; i++) {
            pages.push(i);
        }
    } else {
        // Calculate start and end of visible range
        let start = Math.max(1, current - Math.floor(maxVisible / 2));
        let end = Math.min(totalPages, start + maxVisible - 1);

        // Adjust start if we're near the end
        if (end - start + 1 < maxVisible) {
            start = Math.max(1, end - maxVisible + 1);
        }

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
    }

    return pages;
});

/**
 * Calculate pagination info text
 */
const paginationInfo = computed(() => {
    const start = (props.currentPage - 1) * props.itemsPerPage + 1;
    const end = Math.min(
        props.currentPage * props.itemsPerPage,
        props.totalItems
    );
    return `Showing ${start}-${end} of ${props.totalItems} items`;
});

/**
 * Check if previous button should be disabled
 */
const isPreviousDisabled = computed(() => {
    return props.currentPage <= 1;
});

/**
 * Check if next button should be disabled
 */
const isNextDisabled = computed(() => {
    return props.currentPage >= props.totalPages;
});

/**
 * Handle page change
 */
function changePage(page) {
    if (page >= 1 && page <= props.totalPages && page !== props.currentPage) {
        emit("page-changed", page);
    }
}

/**
 * Go to previous page
 */
function goToPrevious() {
    if (!isPreviousDisabled.value) {
        changePage(props.currentPage - 1);
    }
}

/**
 * Go to next page
 */
function goToNext() {
    if (!isNextDisabled.value) {
        changePage(props.currentPage + 1);
    }
}
</script>

<template>
    <div
        class="flex items-center justify-between px-6 py-3 bg-white border-t border-gray-200"
    >
        <!-- Pagination Info -->
        <div v-if="showInfo" class="flex-1 flex justify-between sm:hidden">
            <button
                @click="goToPrevious"
                :disabled="isPreviousDisabled"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Previous
            </button>
            <button
                @click="goToNext"
                :disabled="isNextDisabled"
                class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Next
            </button>
        </div>

        <div
            v-if="showInfo"
            class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"
        >
            <div>
                <p class="text-sm text-gray-700">
                    {{ paginationInfo }}
                </p>
            </div>
            <div>
                <nav
                    class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                    aria-label="Pagination"
                >
                    <!-- Previous Button -->
                    <button
                        @click="goToPrevious"
                        :disabled="isPreviousDisabled"
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="{
                            'cursor-not-allowed opacity-50': isPreviousDisabled,
                        }"
                    >
                        <span class="sr-only">Previous</span>
                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                    </button>

                    <!-- Page Numbers -->
                    <template v-for="page in visiblePages" :key="page">
                        <!-- Show first page and ellipsis if needed -->
                        <template v-if="page === visiblePages[0] && page > 1">
                            <button
                                @click="changePage(1)"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                1
                            </button>
                            <span
                                v-if="page > 2"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                            >
                                ...
                            </span>
                        </template>

                        <!-- Current page or regular page -->
                        <button
                            @click="changePage(page)"
                            :class="{
                                'z-10 bg-primary-green border-primary-green text-white':
                                    page === currentPage,
                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50':
                                    page !== currentPage,
                            }"
                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                        >
                            {{ page }}
                        </button>

                        <!-- Show last page and ellipsis if needed -->
                        <template
                            v-if="
                                page ===
                                    visiblePages[visiblePages.length - 1] &&
                                page < totalPages
                            "
                        >
                            <span
                                v-if="page < totalPages - 1"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                            >
                                ...
                            </span>
                            <button
                                @click="changePage(totalPages)"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                {{ totalPages }}
                            </button>
                        </template>
                    </template>

                    <!-- Next Button -->
                    <button
                        @click="goToNext"
                        :disabled="isNextDisabled"
                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="{
                            'cursor-not-allowed opacity-50': isNextDisabled,
                        }"
                    >
                        <span class="sr-only">Next</span>
                        <i class="fa fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>
