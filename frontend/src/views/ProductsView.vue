<template>
    <div class="min-h-screen bg-neutral-light">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1
                    class="text-3xl font-display font-bold text-neutral-charcoal mb-2"
                >
                    Our Solar Products
                </h1>
                <p class="text-gray-600">
                    Discover our range of high-quality solar solutions for your
                    energy needs
                </p>
            </div>

            <!-- Search and Filter Section -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label
                            for="search"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Search Products
                        </label>
                        <div class="relative">
                            <input
                                id="search"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by name, description..."
                                class="input-field pl-10"
                                @input="handleSearch"
                            />
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <i class="fa fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label
                            for="category"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Filter by Category
                        </label>
                        <select
                            id="category"
                            v-model="selectedCategory"
                            @change="handleCategoryChange"
                            class="input-field"
                        >
                            <option value="">All Categories</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Active Filters Display -->
                <div
                    v-if="activeFilters.length > 0"
                    class="mt-4 pt-4 border-t border-gray-200"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm text-gray-600"
                            >Active filters:</span
                        >
                        <span
                            v-for="filter in activeFilters"
                            :key="filter.key"
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-light text-primary-dark"
                        >
                            {{ filter.label }}
                            <button
                                @click="removeFilter(filter.key)"
                                class="ml-2 text-primary-dark hover:text-primary"
                            >
                                <i class="fa fa-times"></i>
                            </button>
                        </span>
                        <button
                            @click="clearAllFilters"
                            class="text-sm text-gray-500 hover:text-gray-700 underline"
                        >
                            Clear all
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
                <div
                    class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
                ></div>
                <p class="mt-4 text-gray-600">Loading products...</p>
            </div>

            <!-- Products Grid -->
            <div
                v-else-if="filteredProducts.length > 0"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            >
                <div
                    v-for="product in filteredProducts"
                    :key="product.id"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer"
                    @click="viewProduct(product)"
                >
                    <!-- Product Image -->
                    <div class="aspect-w-16 aspect-h-12 bg-gray-100">
                        <img
                            v-if="product.primary_image"
                            :src="product.primary_image.url"
                            :alt="
                                product.primary_image.alt_text || product.name
                            "
                            class="w-full h-48 object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400"
                        >
                            <div class="text-center">
                                <i class="fa fa-image text-4xl mb-2"></i>
                                <p class="text-sm">
                                    {{ product.name }} - Image
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="p-4">
                        <h3
                            class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2"
                        >
                            {{ product.name }}
                        </h3>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                            {{ product.description }}
                        </p>

                        <!-- Price Section -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span
                                    v-if="
                                        product.original_price &&
                                        product.original_price !== product.price
                                    "
                                    class="text-sm text-gray-500 line-through"
                                >
                                    KSh
                                    {{ formatPrice(product.original_price) }}
                                </span>
                                <span
                                    :class="[
                                        'font-bold text-lg',
                                        product.original_price &&
                                        product.original_price !== product.price
                                            ? 'text-primary'
                                            : 'text-gray-900',
                                    ]"
                                >
                                    KSh {{ formatPrice(product.price) }}
                                </span>
                            </div>

                            <!-- Discount Badge -->
                            <div
                                v-if="
                                    product.original_price &&
                                    product.original_price !== product.price
                                "
                                class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded"
                            >
                                -{{
                                    calculateDiscountPercentage(
                                        product.original_price,
                                        product.price
                                    )
                                }}%
                            </div>
                        </div>

                        <!-- Category Badge -->
                        <div v-if="product.category" class="mt-2">
                            <span
                                class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded"
                            >
                                {{ product.category.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Products Found -->
            <div v-else class="text-center py-12">
                <div class="text-gray-400 mb-4">
                    <i class="fa fa-search text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    No products found
                </h3>
                <p class="text-gray-600 mb-4">
                    Try adjusting your search criteria or browse all categories
                </p>
                <button @click="clearAllFilters" class="btn-primary">
                    Clear Filters
                </button>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="mt-8">
                <nav class="flex items-center justify-between">
                    <div class="flex items-center">
                        <p class="text-sm text-gray-700">
                            Showing {{ pagination.from }} to
                            {{ pagination.to }} of
                            {{ pagination.total }} results
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button
                            @click="changePage(pagination.current_page - 1)"
                            :disabled="pagination.current_page <= 1"
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>
                        <button
                            v-for="page in visiblePages"
                            :key="page"
                            @click="changePage(page)"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded-md',
                                page === pagination.current_page
                                    ? 'bg-primary text-white'
                                    : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50',
                            ]"
                        >
                            {{ page }}
                        </button>
                        <button
                            @click="changePage(pagination.current_page + 1)"
                            :disabled="
                                pagination.current_page >= pagination.last_page
                            "
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import { productService } from "@/services/productService";
import { categoryService } from "@/services/categoryService";

const router = useRouter();

// Reactive data
const products = ref([]);
const categories = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const selectedCategory = ref("");
const pagination = ref(null);

// Debounced search
let searchTimeout = null;

/**
 * Load products from API
 */
async function loadProducts(page = 1) {
    loading.value = true;
    try {
        const params = {
            page,
            per_page: 12,
        };

        if (selectedCategory.value) {
            const response = await categoryService.getCategoryProducts(
                selectedCategory.value,
                params
            );
            products.value = response.data || [];
            pagination.value = response.meta || null;
        } else {
            const response = await productService.getProducts({
                ...params,
                search: searchQuery.value,
            });
            products.value = response.data || [];
            pagination.value = response.meta || null;
        }
    } catch (error) {
        console.error("Error loading products:", error);
        products.value = [];
        pagination.value = null;
    } finally {
        loading.value = false;
    }
}

/**
 * Load categories from API
 */
async function loadCategories() {
    try {
        const response = await categoryService.getCategories(true);
        categories.value = response.data || response.categories || [];
    } catch (error) {
        console.error("Error loading categories:", error);
        categories.value = [];
    }
}

/**
 * Handle search input with debouncing
 */
function handleSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadProducts(1);
    }, 500);
}

/**
 * Handle category change
 */
function handleCategoryChange() {
    loadProducts(1);
}

/**
 * View product details
 */
function viewProduct(product) {
    router.push({
        name: "product-detail",
        params: { slug: product.slug || product.id },
    });
}

/**
 * Format price for display
 */
function formatPrice(price) {
    return new Intl.NumberFormat("en-KE").format(price);
}

/**
 * Calculate discount percentage
 */
function calculateDiscountPercentage(originalPrice, currentPrice) {
    const discount = ((originalPrice - currentPrice) / originalPrice) * 100;
    return Math.round(discount);
}

/**
 * Change page
 */
function changePage(page) {
    if (page >= 1 && page <= pagination.value.last_page) {
        loadProducts(page);
    }
}

/**
 * Remove specific filter
 */
function removeFilter(filterKey) {
    if (filterKey === "search") {
        searchQuery.value = "";
        loadProducts(1);
    } else if (filterKey === "category") {
        selectedCategory.value = "";
        loadProducts(1);
    }
}

/**
 * Clear all filters
 */
function clearAllFilters() {
    searchQuery.value = "";
    selectedCategory.value = "";
    loadProducts(1);
}

// Computed properties
const filteredProducts = computed(() => {
    return products.value;
});

const activeFilters = computed(() => {
    const filters = [];
    if (searchQuery.value) {
        filters.push({
            key: "search",
            label: `Search: "${searchQuery.value}"`,
        });
    }
    if (selectedCategory.value) {
        const category = categories.value.find(
            (c) => c.id == selectedCategory.value
        );
        if (category) {
            filters.push({
                key: "category",
                label: `Category: ${category.name}`,
            });
        }
    }
    return filters;
});

const visiblePages = computed(() => {
    if (!pagination.value) return [];

    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    const delta = 2;

    const range = [];
    const rangeWithDots = [];

    for (
        let i = Math.max(2, current - delta);
        i <= Math.min(last - 1, current + delta);
        i++
    ) {
        range.push(i);
    }

    if (current - delta > 2) {
        rangeWithDots.push(1, "...");
    } else {
        rangeWithDots.push(1);
    }

    rangeWithDots.push(...range);

    if (current + delta < last - 1) {
        rangeWithDots.push("...", last);
    } else {
        rangeWithDots.push(last);
    }

    return rangeWithDots;
});

// Lifecycle
onMounted(() => {
    loadCategories();
    loadProducts();
});
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.aspect-w-16 {
    position: relative;
    padding-bottom: 75%;
}

.aspect-w-16 > * {
    position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}
</style>
