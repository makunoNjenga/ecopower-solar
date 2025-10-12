<template>
    <div class="min-h-screen bg-neutral-light">
        <div class="container mx-auto px-4 py-8">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
                <div
                    class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
                ></div>
                <p class="mt-4 text-gray-600">Loading product details...</p>
            </div>

            <!-- Product Not Found -->
            <div v-else-if="!product" class="text-center py-12">
                <div class="text-gray-400 mb-4">
                    <i class="fa fa-exclamation-triangle text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    Product not found
                </h3>
                <p class="text-gray-600 mb-4">
                    The product you're looking for doesn't exist or has been
                    removed.
                </p>
                <router-link to="/products" class="btn-primary">
                    Browse All Products
                </router-link>
            </div>

            <!-- Product Details -->
            <div v-else>
                <!-- Breadcrumb -->
                <nav class="mb-6">
                    <ol
                        class="flex items-center space-x-2 text-sm text-gray-600"
                    >
                        <li>
                            <router-link to="/" class="hover:text-primary"
                                >Home</router-link
                            >
                        </li>
                        <li><i class="fa fa-chevron-right text-xs"></i></li>
                        <li>
                            <router-link
                                to="/products"
                                class="hover:text-primary"
                                >Products</router-link
                            >
                        </li>
                        <li v-if="product.category">
                            <i class="fa fa-chevron-right text-xs"></i>
                        </li>
                        <li v-if="product.category">
                            <router-link
                                :to="`/products?category=${product.category.id}`"
                                class="hover:text-primary"
                            >
                                {{ product.category.name }}
                            </router-link>
                        </li>
                        <li><i class="fa fa-chevron-right text-xs"></i></li>
                        <li class="text-gray-900">{{ product.name }}</li>
                    </ol>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Product Images -->
                    <div class="space-y-4">
                        <!-- Main Image -->
                        <div
                            class="aspect-w-16 aspect-h-12 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
                        >
                            <img
                                v-if="selectedImage"
                                :src="selectedImage.url"
                                :alt="selectedImage.alt_text || product.name"
                                class="w-full h-96 object-cover"
                            />
                            <div
                                v-else
                                class="w-full h-96 bg-gray-100 flex items-center justify-center text-gray-400"
                            >
                                <div class="text-center">
                                    <i class="fa fa-image text-6xl mb-4"></i>
                                    <p class="text-lg">{{ product.name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Thumbnail Images -->
                        <div
                            v-if="product.images && product.images.length > 1"
                            class="grid grid-cols-4 gap-2"
                        >
                            <button
                                v-for="image in product.images"
                                :key="image.id"
                                @click="selectedImage = image"
                                :class="[
                                    'aspect-w-16 aspect-h-12 rounded-lg border-2 overflow-hidden',
                                    selectedImage &&
                                    selectedImage.id === image.id
                                        ? 'border-primary'
                                        : 'border-gray-200 hover:border-gray-300',
                                ]"
                            >
                                <img
                                    :src="image.url"
                                    :alt="image.alt_text || product.name"
                                    class="w-full h-20 object-cover"
                                />
                            </button>
                        </div>
                    </div>

                    <!-- Product Information -->
                    <div class="space-y-6">
                        <!-- Product Title and Category -->
                        <div>
                            <div v-if="product.category" class="mb-2">
                                <span
                                    class="inline-block px-3 py-1 text-sm bg-primary-light text-primary-dark rounded-full"
                                >
                                    {{ product.category.name }}
                                </span>
                            </div>
                            <h1
                                class="text-3xl font-display font-bold text-neutral-charcoal mb-4"
                            >
                                {{ product.name }}
                            </h1>
                            <div
                                class="text-lg text-gray-600 leading-relaxed"
                                v-html="product.description"
                            ></div>
                        </div>

                        <!-- Price Section -->
                        <div
                            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <span
                                        v-if="
                                            product.original_price &&
                                            product.original_price !==
                                                product.price
                                        "
                                        class="text-xl text-gray-500 line-through"
                                    >
                                        KSh
                                        {{
                                            formatPrice(product.original_price)
                                        }}
                                    </span>
                                    <span
                                        :class="[
                                            'text-3xl font-bold',
                                            product.original_price &&
                                            product.original_price !==
                                                product.price
                                                ? 'text-primary'
                                                : 'text-neutral-charcoal',
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
                                    class="bg-red-500 text-white text-lg font-bold px-4 py-2 rounded-lg"
                                >
                                    -{{
                                        calculateDiscountPercentage(
                                            product.original_price,
                                            product.price
                                        )
                                    }}%
                                </div>
                            </div>

                            <!-- Stock Status -->
                            <div class="flex items-center space-x-2 mb-6">
                                <div
                                    :class="[
                                        'w-3 h-3 rounded-full',
                                        product.stock_quantity > 0
                                            ? 'bg-green-500'
                                            : 'bg-red-500',
                                    ]"
                                ></div>
                                <span
                                    :class="[
                                        'font-medium',
                                        product.stock_quantity > 0
                                            ? 'text-green-700'
                                            : 'text-red-700',
                                    ]"
                                >
                                    {{
                                        product.stock_quantity > 0
                                            ? "In Stock"
                                            : "Out of Stock"
                                    }}
                                </span>
                                <span
                                    v-if="product.stock_quantity > 0"
                                    class="text-gray-600"
                                >
                                    ({{ product.stock_quantity }} available)
                                </span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-3">
                                <button
                                    @click="contactForQuote"
                                    class="w-full btn-primary py-3 text-lg"
                                >
                                    <i class="fa fa-envelope mr-2"></i>
                                    Contact for Quote
                                </button>
                            </div>
                        </div>

                        <!-- Product Specifications -->
                        <div
                            v-if="product.specifications"
                            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                        >
                            <h3
                                class="text-xl font-semibold text-neutral-charcoal mb-4"
                            >
                                Specifications
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div
                                    v-for="(
                                        value, key
                                    ) in product.specifications"
                                    :key="key"
                                    class="flex justify-between py-2 border-b border-gray-100"
                                >
                                    <span
                                        class="font-medium text-gray-700 capitalize"
                                    >
                                        {{ key.replace(/_/g, " ") }}
                                    </span>
                                    <span class="text-gray-900">{{
                                        value
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Product Features -->
                        <div
                            v-if="
                                product.features && product.features.length > 0
                            "
                            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                        >
                            <h3
                                class="text-xl font-semibold text-neutral-charcoal mb-4"
                            >
                                Key Features
                            </h3>
                            <ul class="space-y-2">
                                <li
                                    v-for="feature in product.features"
                                    :key="feature"
                                    class="flex items-start space-x-2"
                                >
                                    <i
                                        class="fa fa-check text-primary mt-1"
                                    ></i>
                                    <span class="text-gray-700">{{
                                        feature
                                    }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <div v-if="relatedProducts.length > 0" class="mt-16">
                    <h2
                        class="text-2xl font-display font-bold text-neutral-charcoal mb-8"
                    >
                        Related Products
                    </h2>
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"
                    >
                        <div
                            v-for="relatedProduct in relatedProducts"
                            :key="relatedProduct.id"
                            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer"
                            @click="viewProduct(relatedProduct)"
                        >
                            <div class="aspect-w-16 aspect-h-12 bg-gray-100">
                                <img
                                    v-if="relatedProduct.primary_image"
                                    :src="relatedProduct.primary_image.url"
                                    :alt="
                                        relatedProduct.primary_image.alt_text ||
                                        relatedProduct.name
                                    "
                                    class="w-full h-48 object-cover"
                                />
                                <div
                                    v-else
                                    class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400"
                                >
                                    <i class="fa fa-image text-2xl"></i>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3
                                    class="font-semibold text-gray-900 mb-2 line-clamp-2"
                                >
                                    {{ relatedProduct.name }}
                                </h3>
                                <p class="text-primary font-bold">
                                    KSh {{ formatPrice(relatedProduct.price) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agent Contact Modal -->
            <div
                v-if="showAgentModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="closeAgentModal"
            >
                <div
                    class="bg-white rounded-lg p-8 max-w-md w-full shadow-2xl relative"
                >
                    <button
                        @click="closeAgentModal"
                        class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition-colors"
                        type="button"
                        title="Close"
                    >
                        <i class="fa fa-times text-2xl"></i>
                    </button>

                    <div class="text-center mb-6">
                        <div
                            class="w-20 h-20 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-4"
                        >
                            <i class="fa fa-user text-4xl text-primary"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">
                            Contact Agent
                        </h3>
                        <p class="text-gray-600">
                            Get in touch for a custom quote
                        </p>
                    </div>

                    <div
                        v-if="product.agent"
                        class="bg-gray-50 rounded-lg p-6 mb-6"
                    >
                        <div class="text-center mb-4">
                            <p class="text-lg font-semibold text-gray-900">
                                {{ product.agent.name }}
                            </p>
                            <p class="text-sm text-gray-600">Product Agent</p>
                        </div>

                        <div v-if="product.agent.phone" class="space-y-3">
                            <div class="text-center text-gray-700 mb-4">
                                <i class="fa fa-phone mr-2 text-primary"></i>
                                <span class="font-medium">{{
                                    product.agent.phone
                                }}</span>
                            </div>

                            <button
                                @click="callAgent(product.agent.phone)"
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center"
                            >
                                <i class="fa fa-phone mr-2"></i>
                                Call Now
                            </button>

                            <button
                                @click="openWhatsApp(product.agent.phone)"
                                class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors duration-200 flex items-center justify-center"
                            >
                                <i class="fab fa-whatsapp mr-2"></i>
                                WhatsApp
                            </button>
                        </div>

                        <div v-else class="text-center text-gray-500">
                            <p>No phone number available</p>
                        </div>
                    </div>

                    <div
                        v-else
                        class="bg-yellow-50 rounded-lg p-6 mb-6 text-center"
                    >
                        <i
                            class="fa fa-exclamation-triangle text-yellow-500 text-3xl mb-2"
                        ></i>
                        <p class="text-gray-700">
                            No agent assigned to this product
                        </p>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            Product:
                            <span class="font-medium">{{ product.name }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { productService } from "@/services/productService";

const route = useRoute();
const router = useRouter();

// Reactive data
const product = ref(null);
const selectedImage = ref(null);
const relatedProducts = ref([]);
const loading = ref(false);
const showAgentModal = ref(false);

/**
 * Load product details
 */
async function loadProduct() {
    loading.value = true;
    try {
        const slug = route.params.slug;
        const response = await productService.getProduct(slug);
        product.value = response.data || response;

        // Set primary image as selected
        if (product.value.primary_image) {
            selectedImage.value = product.value.primary_image;
        } else if (product.value.images && product.value.images.length > 0) {
            selectedImage.value = product.value.images[0];
        }

        // Load related products
        await loadRelatedProducts();
    } catch (error) {
        console.error("Error loading product:", error);
        product.value = null;
    } finally {
        loading.value = false;
    }
}

/**
 * Load related products
 */
async function loadRelatedProducts() {
    try {
        const response = await productService.getProducts({
            category_id: product.value.category?.id,
            per_page: 4,
            exclude: product.value.id,
        });
        relatedProducts.value = response.data || [];
    } catch (error) {
        console.error("Error loading related products:", error);
        relatedProducts.value = [];
    }
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
 * Contact for quote
 */
function contactForQuote() {
    showAgentModal.value = true;
}

/**
 * Close agent modal
 */
function closeAgentModal() {
    showAgentModal.value = false;
}

/**
 * Call agent phone
 */
function callAgent(phone) {
    window.location.href = `tel:${phone}`;
}

/**
 * Open WhatsApp
 */
function openWhatsApp(phone) {
    const cleanPhone = phone.replace(/[^\d+]/g, "");
    const message = encodeURIComponent(
        `Hi, I'm interested in ${product.value.name}`
    );
    window.open(`https://wa.me/${cleanPhone}?text=${message}`, "_blank");
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

// Watch for route changes
watch(
    () => route.params.slug,
    () => {
        loadProduct();
    }
);

// Lifecycle
onMounted(() => {
    loadProduct();
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
