<script setup>
import { ref, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import { useAlert } from "@/composables/useAlert";
import { blogService } from "@/services/blogService";
import { categoryService } from "@/services/categoryService";
import Pagination from "@/components/ui/Pagination.vue";

const router = useRouter();
const alert = useAlert();
const blogs = ref([]);
const categories = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const selectedCategory = ref("");

// Pagination state
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const itemsPerPage = ref(12);

/**
 * Load published blogs
 */
async function loadBlogs() {
    loading.value = true;
    try {
        const params = {
            page: currentPage.value,
            per_page: itemsPerPage.value,
        };

        if (searchQuery.value && searchQuery.value.trim()) {
            params.search = searchQuery.value.trim();
        }

        if (selectedCategory.value && selectedCategory.value !== "") {
            params.category_id = selectedCategory.value;
        }

        const response = await blogService.getBlogs(params);

        if (response.data && Array.isArray(response.data)) {
            blogs.value = response.data;
            totalPages.value = response.meta?.last_page || 1;
            totalItems.value = response.meta?.total || 0;
            currentPage.value = response.meta?.current_page || 1;
        } else if (Array.isArray(response)) {
            blogs.value = response;
            totalPages.value = 1;
            totalItems.value = blogs.value.length;
            currentPage.value = 1;
        } else {
            blogs.value = [];
            totalPages.value = 1;
            totalItems.value = 0;
            currentPage.value = 1;
        }
    } catch (error) {
        console.error("Error loading blogs:", error);
        alert.error("Failed to load blogs");
    } finally {
        loading.value = false;
    }
}

/**
 * Load categories
 */
async function loadCategories() {
    try {
        const response = await categoryService.getCategories();
        categories.value =
            response.data?.categories || response.categories || [];
    } catch (error) {
        console.error("Error loading categories:", error);
    }
}

/**
 * Navigate to blog detail
 */
function viewBlog(blog) {
    router.push(`/blogs/${blog.slug}`);
}

/**
 * Format date
 */
function formatDate(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}

/**
 * Get excerpt
 */
function getExcerpt(blog) {
    if (blog.excerpt) return blog.excerpt;

    // Create excerpt from content (strip HTML and limit to 150 chars)
    const plainText = blog.content.replace(/<[^>]*>/g, "");
    return plainText.length > 150
        ? plainText.substring(0, 150) + "..."
        : plainText;
}

/**
 * Handle page change
 */
function handlePageChange(page) {
    currentPage.value = page;
    loadBlogs();
    window.scrollTo({ top: 0, behavior: "smooth" });
}

/**
 * Handle search
 */
function handleSearch() {
    currentPage.value = 1;
    loadBlogs();
}

/**
 * Handle category change
 */
function handleCategoryChange() {
    currentPage.value = 1;
    loadBlogs();
}

/**
 * Clear filters
 */
function clearFilters() {
    searchQuery.value = "";
    selectedCategory.value = "";
    currentPage.value = 1;
    loadBlogs();
}

// Watch for search query changes with debounce
let searchTimeout = null;
watch(searchQuery, (newValue) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        handleSearch();
    }, 500);
});

onMounted(() => {
    loadBlogs();
    loadCategories();
});
</script>

<template>
    <div class="min-h-screen bg-neutral-light">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8 text-center">
                <h1
                    class="text-4xl font-display font-bold text-neutral-charcoal mb-4"
                >
                    Solar Power & Green Energy Blog
                </h1>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Stay updated with the latest insights, tips, and news about
                    solar power and sustainable energy solutions
                </p>
            </div>

            <!-- Search and Filter -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Search Blogs
                        </label>
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by title or content..."
                                class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            />
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <i class="fa fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Filter by Category
                        </label>
                        <select
                            v-model="selectedCategory"
                            @change="handleCategoryChange"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
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

                <div
                    v-if="searchQuery || selectedCategory"
                    class="mt-4 pt-4 border-t border-gray-200"
                >
                    <button
                        @click="clearFilters"
                        class="text-sm text-gray-500 hover:text-gray-700 underline"
                    >
                        Clear all filters
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
                <div
                    class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-green"
                ></div>
                <p class="mt-4 text-gray-600">Loading blogs...</p>
            </div>

            <!-- Blogs Grid -->
            <div v-else-if="blogs.length > 0" class="space-y-8">
                <div
                    v-for="blog in blogs"
                    :key="blog.id"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer"
                    @click="viewBlog(blog)"
                >
                    <div class="md:flex">
                        <!-- Featured Image -->
                        <div class="md:w-1/3">
                            <div class="aspect-w-16 aspect-h-9 bg-gray-100">
                                <img
                                    v-if="blog.featured_image"
                                    :src="blog.featured_image"
                                    :alt="blog.title"
                                    class="w-full h-64 md:h-full object-cover"
                                />
                                <div
                                    v-else
                                    class="w-full h-64 md:h-full bg-gradient-to-br from-primary-green to-primary-dark flex items-center justify-center text-white"
                                >
                                    <div class="text-center">
                                        <i
                                            class="fa fa-newspaper text-5xl mb-2 opacity-50"
                                        ></i>
                                        <p class="text-sm opacity-75">
                                            {{ blog.title }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Blog Info -->
                        <div class="md:w-2/3 p-6">
                            <div
                                class="flex items-center text-sm text-gray-500 mb-3"
                            >
                                <span
                                    v-if="blog.category"
                                    class="bg-primary-light text-primary-dark px-3 py-1 rounded-full mr-3"
                                >
                                    {{ blog.category.name }}
                                </span>
                                <span class="flex items-center">
                                    <i class="fa fa-calendar mr-1"></i>
                                    {{ formatDate(blog.published_at) }}
                                </span>
                                <span class="flex items-center ml-4">
                                    <i class="fa fa-eye mr-1"></i>
                                    {{ blog.views }} views
                                </span>
                            </div>

                            <h2
                                class="text-2xl font-bold text-gray-900 mb-3 hover:text-primary-green transition-colors"
                            >
                                {{ blog.title }}
                            </h2>

                            <p class="text-gray-600 mb-4 line-clamp-3">
                                {{ getExcerpt(blog) }}
                            </p>

                            <div class="flex items-center justify-between">
                                <div
                                    class="flex items-center text-sm text-gray-500"
                                >
                                    <i class="fa fa-user mr-2"></i>
                                    <span>{{
                                        blog.author?.name || "Admin"
                                    }}</span>
                                </div>

                                <span
                                    class="text-primary-green hover:text-primary-dark font-medium inline-flex items-center"
                                >
                                    Read More
                                    <i class="fa fa-arrow-right ml-2"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <i class="fa fa-inbox text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No blogs found</p>
                <p class="text-gray-400">
                    Try adjusting your search or filters
                </p>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="mt-8">
                <Pagination
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :total-items="totalItems"
                    @page-change="handlePageChange"
                />
            </div>
        </div>
    </div>
</template>
