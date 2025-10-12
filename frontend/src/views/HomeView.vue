<template>
    <div class="min-h-screen">
        <!-- Hero Section -->
        <section
            class="relative bg-gradient-to-br from-neutral-white via-neutral-light to-white py-20 overflow-hidden"
        >
            <!-- Background decorative elements -->
            <div class="absolute inset-0 opacity-10">
                <div
                    class="absolute top-10 left-10 w-32 h-32 bg-primary rounded-full"
                ></div>
                <div
                    class="absolute bottom-20 right-20 w-24 h-24 bg-secondary rounded-full"
                ></div>
                <div
                    class="absolute top-1/2 left-1/3 w-16 h-16 bg-accent rounded-full"
                ></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-12">
                    <h1
                        class="text-4xl md:text-6xl font-display font-bold mb-6 text-neutral-charcoal"
                    >
                        Power Your Future with
                        <span
                            class="text-gradient bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent"
                        >
                            Clean Energy
                        </span>
                    </h1>

                    <p
                        class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto text-gray-600 leading-relaxed"
                    >
                        Discover our cutting-edge solar technology solutions for
                        homes and businesses.
                        <br class="hidden md:block" />
                        Join the renewable energy revolution today with
                        <strong class="text-primary"
                            >Eco Power Tech Global</strong
                        >.
                    </p>

                    <div
                        class="flex flex-col sm:flex-row gap-4 justify-center items-center"
                    >
                        <router-link
                            to="/products"
                            class="btn-primary inline-flex items-center space-x-2 hover-lift"
                        >
                            <span>Shop Products</span>
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3"
                                ></path>
                            </svg>
                        </router-link>
                        <button
                            class="btn-outline inline-flex items-center space-x-2 hover-lift"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"
                                ></path>
                            </svg>
                            <span>Learn More</span>
                        </button>
                    </div>
                </div>

                <!-- Hero Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16">
                    <div
                        class="text-center p-6 bg-white/50 backdrop-blur-sm rounded-xl border border-primary/10"
                    >
                        <div class="text-3xl font-bold text-primary mb-2">
                            1000+
                        </div>
                        <div class="text-gray-600">Happy Customers</div>
                    </div>
                    <div
                        class="text-center p-6 bg-white/50 backdrop-blur-sm rounded-xl border border-secondary/10"
                    >
                        <div
                            class="text-3xl font-bold text-secondary-dark mb-2"
                        >
                            50MW+
                        </div>
                        <div class="text-gray-600">Solar Power Generated</div>
                    </div>
                    <div
                        class="text-center p-6 bg-white/50 backdrop-blur-sm rounded-xl border border-accent/10"
                    >
                        <div class="text-3xl font-bold text-accent mb-2">
                            5 Years
                        </div>
                        <div class="text-gray-600">Industry Experience</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2
                        class="text-3xl font-display font-bold text-neutral-charcoal mb-4"
                    >
                        Featured Products
                    </h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Explore our most popular solar solutions designed for
                        efficiency and sustainability.
                    </p>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="text-center py-12">
                    <i class="fa fa-spinner fa-spin text-4xl text-primary"></i>
                    <p class="text-gray-600 mt-4">Loading products...</p>
                </div>

                <!-- Featured Products -->
                <div
                    v-else-if="featuredProducts.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                >
                    <div
                        v-for="product in featuredProducts"
                        :key="product.id"
                        class="card text-center hover-lift"
                    >
                        <router-link :to="`/products/${product.slug}`">
                            <div
                                class="h-48 bg-neutral-light rounded-lg mb-4 flex items-center justify-center overflow-hidden"
                            >
                                <img
                                    v-if="product.primary_image"
                                    :src="`/storage/${product.primary_image.path}`"
                                    :alt="
                                        product.primary_image.alt_text ||
                                        product.name
                                    "
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-gray-500">
                                    <i class="fa fa-image text-4xl"></i>
                                </span>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">
                                {{ product.name }}
                            </h3>
                            <p class="text-gray-600 mb-4 line-clamp-2">
                                {{
                                    product.short_description ||
                                    "High-quality solar solution"
                                }}
                            </p>
                        </router-link>
                        <div class="flex justify-between items-center">
                            <div class="text-2xl font-bold">
                                <PriceDisplay
                                    :amount="product.price"
                                    :sale-price="
                                        product.is_on_sale
                                            ? product.sale_price
                                            : null
                                    "
                                    :show-discount="true"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Products -->
                <div v-else class="text-center py-12 text-gray-500">
                    <i class="fa fa-box-open text-6xl mb-4 text-gray-300"></i>
                    <p>No featured products available at the moment</p>
                </div>

                <div class="text-center mt-12">
                    <router-link to="/products" class="btn-outline">
                        View All Products
                    </router-link>
                </div>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="py-16 bg-neutral-light">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2
                        class="text-3xl font-display font-bold text-neutral-charcoal mb-4"
                    >
                        Why Choose Eco Power Tech Global?
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4"
                        >
                            <span class="text-white text-2xl">🌱</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Eco-Friendly</h3>
                        <p class="text-gray-600">
                            100% renewable energy solutions for a sustainable
                            future
                        </p>
                    </div>

                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-secondary rounded-full flex items-center justify-center mx-auto mb-4"
                        >
                            <span class="text-neutral-charcoal text-2xl"
                                >⚡</span
                            >
                        </div>
                        <h3 class="text-xl font-semibold mb-2">
                            High Efficiency
                        </h3>
                        <p class="text-gray-600">
                            Advanced technology for maximum energy conversion
                        </p>
                    </div>

                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-accent rounded-full flex items-center justify-center mx-auto mb-4"
                        >
                            <span class="text-white text-2xl">🛡️</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">
                            Reliable Support
                        </h3>
                        <p class="text-gray-600">
                            Comprehensive warranty and expert customer service
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { productService } from "@/services/productService";

const loading = ref(false);
const featuredProducts = ref([]);

/**
 * Load featured products
 */
async function loadFeaturedProducts() {
    loading.value = true;
    try {
        const response = await productService.getProducts({
            featured: true,
            per_page: 6,
        });
        featuredProducts.value = response.data || [];
    } catch (error) {
        console.error("Error loading featured products:", error);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadFeaturedProducts();
});
</script>
