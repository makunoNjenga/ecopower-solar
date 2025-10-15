<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { blogService } from "@/services/blogService";
import PriceDisplay from "@/components/global/PriceDisplay.vue";

const router = useRouter();
const route = useRoute();
const blog = ref(null);
const loading = ref(false);
const relatedProducts = ref([]);

/**
 * Load blog by slug
 */
async function loadBlog() {
    loading.value = true;
    try {
        const response = await blogService.getBlog(route.params.slug);
        blog.value = response.data || response;
        relatedProducts.value = blog.value.products || [];
    } catch (error) {
        console.error("Error loading blog:", error);
        if (error.status === 404) {
            alert.error("Blog not found");
            router.push("/blogs");
        } else {
            alert.error("Failed to load blog");
        }
    } finally {
        loading.value = false;
    }
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
 * Navigate to product
 */
function viewProduct(product) {
    router.push(`/products/${product.id}`);
}

/**
 * Go back to blogs list
 */
function goBack() {
    router.push("/blogs");
}

/**
 * Share blog
 */
function shareBlog(platform) {
    const url = window.location.href;
    const title = blog.value?.title || "";

    let shareUrl = "";

    switch (platform) {
        case "facebook":
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(
                url
            )}`;
            break;
        case "twitter":
            shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(
                url
            )}&text=${encodeURIComponent(title)}`;
            break;
        case "linkedin":
            shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(
                url
            )}`;
            break;
    }

    if (shareUrl) {
        window.open(shareUrl, "_blank", "width=600,height=400");
    }
}

onMounted(() => {
    loadBlog();
});
</script>

<template>
    <div class="min-h-screen bg-neutral-light">
        <!-- Loading State -->
        <div v-if="loading" class="container mx-auto px-4 py-12 text-center">
            <div
                class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-green"
            ></div>
            <p class="mt-4 text-gray-600">Loading blog...</p>
        </div>

        <!-- Blog Content -->
        <div v-else-if="blog" class="container mx-auto px-4 py-8">
            <!-- Back Button -->
            <button
                @click="goBack"
                class="text-primary-green hover:text-primary-dark mb-6 inline-flex items-center"
            >
                <i class="fa fa-arrow-left mr-2"></i>
                Back to Blogs
            </button>

            <!-- Blog Header -->
            <article class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Featured Image -->
                <div v-if="blog.featured_image" class="w-full">
                    <img
                        :src="blog.featured_image"
                        :alt="blog.title"
                        class="w-full h-96 object-cover"
                    />
                </div>

                <!-- Blog Meta and Title -->
                <div class="p-8">
                    <div class="flex items-center text-sm text-gray-500 mb-4">
                        <span
                            v-if="blog.category"
                            class="bg-primary-light text-primary-dark px-3 py-1 rounded-full mr-4"
                        >
                            {{ blog.category.name }}
                        </span>
                        <span class="flex items-center mr-4">
                            <i class="fa fa-calendar mr-2"></i>
                            {{ formatDate(blog.published_at) }}
                        </span>
                        <span class="flex items-center mr-4">
                            <i class="fa fa-user mr-2"></i>
                            {{ blog.author?.name || "Admin" }}
                        </span>
                        <span class="flex items-center">
                            <i class="fa fa-eye mr-2"></i>
                            {{ blog.views }} views
                        </span>
                    </div>

                    <h1
                        class="text-4xl font-display font-bold text-neutral-charcoal mb-6"
                    >
                        {{ blog.title }}
                    </h1>

                    <div
                        v-if="blog.excerpt"
                        class="text-xl text-gray-600 mb-8 pb-8 border-b border-gray-200"
                    >
                        {{ blog.excerpt }}
                    </div>

                    <!-- Blog Content -->
                    <div
                        class="prose prose-lg max-w-none mb-8"
                        v-html="blog.content"
                    ></div>

                    <!-- Blog Images -->
                    <div
                        v-if="blog.images && blog.images.length > 0"
                        class="mb-8"
                    >
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">
                            Gallery
                        </h3>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                        >
                            <div
                                v-for="image in blog.images"
                                :key="image.id"
                                class="relative aspect-video bg-gray-100 rounded-lg overflow-hidden"
                            >
                                <img
                                    :src="image.image_path"
                                    :alt="image.alt_text || blog.title"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    v-if="image.caption"
                                    class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-2 text-sm"
                                >
                                    {{ image.caption }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Share Buttons -->
                    <div
                        class="flex items-center justify-between py-6 border-t border-b border-gray-200 mb-8"
                    >
                        <span class="text-gray-700 font-medium"
                            >Share this article:</span
                        >
                        <div class="flex space-x-3">
                            <button
                                @click="shareBlog('facebook')"
                                class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition-colors"
                                title="Share on Facebook"
                            >
                                <i class="fab fa-facebook-f"></i>
                            </button>
                            <button
                                @click="shareBlog('twitter')"
                                class="w-10 h-10 rounded-full bg-sky-500 text-white flex items-center justify-center hover:bg-sky-600 transition-colors"
                                title="Share on Twitter"
                            >
                                <i class="fab fa-twitter"></i>
                            </button>
                            <button
                                @click="shareBlog('linkedin')"
                                class="w-10 h-10 rounded-full bg-blue-700 text-white flex items-center justify-center hover:bg-blue-800 transition-colors"
                                title="Share on LinkedIn"
                            >
                                <i class="fab fa-linkedin-in"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Related Products -->
                    <div v-if="relatedProducts.length > 0" class="mt-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">
                            Related Products
                        </h3>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                        >
                            <div
                                v-for="product in relatedProducts"
                                :key="product.id"
                                class="bg-neutral-light rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200 cursor-pointer border border-gray-200"
                                @click="viewProduct(product)"
                            >
                                <!-- Product Image -->
                                <div
                                    class="aspect-w-16 aspect-h-12 bg-gray-100"
                                >
                                    <img
                                        v-if="product.primary_image"
                                        :src="product.primary_image.path"
                                        :alt="product.name"
                                        class="w-full h-48 object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400"
                                    >
                                        <i class="fa fa-image text-4xl"></i>
                                    </div>
                                </div>

                                <!-- Product Info -->
                                <div class="p-4">
                                    <h4
                                        class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2"
                                    >
                                        {{ product.name }}
                                    </h4>

                                    <p
                                        v-if="product.short_description"
                                        class="text-sm text-gray-600 mb-3 line-clamp-2"
                                    >
                                        {{ product.short_description }}
                                    </p>

                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <PriceDisplay
                                            :amount="product.price"
                                            :sale-price="product.sale_price"
                                            :show-discount="true"
                                        />

                                        <span
                                            class="text-primary-green hover:text-primary-dark font-medium text-sm inline-flex items-center"
                                        >
                                            View Product
                                            <i
                                                class="fa fa-arrow-right ml-1"
                                            ></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <!-- Error State -->
        <div v-else class="container mx-auto px-4 py-12 text-center">
            <i
                class="fa fa-exclamation-triangle text-6xl text-gray-300 mb-4"
            ></i>
            <p class="text-gray-500 text-lg">Blog not found</p>
        </div>
    </div>
</template>

<style scoped>
/* Prose styles for blog content */
.prose {
    color: #374151;
}

.prose h2 {
    font-size: 1.875rem;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.prose h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.prose p {
    margin-bottom: 1rem;
    line-height: 1.75;
}

.prose ul,
.prose ol {
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}

.prose li {
    margin-bottom: 0.5rem;
}

.prose a {
    color: #10b981;
    text-decoration: underline;
}

.prose a:hover {
    color: #059669;
}

.prose img {
    border-radius: 0.5rem;
    margin: 1.5rem 0;
}

.prose blockquote {
    border-left: 4px solid #10b981;
    padding-left: 1rem;
    font-style: italic;
    color: #6b7280;
    margin: 1.5rem 0;
}
</style>
