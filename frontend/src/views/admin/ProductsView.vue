<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useRouter } from "vue-router";
import { productService } from "@/services/productService";
import { categoryService } from "@/services/categoryService";
import WysiwygEditor from "@/components/ui/WysiwygEditor.vue";
import ConfirmDialog from "@/components/ui/ConfirmDialog.vue";
import Pagination from "@/components/ui/Pagination.vue";

const router = useRouter();
const products = ref([]);
const categories = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const currentProduct = ref(null);
const searchQuery = ref("");
const selectedCategory = ref("");
const showDeleteDialog = ref(false);
const productToDelete = ref(null);

// Pagination state
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const itemsPerPage = ref(15);

const form = ref({
    name: "",
    description: "",
    short_description: "",
    sku: "",
    price: 0,
    sale_price: null,
    stock_quantity: 0,
    min_stock_level: 0,
    weight: null,
    category_id: "",
    brand: "",
    tags: [],
    is_featured: false,
    is_active: true,
    meta_title: "",
    meta_description: "",
});

// Remove client-side filtering since we'll handle it server-side with pagination
const filteredProducts = computed(() => {
    return products.value;
});

/**
 * Load products from API with pagination
 */
async function loadProducts() {
    loading.value = true;
    try {
        const params = {
            page: currentPage.value,
            per_page: itemsPerPage.value,
        };

        // Only add search and category_id if they have actual values
        if (searchQuery.value && searchQuery.value.trim()) {
            params.search = searchQuery.value.trim();
        }

        if (selectedCategory.value && selectedCategory.value !== "") {
            params.category_id = selectedCategory.value;
        }

        const response = await productService.getAdminProducts(params);

        // Handle paginated response from Laravel
        // The API interceptor returns response.data, so response is { data: [...], meta: {...} }
        if (response.data && Array.isArray(response.data)) {
            // Paginated response structure: { data: [...], meta: {...} }
            products.value = response.data;
            totalPages.value = response.meta?.last_page || 1;
            totalItems.value = response.meta?.total || 0;
            currentPage.value = response.meta?.current_page || 1;
        } else if (Array.isArray(response)) {
            // Direct array response (non-paginated)
            products.value = response;
            totalPages.value = 1;
            totalItems.value = products.value.length;
            currentPage.value = 1;
        } else {
            // Fallback for any other structure
            products.value = [];
            totalPages.value = 1;
            totalItems.value = 0;
            currentPage.value = 1;
        }
    } catch (error) {
        console.error("Error loading products:", error);

        if (error.status === 401) {
            alert("Authentication required. Please log in again.");
            router.push("/login");
        } else if (error.status === 403) {
            alert("Access denied. Admin privileges required.");
        } else {
            alert(
                "Failed to load products: " + (error.message || "Unknown error")
            );
        }
    } finally {
        loading.value = false;
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
 * Open modal for creating new product
 */
function openCreateModal() {
    isEditing.value = false;
    currentProduct.value = null;
    resetForm();
    showModal.value = true;
}

/**
 * Open modal for editing product
 */
function openEditModal(product) {
    isEditing.value = true;
    currentProduct.value = product;

    // Populate form
    form.value = {
        name: product.name,
        description: product.description,
        short_description: product.short_description || "",
        sku: product.sku,
        price: product.price,
        sale_price: product.sale_price,
        stock_quantity: product.stock_quantity,
        min_stock_level: product.min_stock_level || 0,
        weight: product.weight,
        category_id: product.category?.id || "",
        brand: product.brand || "",
        tags: product.tags || [],
        is_featured: product.is_featured,
        is_active: product.is_active,
        meta_title: product.meta_title || "",
        meta_description: product.meta_description || "",
    };

    showModal.value = true;
}

/**
 * Navigate to product images page
 */
function manageImages(product) {
    router.push(`/admin/products/${product.id}/images`);
}

/**
 * Reset form
 */
function resetForm() {
    form.value = {
        name: "",
        description: "",
        short_description: "",
        sku: "",
        price: 0,
        sale_price: null,
        stock_quantity: 0,
        min_stock_level: 0,
        weight: null,
        category_id: "",
        brand: "",
        tags: [],
        is_featured: false,
        is_active: true,
        meta_title: "",
        meta_description: "",
    };
}

/**
 * Save product (create or update)
 */
async function saveProduct() {
    loading.value = true;
    try {
        if (isEditing.value) {
            await productService.updateProduct(
                currentProduct.value.id,
                form.value
            );
        } else {
            await productService.createProduct(form.value);
        }

        showModal.value = false;
        await loadProducts();
        alert(
            isEditing.value
                ? "Product updated successfully"
                : "Product created successfully"
        );
    } catch (error) {
        console.error("Error saving product:", error);
        alert(
            "Failed to save product: " +
                (error.response?.data?.message || error.message)
        );
    } finally {
        loading.value = false;
    }
}

/**
 * Show delete confirmation dialog
 */
function confirmDelete(product) {
    productToDelete.value = product;
    showDeleteDialog.value = true;
}

/**
 * Delete product after confirmation
 */
async function deleteProduct() {
    if (!productToDelete.value) return;

    loading.value = true;
    try {
        await productService.deleteProduct(productToDelete.value.id);
        await loadProducts();
        alert("Product deleted successfully");
        productToDelete.value = null;
    } catch (error) {
        console.error("Error deleting product:", error);
        alert("Failed to delete product");
    } finally {
        loading.value = false;
    }
}

/**
 * Cancel delete action
 */
function cancelDelete() {
    productToDelete.value = null;
    showDeleteDialog.value = false;
}

/**
 * Handle page change from pagination
 */
function handlePageChange(page) {
    currentPage.value = page;
    loadProducts();
}

/**
 * Handle search query change with debounce
 */
function handleSearch() {
    currentPage.value = 1; // Reset to first page when searching
    loadProducts();
}

/**
 * Handle category filter change
 */
function handleCategoryChange() {
    currentPage.value = 1; // Reset to first page when filtering
    loadProducts();
}

// Watch for search query changes with debounce
let searchTimeout = null;
watch(searchQuery, (newValue) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        handleSearch();
    }, 500); // 500ms debounce
});

onMounted(() => {
    loadProducts();
    loadCategories();
});
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-display font-bold text-gray-900">
                Manage Products
            </h1>
            <button @click="openCreateModal" class="btn-primary">
                <i class="fa fa-plus mr-2"></i>Add Product
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search products..."
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
            </div>
        </div>

        <!-- Products Table -->
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
                            Product
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            SKU
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Price
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Stock
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Status
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="product in filteredProducts" :key="product.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-10 w-10 cursor-pointer"
                                    @click="openEditModal(product)"
                                >
                                    <img
                                        v-if="product.primary_image"
                                        :src="`/storage/${product.primary_image.path}`"
                                        :alt="product.name"
                                        class="h-10 w-10 rounded object-cover hover:opacity-75 transition-opacity"
                                    />
                                    <div
                                        v-else
                                        class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center hover:bg-gray-300 transition-colors"
                                    >
                                        <i
                                            class="fa fa-image text-gray-400"
                                        ></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div
                                        class="text-sm font-medium text-gray-900 cursor-pointer hover:text-blue-600 transition-colors"
                                        @click="openEditModal(product)"
                                    >
                                        {{ product.name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ product.category?.name }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ product.sku }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm">
                                <PriceDisplay
                                    :amount="product.price"
                                    :sale-price="product.sale_price"
                                    :show-discount="true"
                                />
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="{
                                    'text-green-600':
                                        product.stock_quantity >
                                        product.min_stock_level,
                                    'text-yellow-600':
                                        product.stock_quantity > 0 &&
                                        product.stock_quantity <=
                                            product.min_stock_level,
                                    'text-red-600':
                                        product.stock_quantity === 0,
                                }"
                                class="text-sm font-medium"
                            >
                                {{ product.stock_quantity }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="
                                    product.is_active
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800'
                                "
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            >
                                {{ product.is_active ? "Active" : "Inactive" }}
                            </span>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2"
                        >
                            <button
                                @click="openEditModal(product)"
                                class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded transition-colors"
                                title="Edit Product"
                            >
                                <i class="fa fa-edit mr-1"></i>
                                Edit
                            </button>
                            <button
                                @click="manageImages(product)"
                                class="inline-flex items-center px-3 py-1 bg-green-50 text-green-600 hover:bg-green-100 rounded transition-colors"
                                title="Manage Images"
                            >
                                <i class="fa fa-images mr-1"></i>
                                Images
                            </button>
                            <button
                                @click="confirmDelete(product)"
                                class="inline-flex items-center px-3 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded transition-colors"
                                title="Delete Product"
                            >
                                <i class="fa fa-trash mr-1"></i>
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                v-if="filteredProducts.length === 0"
                class="text-center py-8 text-gray-500"
            >
                No products found
            </div>
        </div>

        <!-- Pagination -->
        <Pagination
            v-if="totalPages > 1"
            :current-page="currentPage"
            :total-pages="totalPages"
            :total-items="totalItems"
            :items-per-page="itemsPerPage"
            @page-changed="handlePageChange"
        />

        <!-- Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto"
        >
            <div
                class="bg-white rounded-lg p-8 max-w-3xl w-full mx-4 my-8 max-h-screen overflow-y-auto relative"
            >
                <button
                    @click="showModal = false"
                    class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition-colors z-10 bg-white rounded-full p-2 shadow-md hover:shadow-lg"
                    type="button"
                    title="Close"
                >
                    <i class="fa fa-times text-xl"></i>
                </button>
                <h2 class="text-2xl font-bold mb-6">
                    {{ isEditing ? "Edit Product" : "Create Product" }}
                </h2>

                <form @submit.prevent="saveProduct" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Name <span class="text-red-600">*</span></label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >SKU <span class="text-red-600">*</span></label
                            >
                            <input
                                v-model="form.sku"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Price
                                <span class="text-red-600">*</span></label
                            >
                            <input
                                v-model.number="form.price"
                                type="number"
                                step="0.01"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Sale Price</label
                            >
                            <input
                                v-model.number="form.sale_price"
                                type="number"
                                step="0.01"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Stock Quantity
                                <span class="text-red-600">*</span></label
                            >
                            <input
                                v-model.number="form.stock_quantity"
                                type="number"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Min Stock Level</label
                            >
                            <input
                                v-model.number="form.min_stock_level"
                                type="number"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Category
                                <span class="text-red-600">*</span></label
                            >
                            <select
                                v-model="form.category_id"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            >
                                <option value="">Select a category</option>
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
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Brand</label
                            >
                            <input
                                v-model="form.brand"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Weight (kg)</label
                            >
                            <input
                                v-model.number="form.weight"
                                type="number"
                                step="0.01"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Short Description</label
                        >
                        <WysiwygEditor
                            v-model="form.short_description"
                            placeholder="Enter short product description..."
                            :height="120"
                            toolbar="bold italic underline | link | removeformat"
                            plugins="link"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Description
                            <span class="text-red-600">*</span></label
                        >
                        <WysiwygEditor
                            v-model="form.description"
                            placeholder="Enter product description..."
                            :height="250"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <input
                                v-model="form.is_featured"
                                type="checkbox"
                                class="h-4 w-4 text-primary-green focus:ring-primary-green border-gray-300 rounded"
                            />
                            <label class="ml-2 block text-sm text-gray-700"
                                >Featured Product</label
                            >
                        </div>

                        <div class="flex items-center">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 text-primary-green focus:ring-primary-green border-gray-300 rounded"
                            />
                            <label class="ml-2 block text-sm text-gray-700"
                                >Active</label
                            >
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 mt-6">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="loading"
                            class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{
                                loading
                                    ? "Saving..."
                                    : isEditing
                                    ? "Update"
                                    : "Create"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            v-model:show="showDeleteDialog"
            title="Delete Product"
            :message="`Are you sure you want to delete '${productToDelete?.name}'? This action cannot be undone.`"
            confirm-text="Delete"
            cancel-text="Cancel"
            type="danger"
            @confirm="deleteProduct"
            @cancel="cancelDelete"
        />
    </div>
</template>
