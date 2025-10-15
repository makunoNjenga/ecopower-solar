<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { productService } from "@/services/productService";

const route = useRoute();
const router = useRouter();
const product = ref(null);
const images = ref([]);
const loading = ref(false);
const uploadingImage = ref(false);
const selectedFiles = ref([]);
const altText = ref("");
const isPrimary = ref(false);
const uploadProgress = ref(0);

/**
 * Load product details
 */
async function loadProduct() {
    loading.value = true;
    try {
        const response = await productService.getAdminProduct(route.params.id);
        product.value = response.data;
    } catch (error) {
        console.error("Error loading product:", error);
        alert("Failed to load product");
        router.push("/admin/products");
    } finally {
        loading.value = false;
    }
}

/**
 * Load product images
 */
async function loadImages() {
    loading.value = true;
    try {
        const response = await productService.getProductImages(route.params.id);
        images.value = response.data || response;
    } catch (error) {
        console.error("Error loading images:", error);
        alert("Failed to load images");
    } finally {
        loading.value = false;
    }
}

/**
 * Handle file selection (multiple files)
 */
function onFileSelect(event) {
    const files = Array.from(event.target.files);
    if (files.length > 0) {
        selectedFiles.value = files;
        // Reset alt text if multiple files selected
        if (files.length > 1) {
            altText.value = "";
        }
    }
}

/**
 * Upload image(s)
 */
async function uploadImages() {
    if (selectedFiles.value.length === 0) {
        alert("Please select at least one image");
        return;
    }

    uploadingImage.value = true;
    uploadProgress.value = 0;

    try {
        // If single file, use single upload method
        if (selectedFiles.value.length === 1) {
            const formData = new FormData();
            formData.append("image", selectedFiles.value[0]);
            formData.append("alt_text", altText.value);
            formData.append("is_primary", isPrimary.value ? "1" : "0");

            await productService.uploadProductImage(route.params.id, formData);
            alert("Image uploaded successfully");
        } else {
            // Multiple files - use multiple upload method
            const formData = new FormData();

            // Add all files
            selectedFiles.value.forEach((file) => {
                formData.append("images[]", file);
            });

            // Add alt texts if provided
            if (altText.value) {
                selectedFiles.value.forEach(() => {
                    formData.append("alt_texts[]", altText.value);
                });
            }

            // Set first as primary if checkbox is checked
            formData.append(
                "set_first_as_primary",
                isPrimary.value ? "1" : "0"
            );

            const response = await productService.uploadMultipleProductImages(
                route.params.id,
                formData
            );

            // Handle response
            const data = response.data;

            if (data.success_count > 0) {
                alert(`${data.success_count} images uploaded successfully`);

                if (data.error_count && data.error_count > 0) {
                    alert(
                        `Warning: ${data.error_count} images failed to upload`
                    );
                }
            } else {
                alert("Failed to upload images");
            }
        }

        // Reset form
        selectedFiles.value = [];
        altText.value = "";
        isPrimary.value = false;

        // Clear file input
        const fileInput = document.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.value = "";
        }

        // Reload images
        await loadImages();
    } catch (error) {
        console.error("Error uploading images:", error);
        alert(
            "Failed to upload images: " +
                (error.response?.data?.message || error.message)
        );
    } finally {
        uploadingImage.value = false;
        uploadProgress.value = 0;
    }
}

/**
 * Set image as primary
 */
async function setPrimaryImage(image) {
    loading.value = true;
    try {
        await productService.updateProductImage(route.params.id, image.id, {
            is_primary: true,
        });
        await loadImages();
        alert("Primary image updated");
    } catch (error) {
        console.error("Error updating primary image:", error);
        alert("Failed to update primary image");
    } finally {
        loading.value = false;
    }
}

/**
 * Delete image
 */
async function deleteImage(image) {
    if (!confirm("Are you sure you want to delete this image?")) {
        return;
    }

    loading.value = true;
    try {
        await productService.deleteProductImage(route.params.id, image.id);
        await loadImages();
        alert("Image deleted successfully");
    } catch (error) {
        console.error("Error deleting image:", error);
        alert("Failed to delete image");
    } finally {
        loading.value = false;
    }
}

/**
 * Go back to products page
 */
function goBack() {
    router.push("/admin/products");
}

onMounted(() => {
    loadProduct();
    loadImages();
});
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <button
                @click="goBack"
                class="text-primary-green hover:text-green-700 flex items-center mb-4"
            >
                <i class="fa fa-arrow-left mr-2"></i> Back to Products
            </button>
            <h1 class="text-3xl font-display font-bold text-gray-900">
                Manage Images: {{ product?.name || "Loading..." }}
            </h1>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Upload New Image</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                        >Select Image <span class="text-red-600">*</span></label
                    >
                    <div class="relative">
                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                            @change="onFileSelect"
                            multiple
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                        />
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Max size: 5MB per file. Max 10 files. Formats: JPEG,
                        PNG, JPG, GIF, WEBP
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                        >Alt Text</label
                    >
                    <input
                        v-model="altText"
                        type="text"
                        placeholder="Descriptive text for the image"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                    />
                </div>
            </div>
            <div class="mt-6">
                <div class="flex items-center mb-4">
                    <input
                        v-model="isPrimary"
                        type="checkbox"
                        class="h-4 w-4 text-primary-green focus:ring-primary-green border-gray-300 rounded"
                    />
                    <label class="ml-2 block text-sm text-gray-700"
                        >Set as primary image</label
                    >
                </div>

                <!-- Upload Button -->
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        <span
                            v-if="selectedFiles.length > 0"
                            class="text-green-600"
                        >
                            <i class="fa fa-check-circle mr-1"></i>
                            {{ selectedFiles.length }} file(s) selected
                            <span v-if="selectedFiles.length <= 3" class="ml-2">
                                ({{
                                    selectedFiles.map((f) => f.name).join(", ")
                                }})
                            </span>
                            <span v-else class="ml-2">
                                ({{ selectedFiles[0].name }} and
                                {{ selectedFiles.length - 1 }} more)
                            </span>
                        </span>
                        <span v-else class="text-gray-500">
                            <i class="fa fa-info-circle mr-1"></i>
                            Select one or more images to upload
                        </span>
                    </div>

                    <button
                        @click="uploadImages"
                        :disabled="selectedFiles.length === 0 || uploadingImage"
                        class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i
                            v-if="uploadingImage"
                            class="fa fa-spinner fa-spin mr-2"
                        ></i>
                        <i v-else class="fa fa-upload mr-2"></i>
                        {{
                            uploadingImage
                                ? "Uploading..."
                                : `Upload ${
                                      selectedFiles.length === 1
                                          ? "Image"
                                          : "Images"
                                  }`
                        }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Images Grid -->
        <div v-if="loading" class="text-center py-8">
            <i class="fa fa-spinner fa-spin text-4xl text-primary-green"></i>
        </div>

        <div
            v-else-if="images.length === 0"
            class="bg-white rounded-lg shadow-md p-8 text-center text-gray-500"
        >
            <i class="fa fa-images text-6xl mb-4 text-gray-300"></i>
            <p>No images uploaded yet</p>
        </div>

        <div
            v-else
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
        >
            <div
                v-for="image in images"
                :key="image.id"
                class="bg-white rounded-lg shadow-md overflow-hidden relative group"
            >
                <!-- Primary Badge -->
                <div
                    v-if="image.is_primary"
                    class="absolute top-2 left-2 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold z-10"
                >
                    Primary
                </div>

                <!-- Image -->
                <div class="aspect-square bg-gray-100">
                    <img
                        :src="image.path"
                        :alt="image.alt_text || product?.name"
                        class="w-full h-full object-cover"
                    />
                </div>

                <!-- Image Details -->
                <div class="p-4">
                    <p
                        class="text-sm text-gray-600 mb-2 truncate"
                        :title="image.alt_text"
                    >
                        {{ image.alt_text || "No description" }}
                    </p>
                    <p class="text-xs text-gray-400">
                        Order: {{ image.sort_order }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="p-4 border-t flex justify-end space-x-2">
                    <button
                        v-if="!image.is_primary"
                        @click="setPrimaryImage(image)"
                        class="text-green-600 hover:text-green-900 text-sm"
                        title="Set as primary"
                    >
                        <i class="fa fa-star"></i>
                    </button>
                    <button
                        @click="deleteImage(image)"
                        class="text-red-600 hover:text-red-900 text-sm"
                        title="Delete"
                    >
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
