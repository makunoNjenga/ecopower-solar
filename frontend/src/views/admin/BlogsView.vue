<script setup>
import { ref, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import { blogService } from "@/services/blogService";
import { categoryService } from "@/services/categoryService";
import ConfirmDialog from "@/components/ui/ConfirmDialog.vue";
import Pagination from "@/components/ui/Pagination.vue";

const router = useRouter();
const blogs = ref([]);
const categories = ref([]);
const statistics = ref(null);
const loading = ref(false);
const searchQuery = ref("");
const selectedCategory = ref("");
const selectedStatus = ref("");
const showDeleteDialog = ref(false);
const blogToDelete = ref(null);

// Pagination state
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const itemsPerPage = ref(15);

/**
 * Load blogs from API with pagination
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

        if (selectedStatus.value !== "") {
            params.is_published = selectedStatus.value === "published";
        }

        const response = await blogService.getAdminBlogs(params);

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

        if (error.status === 401) {
            alert("Authentication required. Please log in again.");
            router.push("/login");
        } else if (error.status === 403) {
            alert("Access denied. Admin privileges required.");
        } else {
            alert(
                "Failed to load blogs: " + (error.message || "Unknown error")
            );
        }
    } finally {
        loading.value = false;
    }
}

/**
 * Load statistics
 */
async function loadStatistics() {
    try {
        const response = await blogService.getBlogStatistics();
        statistics.value = response;
    } catch (error) {
        console.error("Error loading statistics:", error);
    }
}

/**
 * Load categories from API
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
 * Navigate to create blog page
 */
function createBlog() {
    router.push("/admin/blogs/create");
}

/**
 * Navigate to edit blog page
 */
function editBlog(blog) {
    router.push(`/admin/blogs/${blog.id}/edit`);
}

/**
 * Navigate to blog images page
 */
function manageImages(blog) {
    router.push(`/admin/blogs/${blog.id}/images`);
}

/**
 * Show delete confirmation dialog
 */
function confirmDelete(blog) {
    blogToDelete.value = blog;
    showDeleteDialog.value = true;
}

/**
 * Delete blog after confirmation
 */
async function deleteBlog() {
    if (!blogToDelete.value) return;

    loading.value = true;
    try {
        await blogService.deleteBlog(blogToDelete.value.id);
        await loadBlogs();
        await loadStatistics();
        alert("Blog deleted successfully");
        blogToDelete.value = null;
    } catch (error) {
        console.error("Error deleting blog:", error);
        alert("Failed to delete blog");
    } finally {
        loading.value = false;
    }
}

/**
 * Cancel delete action
 */
function cancelDelete() {
    blogToDelete.value = null;
    showDeleteDialog.value = false;
}

/**
 * Handle page change from pagination
 */
function handlePageChange(page) {
    currentPage.value = page;
    loadBlogs();
}

/**
 * Handle search query change with debounce
 */
function handleSearch() {
    currentPage.value = 1;
    loadBlogs();
}

/**
 * Handle category filter change
 */
function handleCategoryChange() {
    currentPage.value = 1;
    loadBlogs();
}

/**
 * Handle status filter change
 */
function handleStatusChange() {
    currentPage.value = 1;
    loadBlogs();
}

/**
 * Format date to readable string
 */
function formatDate(dateString) {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
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
    loadStatistics();
});
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-display font-bold text-gray-900">
                Manage Blogs
            </h1>
            <button @click="createBlog" class="btn-primary">
                <i class="fa fa-plus mr-2"></i>Add Blog
            </button>
        </div>

        <!-- Statistics Cards -->
        <div
            v-if="statistics"
            class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6"
        >
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Blogs</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ statistics.total_blogs }}
                        </p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fa fa-newspaper text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Published</p>
                        <p class="text-2xl font-bold text-green-600">
                            {{ statistics.published_blogs }}
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i
                            class="fa fa-check-circle text-green-600 text-xl"
                        ></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Drafts</p>
                        <p class="text-2xl font-bold text-yellow-600">
                            {{ statistics.draft_blogs }}
                        </p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fa fa-file-alt text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Views</p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ statistics.total_views }}
                        </p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fa fa-eye text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search blogs..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                    />
                </div>
                <div>
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
                <div>
                    <select
                        v-model="selectedStatus"
                        @change="handleStatusChange"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                    >
                        <option value="">All Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Blogs Table -->
        <div v-if="loading" class="text-center py-8">
            <i class="fa fa-spinner fa-spin text-4xl text-primary-green"></i>
        </div>

        <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Blog
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Author
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Views
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Status
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Published
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="blog in blogs" :key="blog.id">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-16 w-16 cursor-pointer"
                                    @click="editBlog(blog)"
                                >
                                    <img
                                        v-if="blog.featured_image"
                                        :src="`/storage/${blog.featured_image}`"
                                        :alt="blog.title"
                                        class="h-16 w-16 rounded object-cover hover:opacity-75 transition-opacity"
                                    />
                                    <div
                                        v-else
                                        class="h-16 w-16 bg-gray-200 rounded flex items-center justify-center hover:bg-gray-300 transition-colors"
                                    >
                                        <i
                                            class="fa fa-image text-gray-400"
                                        ></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div
                                        class="text-sm font-medium text-gray-900 cursor-pointer hover:text-blue-600 transition-colors"
                                        @click="editBlog(blog)"
                                    >
                                        {{ blog.title }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{
                                            blog.category?.name ||
                                            "Uncategorized"
                                        }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ blog.author?.name || "Unknown" }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">
                                <i class="fa fa-eye text-gray-400 mr-1"></i>
                                {{ blog.views }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="
                                    blog.is_published
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-yellow-100 text-yellow-800'
                                "
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            >
                                {{ blog.is_published ? "Published" : "Draft" }}
                            </span>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ formatDate(blog.published_at) }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2"
                        >
                            <button
                                @click="editBlog(blog)"
                                class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded transition-colors"
                                title="Edit Blog"
                            >
                                <i class="fa fa-edit mr-1"></i>
                                Edit
                            </button>
                            <button
                                @click="manageImages(blog)"
                                class="inline-flex items-center px-3 py-1 bg-green-50 text-green-600 hover:bg-green-100 rounded transition-colors"
                                title="Manage Images"
                            >
                                <i class="fa fa-images mr-1"></i>
                                Images
                            </button>
                            <button
                                @click="confirmDelete(blog)"
                                class="inline-flex items-center px-3 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded transition-colors"
                                title="Delete Blog"
                            >
                                <i class="fa fa-trash mr-1"></i>
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="blogs.length === 0">
                        <td
                            colspan="6"
                            class="px-6 py-8 text-center text-gray-500"
                        >
                            <i class="fa fa-inbox text-4xl mb-2"></i>
                            <p>No blogs found</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div
                v-if="totalPages > 1"
                class="px-6 py-4 border-t border-gray-200"
            >
                <Pagination
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :total-items="totalItems"
                    @page-change="handlePageChange"
                />
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            v-if="showDeleteDialog"
            title="Delete Blog"
            message="Are you sure you want to delete this blog? This action cannot be undone."
            confirm-text="Delete"
            cancel-text="Cancel"
            @confirm="deleteBlog"
            @cancel="cancelDelete"
        />
    </div>
</template>
