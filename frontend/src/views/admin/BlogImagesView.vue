<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { blogService } from "@/services/blogService";

const route = useRoute();
const router = useRouter();
const blog = ref(null);
const images = ref([]);
const loading = ref(false);
const uploadingImage = ref(false);
const selectedFiles = ref([]);
const altText = ref("");
const caption = ref("");

/**
 * Load blog details
 */
async function loadBlog() {
    loading.value = true;
    try {
        const response = await blogService.getAdminBlog(route.params.id);
        blog.value = response.data || response;
    } catch (error) {
        console.error("Error loading blog:", error);
        alert("Failed to load blog");
        router.push("/admin/blogs");
    } finally {
        loading.value = false;
    }
}

/**
 * Load blog images
 */
async function loadImages() {
    loading.value = true;
    try {
        const response = await blogService.getBlogImages(route.params.id);
        images.value = response.data || response;
    } catch (error) {
        console.error("Error loading images:", error);
        alert("Failed to load images");
    } finally {
        loading.value = false;
    }
}

/**
 * Handle file selection
 */
function onFileSelect(event) {
    const files = Array.from(event.target.files);
    if (files.length > 0) {
        selectedFiles.value = files;
        if (files.length > 1) {
            altText.value = "";
            caption.value = "";
        }
    }
}

/**
 * Upload images
 */
async function uploadImages() {
    if (selectedFiles.value.length === 0) {
        alert("Please select at least one image");
        return;
    }

    uploadingImage.value = true;

    try {
        if (selectedFiles.value.length === 1) {
            const formData = new FormData();
            formData.append("image", selectedFiles.value[0]);
            formData.append("alt_text", altText.value);
            formData.append("caption", caption.value);

            await blogService.uploadBlogImage(route.params.id, formData);
            alert("Image uploaded successfully");
        } else {
            const formData = new FormData();

            selectedFiles.value.forEach((file) => {
                formData.append("images[]", file);
            });

            if (altText.value) {
                selectedFiles.value.forEach(() => {
                    formData.append("alt_texts[]", altText.value);
                });
            }

            if (caption.value) {
                selectedFiles.value.forEach(() => {
                    formData.append("captions[]", caption.value);
                });
            }

            const response = await blogService.uploadMultipleBlogImages(
                route.params.id,
                formData
            );

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
        caption.value = "";

        const fileInput = document.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.value = "";
        }

        await loadImages();
    } catch (error) {
        console.error("Error uploading images:", error);
        alert(
            "Failed to upload images: " +
                (error.response?.data?.message || error.message)
        );
    } finally {
        uploadingImage.value = false;
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
        await blogService.deleteBlogImage(route.params.id, image.id);
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
 * Go back to blogs page
 */
function goBack() {
    router.push("/admin/blogs");
}

onMounted(() => {
    loadBlog();
    loadImages();
});
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <button
                    @click="goBack"
                    class="text-primary-green hover:text-green-700 mb-2 inline-flex items-center"
                >
                    <i class="fa fa-arrow-left mr-2"></i>
                    Back to Blogs
                </button>
                <h1 class="text-3xl font-display font-bold text-gray-900">
                    Manage Blog Images
                </h1>
                <p v-if="blog" class="text-gray-600 mt-1">{{ blog.title }}</p>
            </div>
        </div>

        <!-- Upload Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Upload Images</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Images
                    </label>
                    <input
                        type="file"
                        @change="onFileSelect"
                        accept="image/*"
                        multiple
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                    />
                    <p class="text-sm text-gray-500 mt-1">
                        You can select multiple images. Max 5MB per image.
                    </p>
                    <div
                        v-if="selectedFiles.length > 0"
                        class="mt-2 text-sm text-gray-600"
                    >
                        {{ selectedFiles.length }} file(s) selected
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Alt Text
                        </label>
                        <input
                            v-model="altText"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            placeholder="Image alt text"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Caption
                        </label>
                        <input
                            v-model="caption"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            placeholder="Image caption"
                        />
                    </div>
                </div>

                <button
                    @click="uploadImages"
                    :disabled="uploadingImage || selectedFiles.length === 0"
                    class="btn-primary"
                >
                    <i
                        :class="
                            uploadingImage ? 'fa-spinner fa-spin' : 'fa-upload'
                        "
                        class="fa mr-2"
                    ></i>
                    {{ uploadingImage ? "Uploading..." : "Upload Images" }}
                </button>
            </div>
        </div>

        <!-- Images Grid -->
        <div v-if="loading && images.length === 0" class="text-center py-8">
            <i class="fa fa-spinner fa-spin text-4xl text-primary-green"></i>
        </div>

        <div
            v-else
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            <div
                v-for="image in images"
                :key="image.id"
                class="bg-white rounded-lg shadow-md overflow-hidden"
            >
                <div class="relative aspect-video bg-gray-200">
                    <img
                        :src="`/storage/${image.image_path}`"
                        :alt="image.alt_text || 'Blog image'"
                        class="w-full h-full object-cover"
                    />
                </div>

                <div class="p-4">
                    <div class="mb-3">
                        <p v-if="image.alt_text" class="text-sm text-gray-600">
                            <strong>Alt:</strong> {{ image.alt_text }}
                        </p>
                        <p
                            v-if="image.caption"
                            class="text-sm text-gray-600 mt-1"
                        >
                            <strong>Caption:</strong> {{ image.caption }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            Order: {{ image.sort_order }}
                        </p>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button
                            @click="deleteImage(image)"
                            class="inline-flex items-center px-3 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded transition-colors"
                            title="Delete Image"
                        >
                            <i class="fa fa-trash mr-1"></i>
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-if="images.length === 0"
                class="col-span-full text-center py-12 text-gray-500"
            >
                <i class="fa fa-images text-6xl mb-4"></i>
                <p>No images uploaded yet</p>
            </div>
        </div>
    </div>
</template>
