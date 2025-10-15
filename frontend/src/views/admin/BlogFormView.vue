<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAlert } from "@/composables/useAlert";
import { blogService } from "@/services/blogService";
import { categoryService } from "@/services/categoryService";
import { productService } from "@/services/productService";
import WysiwygEditor from "@/components/ui/WysiwygEditor.vue";

const router = useRouter();
const route = useRoute();
const alert = useAlert();
const isEditing = computed(() => !!route.params.id);
const loading = ref(false);
const categories = ref([]);
const products = ref([]);
const selectedProducts = ref([]);

const form = ref({
    title: "",
    excerpt: "",
    content: "",
    category_id: "",
    featured_image: "",
    meta_title: "",
    meta_description: "",
    meta_keywords: "",
    is_published: false,
    published_at: null,
    product_ids: [],
});

/**
 * Load blog data if editing
 */
async function loadBlog() {
    if (!isEditing.value) return;

    loading.value = true;
    try {
        const response = await blogService.getAdminBlog(route.params.id);
        const blog = response.data || response;

        form.value = {
            title: blog.title,
            excerpt: blog.excerpt || "",
            content: blog.content,
            category_id: blog.category_id || "",
            featured_image: blog.featured_image || "",
            meta_title: blog.meta_title || "",
            meta_description: blog.meta_description || "",
            meta_keywords: blog.meta_keywords || "",
            is_published: blog.is_published,
            published_at: blog.published_at,
            product_ids: blog.products?.map((p) => p.id) || [],
        };

        selectedProducts.value = blog.products || [];
    } catch (error) {
        console.error("Error loading blog:", error);
        alert.error("Failed to load blog");
        router.push("/admin/blogs");
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
 * Load products for selection
 */
async function loadProducts() {
    try {
        const response = await productService.getAdminProducts({
            per_page: 100,
        });
        products.value = response.data || [];
    } catch (error) {
        console.error("Error loading products:", error);
    }
}

/**
 * Handle featured image upload
 */
async function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Placeholder - you'd typically upload to a separate endpoint
    // For this example, we'll use a local URL
    form.value.featured_image = URL.createObjectURL(file);
}

/**
 * Toggle product selection
 */
function toggleProduct(product) {
    const index = form.value.product_ids.indexOf(product.id);
    if (index > -1) {
        form.value.product_ids.splice(index, 1);
        selectedProducts.value = selectedProducts.value.filter(
            (p) => p.id !== product.id
        );
    } else {
        form.value.product_ids.push(product.id);
        selectedProducts.value.push(product);
    }
}

/**
 * Check if product is selected
 */
function isProductSelected(product) {
    return form.value.product_ids.includes(product.id);
}

/**
 * Save blog
 */
async function saveBlog() {
    // Validation
    if (!form.value.title.trim()) {
        alert.warning("Please enter a blog title");
        return;
    }

    if (!form.value.content.trim()) {
        alert.warning("Please enter blog content");
        return;
    }

    loading.value = true;
    try {
        if (isEditing.value) {
            await blogService.updateBlog(route.params.id, form.value);
            alert.success("Blog updated successfully");
        } else {
            await blogService.createBlog(form.value);
            alert.success("Blog created successfully");
        }

        router.push("/admin/blogs");
    } catch (error) {
        console.error("Error saving blog:", error);
        alert.error(
            "Failed to save blog: " +
                (error.response?.data?.message || error.message)
        );
    } finally {
        loading.value = false;
    }
}

/**
 * Cancel and go back
 */
function cancel() {
    router.push("/admin/blogs");
}

onMounted(() => {
    loadCategories();
    loadProducts();
    if (isEditing.value) {
        loadBlog();
    }
});
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-display font-bold text-gray-900">
                {{ isEditing ? "Edit Blog" : "Create New Blog" }}
            </h1>
        </div>

        <div v-if="loading && isEditing" class="text-center py-8">
            <i class="fa fa-spinner fa-spin text-4xl text-primary-green"></i>
        </div>

        <form v-else @submit.prevent="saveBlog" class="space-y-6">
            <!-- Main Content -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Blog Content</h2>

                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            placeholder="Enter blog title"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Excerpt
                        </label>
                        <textarea
                            v-model="form.excerpt"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            placeholder="Brief summary of the blog"
                        ></textarea>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Content <span class="text-red-500">*</span>
                        </label>
                        <WysiwygEditor v-model="form.content" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Category
                            </label>
                            <select
                                v-model="form.category_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            >
                                <option value="">Uncategorized</option>
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
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Featured Image
                            </label>
                            <input
                                type="file"
                                @change="handleImageUpload"
                                accept="image/*"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            />
                            <div v-if="form.featured_image" class="mt-2">
                                <img
                                    :src="form.featured_image"
                                    alt="Featured image"
                                    class="h-32 w-auto rounded"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Meta -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">SEO Meta Information</h2>

                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Meta Title
                        </label>
                        <input
                            v-model="form.meta_title"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            placeholder="SEO title (defaults to blog title)"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Meta Description
                        </label>
                        <textarea
                            v-model="form.meta_description"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            placeholder="Brief description for search engines"
                        ></textarea>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Meta Keywords
                        </label>
                        <input
                            v-model="form.meta_keywords"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            placeholder="Comma-separated keywords"
                        />
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Related Products</h2>

                <div v-if="selectedProducts.length > 0" class="mb-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">
                        Selected Products:
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="product in selectedProducts"
                            :key="product.id"
                            class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm"
                        >
                            {{ product.name }}
                            <button
                                @click.prevent="toggleProduct(product)"
                                class="ml-2 text-green-600 hover:text-green-800"
                            >
                                <i class="fa fa-times"></i>
                            </button>
                        </span>
                    </div>
                </div>

                <div
                    class="max-h-96 overflow-y-auto border border-gray-300 rounded-lg"
                >
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Select
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Product
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Price
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="product in products"
                                :key="product.id"
                                :class="{
                                    'bg-green-50': isProductSelected(product),
                                }"
                                class="hover:bg-gray-50 cursor-pointer"
                                @click="toggleProduct(product)"
                            >
                                <td class="px-4 py-2">
                                    <input
                                        type="checkbox"
                                        :checked="isProductSelected(product)"
                                        @click.stop
                                        @change="toggleProduct(product)"
                                        class="h-4 w-4 text-primary-green focus:ring-primary-green border-gray-300 rounded"
                                    />
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center">
                                        <img
                                            v-if="product.primary_image"
                                            :src="product.primary_image.path"
                                            :alt="product.name"
                                            class="h-10 w-10 rounded object-cover mr-2"
                                        />
                                        <div
                                            v-else
                                            class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center mr-2"
                                        >
                                            <i
                                                class="fa fa-image text-gray-400"
                                            ></i>
                                        </div>
                                        <span class="text-sm">{{
                                            product.name
                                        }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    ${{ product.price }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Publish Settings -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Publish Settings</h2>

                <div class="flex items-center">
                    <input
                        v-model="form.is_published"
                        type="checkbox"
                        id="is_published"
                        class="h-4 w-4 text-primary-green focus:ring-primary-green border-gray-300 rounded"
                    />
                    <label
                        for="is_published"
                        class="ml-2 text-sm text-gray-700"
                    >
                        Publish this blog
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4">
                <button
                    @click.prevent="cancel"
                    type="button"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Cancel
                </button>
                <button type="submit" :disabled="loading" class="btn-primary">
                    <i v-if="loading" class="fa fa-spinner fa-spin mr-2"></i>
                    <i v-else class="fa fa-save mr-2"></i>
                    {{ isEditing ? "Update Blog" : "Create Blog" }}
                </button>
            </div>
        </form>
    </div>
</template>
