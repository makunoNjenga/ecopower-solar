<script setup>
import { ref, onMounted, computed } from "vue";
import { useAlert } from "@/composables/useAlert";
import { categoryService } from "@/services/categoryService";

const alert = useAlert();
const categories = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const currentCategory = ref(null);
const searchQuery = ref("");

const form = ref({
    name: "",
    description: "",
    slug: "",
    parent_id: null,
    is_active: true,
});

const filteredCategories = computed(() => {
    let filtered = categories.value;

    // Filter by search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(
            (c) =>
                c.name.toLowerCase().includes(query) ||
                (c.description &&
                    c.description.toLowerCase().includes(query)) ||
                (c.slug && c.slug.toLowerCase().includes(query))
        );
    }

    return filtered;
});

/**
 * Load categories from API
 */
async function loadCategories() {
    loading.value = true;
    try {
        const response = await categoryService.getCategories();
        categories.value =
            response.data?.categories || response.categories || [];
    } catch (error) {
        console.error("Error loading categories:", error);
        alert.error("Failed to load categories");
    } finally {
        loading.value = false;
    }
}

/**
 * Open modal for creating new category
 */
function openCreateModal() {
    isEditing.value = false;
    currentCategory.value = null;
    resetForm();
    showModal.value = true;
}

/**
 * Open modal for editing category
 */
function openEditModal(category) {
    isEditing.value = true;
    currentCategory.value = category;

    // Populate form
    form.value = {
        name: category.name,
        description: category.description || "",
        slug: category.slug || "",
        parent_id: category.parent_id || null,
        is_active: category.is_active,
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
        slug: "",
        parent_id: null,
        is_active: true,
    };
}

/**
 * Save category (create or update)
 */
async function saveCategory() {
    loading.value = true;
    try {
        if (isEditing.value) {
            await categoryService.updateCategory(
                currentCategory.value.id,
                form.value
            );
        } else {
            await categoryService.createCategory(form.value);
        }

        showModal.value = false;
        await loadCategories();
        alert.success(
            isEditing.value
                ? "Category updated successfully"
                : "Category created successfully"
        );
    } catch (error) {
        console.error("Error saving category:", error);
        alert.error(
            "Failed to save category: " +
                (error.response?.data?.message || error.message)
        );
    } finally {
        loading.value = false;
    }
}

/**
 * Show delete confirmation dialog and delete category
 */
async function confirmDelete(category) {
    const confirmed = await alert.confirmDelete(category.name);

    if (confirmed) {
        loading.value = true;
        try {
            await categoryService.deleteCategory(category.id);
            await loadCategories();
            alert.success("Category deleted successfully");
        } catch (error) {
            console.error("Error deleting category:", error);
            alert.error(
                "Failed to delete category: " +
                    (error.response?.data?.message || error.message)
            );
        } finally {
            loading.value = false;
        }
    }
}

/**
 * Get parent category name
 */
function getParentCategoryName(parentId) {
    if (!parentId) return "-";
    const parent = categories.value.find((c) => c.id === parentId);
    return parent ? parent.name : "-";
}

onMounted(() => {
    loadCategories();
});
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-display font-bold text-gray-900">
                Manage Categories
            </h1>
            <button @click="openCreateModal" class="btn-primary">
                <i class="fa fa-plus mr-2"></i>Add Category
            </button>
        </div>

        <!-- Search Filter -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search categories..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
            />
        </div>

        <!-- Categories Table -->
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
                            Name
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Slug
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Parent Category
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
                        v-for="category in filteredCategories"
                        :key="category.id"
                    >
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ category.name }}
                            </div>
                            <div
                                v-if="category.description"
                                class="text-sm text-gray-500 truncate max-w-md"
                            >
                                {{ category.description }}
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ category.slug }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ getParentCategoryName(category.parent_id) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="
                                    category.is_active
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800'
                                "
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            >
                                {{ category.is_active ? "Active" : "Inactive" }}
                            </span>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2"
                        >
                            <button
                                @click="openEditModal(category)"
                                class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded transition-colors"
                                title="Edit Category"
                            >
                                <i class="fa fa-edit mr-1"></i>
                                Edit
                            </button>
                            <button
                                @click="confirmDelete(category)"
                                class="inline-flex items-center px-3 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded transition-colors"
                                title="Delete Category"
                            >
                                <i class="fa fa-trash mr-1"></i>
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                v-if="filteredCategories.length === 0"
                class="text-center py-8 text-gray-500"
            >
                No categories found
            </div>
        </div>

        <!-- Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto"
        >
            <div
                class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 my-8 max-h-screen overflow-y-auto relative"
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
                    {{ isEditing ? "Edit Category" : "Create Category" }}
                </h2>

                <form @submit.prevent="saveCategory" class="space-y-4">
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
                            >Slug</label
                        >
                        <input
                            v-model="form.slug"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            placeholder="Auto-generated if left blank"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Description</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                        ></textarea>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Parent Category</label
                        >
                        <select
                            v-model="form.parent_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                        >
                            <option :value="null">None (Top Level)</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                                :disabled="
                                    isEditing &&
                                    category.id === currentCategory?.id
                                "
                            >
                                {{ category.name }}
                            </option>
                        </select>
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
    </div>
</template>
