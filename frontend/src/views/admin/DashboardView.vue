<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAlert } from "@/composables/useAlert";
import { dashboardService } from "@/services/dashboardService";
import { productService } from "@/services/productService";
import { categoryService } from "@/services/categoryService";
import { blogService } from "@/services/blogService";
import WysiwygEditor from "@/components/ui/WysiwygEditor.vue";

const router = useRouter();
const alert = useAlert();
const loading = ref(false);
const stats = ref(null);
const categories = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const currentProduct = ref(null);

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

/**
 * Load dashboard statistics
 */
async function loadStats() {
    loading.value = true;
    try {
        const response = await dashboardService.getStats();
        stats.value = response;
    } catch (error) {
        console.error("Error loading stats:", error);
        alert.error("Failed to load dashboard statistics");
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
 * Navigate to manage products page
 */
function goToProducts() {
    router.push("/admin/products");
}

/**
 * Navigate to manage categories page
 */
function goToCategories() {
    router.push("/admin/categories");
}

/**
 * Navigate to manage blogs page
 */
function goToBlogs() {
    router.push("/admin/blogs");
}

/**
 * Navigate to manage product images
 */
function manageImages(product) {
    router.push(`/admin/products/${product.id}/images`);
}

/**
 * Open modal for editing product
 */
function openEditModal(product) {
    isEditing.value = true;
    currentProduct.value = product;

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
 * Save product (update only)
 */
async function saveProduct() {
    loading.value = true;
    try {
        await productService.updateProduct(currentProduct.value.id, form.value);

        showModal.value = false;
        await loadStats();
        alert.success("Product updated successfully");
    } catch (error) {
        console.error("Error saving product:", error);
        alert.error(
            "Failed to save product: " +
                (error.response?.data?.message || error.message)
        );
    } finally {
        loading.value = false;
    }
}

/**
 * Show delete confirmation dialog and delete product
 */
async function confirmDelete(product) {
    const confirmed = await alert.confirmDelete(product.name);

    if (confirmed) {
        loading.value = true;
        try {
            await productService.deleteProduct(product.id);
            await loadStats();
            alert.success("Product deleted successfully");
        } catch (error) {
            console.error("Error deleting product:", error);
            alert.error("Failed to delete product");
        } finally {
            loading.value = false;
        }
    }
}

onMounted(() => {
    loadStats();
    loadCategories();
});
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-display font-bold text-gray-900">
                Admin Dashboard
            </h1>
            <div class="flex space-x-4">
                <button @click="goToProducts" class="btn-primary">
                    <i class="fa fa-box mr-2"></i>Manage Products
                </button>
                <button @click="goToCategories" class="btn-primary">
                    <i class="fa fa-folder mr-2"></i>Manage Categories
                </button>
                <button @click="goToBlogs" class="btn-primary">
                    <i class="fa fa-newspaper mr-2"></i>Manage Blog
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
            <i class="fa fa-spinner fa-spin text-4xl text-primary-green"></i>
        </div>

        <!-- Dashboard Content -->
        <div v-else-if="stats">
            <!-- Product Statistics -->
            <div class="mb-8">
                <h2
                    class="text-2xl font-display font-semibold text-gray-900 mb-4"
                >
                    Product Statistics
                </h2>
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <!-- Total Products -->
                    <div
                        class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">
                                    Total Products
                                </p>
                                <p
                                    class="text-3xl font-bold text-gray-900 mt-2"
                                >
                                    {{ stats.products.total }}
                                </p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-3">
                                <i class="fa fa-box text-blue-500 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Active Products -->
                    <div
                        class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">
                                    Active Products
                                </p>
                                <p
                                    class="text-3xl font-bold text-gray-900 mt-2"
                                >
                                    {{ stats.products.active }}
                                </p>
                            </div>
                            <div class="bg-green-100 rounded-full p-3">
                                <i
                                    class="fa fa-check-circle text-green-500 text-2xl"
                                ></i>
                            </div>
                        </div>
                    </div>

                    <!-- Inactive Products -->
                    <div
                        class="bg-white rounded-lg shadow-md p-6 border-l-4 border-gray-500"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">
                                    Inactive Products
                                </p>
                                <p
                                    class="text-3xl font-bold text-gray-900 mt-2"
                                >
                                    {{ stats.products.inactive }}
                                </p>
                            </div>
                            <div class="bg-gray-100 rounded-full p-3">
                                <i
                                    class="fa fa-times-circle text-gray-500 text-2xl"
                                ></i>
                            </div>
                        </div>
                    </div>

                    <!-- Featured Products -->
                    <div
                        class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">
                                    Featured Products
                                </p>
                                <p
                                    class="text-3xl font-bold text-gray-900 mt-2"
                                >
                                    {{ stats.products.featured }}
                                </p>
                            </div>
                            <div class="bg-yellow-100 rounded-full p-3">
                                <i
                                    class="fa fa-star text-yellow-500 text-2xl"
                                ></i>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Products -->
                    <div
                        class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">
                                    Low Stock Products
                                </p>
                                <p
                                    class="text-3xl font-bold text-gray-900 mt-2"
                                >
                                    {{ stats.products.low_stock }}
                                </p>
                            </div>
                            <div class="bg-orange-100 rounded-full p-3">
                                <i
                                    class="fa fa-exclamation-triangle text-orange-500 text-2xl"
                                ></i>
                            </div>
                        </div>
                    </div>

                    <!-- Out of Stock Products -->
                    <div
                        class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">
                                    Out of Stock
                                </p>
                                <p
                                    class="text-3xl font-bold text-gray-900 mt-2"
                                >
                                    {{ stats.products.out_of_stock }}
                                </p>
                            </div>
                            <div class="bg-red-100 rounded-full p-3">
                                <i
                                    class="fa fa-times text-red-500 text-2xl"
                                ></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Statistics -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fa fa-users mr-2"></i>User Statistics
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600 text-sm">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ stats.users.total }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">
                            New Users This Month
                        </p>
                        <p class="text-2xl font-bold text-blue-600 mt-1">
                            {{ stats.users.new_this_month }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Recent Products -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">
                        <i class="fa fa-history mr-2"></i>Recent Products
                    </h3>
                    <button
                        @click="goToProducts"
                        class="text-primary-green hover:text-green-700 text-sm font-medium"
                    >
                        View All <i class="fa fa-arrow-right ml-1"></i>
                    </button>
                </div>

                <div class="overflow-x-auto">
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
                            <tr
                                v-for="product in stats.recent_products"
                                :key="product.id"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 cursor-pointer"
                                            @click="openEditModal(product)"
                                        >
                                            <img
                                                v-if="product.primary_image"
                                                :src="
                                                    product.primary_image.path
                                                "
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
                                        {{
                                            product.is_active
                                                ? "Active"
                                                : "Inactive"
                                        }}
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
                        v-if="
                            !stats.recent_products ||
                            stats.recent_products.length === 0
                        "
                        class="text-center py-8 text-gray-500"
                    >
                        No products found
                    </div>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div
                v-if="
                    stats.low_stock_alerts && stats.low_stock_alerts.length > 0
                "
                class="bg-yellow-50 border border-yellow-200 rounded-lg shadow-md p-6"
            >
                <h3 class="text-xl font-semibold text-yellow-900 mb-4">
                    <i class="fa fa-exclamation-triangle mr-2"></i>Low Stock
                    Alerts
                </h3>
                <div class="space-y-3">
                    <div
                        v-for="product in stats.low_stock_alerts"
                        :key="product.id"
                        class="flex items-center justify-between bg-white p-3 rounded-lg"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-8 w-8 cursor-pointer"
                                @click="openEditModal(product)"
                            >
                                <img
                                    v-if="product.primary_image"
                                    :src="product.primary_image.path"
                                    :alt="product.name"
                                    class="h-8 w-8 rounded object-cover hover:opacity-75 transition-opacity"
                                />
                                <div
                                    v-else
                                    class="h-8 w-8 bg-gray-200 rounded flex items-center justify-center hover:bg-gray-300 transition-colors"
                                >
                                    <i
                                        class="fa fa-image text-gray-400 text-xs"
                                    ></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p
                                    class="text-sm font-medium text-gray-900 cursor-pointer hover:text-blue-600 transition-colors"
                                    @click="openEditModal(product)"
                                >
                                    {{ product.name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    SKU: {{ product.sku }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-yellow-600">
                                {{ product.stock_quantity }} in stock
                            </p>
                            <p class="text-xs text-gray-500">
                                Min: {{ product.min_stock_level }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div
            v-else
            class="bg-red-50 border border-red-200 rounded-lg p-6 text-center"
        >
            <i class="fa fa-exclamation-circle text-red-500 text-3xl mb-2"></i>
            <p class="text-red-700">Failed to load dashboard data</p>
        </div>

        <!-- Edit Product Modal -->
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
                <h2 class="text-2xl font-bold mb-6">Edit Product</h2>

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
                            {{ loading ? "Saving..." : "Update" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
