<template>
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 md:h-16">
                <!-- Logo and Brand -->
                <div class="flex items-center">
                    <router-link to="/" class="flex items-center space-x-3">
                        <img
                            src="/logo.svg"
                            alt="Eco Power Tech Global"
                            class="h-14 md:h-10 w-auto"
                        />
                        <span
                            class="hidden md:inline text-xl font-display font-bold text-primary"
                            >Eco Power Tech Global</span
                        >
                    </router-link>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex md:items-center md:space-x-8">
                    <router-link to="/" class="nav-link">Home</router-link>
                    <router-link to="/products" class="nav-link"
                        >Products</router-link
                    >
                    <router-link to="/blogs" class="nav-link">Blog</router-link>

                    <!-- Auth Menu -->
                    <div v-if="authStore.isAuthenticated" class="relative">
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="flex items-center space-x-1 nav-link"
                        >
                            <UserCircleIcon class="h-6 w-6" />
                            <span>{{ authStore.user?.name }}</span>
                            <ChevronDownIcon class="h-4 w-4" />
                        </button>

                        <!-- User Dropdown -->
                        <div
                            v-if="showUserMenu"
                            @click.away="showUserMenu = false"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
                        >
                            <router-link to="/profile" class="dropdown-item"
                                >Profile</router-link
                            >
                            <div
                                v-if="authStore.isAdmin"
                                class="border-t border-gray-100"
                            >
                                <router-link to="/admin" class="dropdown-item"
                                    >Admin Dashboard</router-link
                                >
                            </div>
                            <div class="border-t border-gray-100">
                                <button
                                    @click="handleLogout"
                                    class="dropdown-item w-full text-left"
                                >
                                    Logout
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Login -->
                    <div v-else>
                        <router-link to="/login" class="btn-primary"
                            >Login</router-link
                        >
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button
                        @click="showMobileMenu = !showMobileMenu"
                        class="nav-link"
                    >
                        <Bars3Icon v-if="!showMobileMenu" class="h-6 w-6" />
                        <XMarkIcon v-else class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div
                v-if="showMobileMenu"
                class="md:hidden border-t border-gray-200 py-4"
            >
                <div class="space-y-4">
                    <router-link to="/" class="block nav-link"
                        >Home</router-link
                    >
                    <router-link to="/products" class="block nav-link"
                        >Products</router-link
                    >
                    <router-link to="/blogs" class="block nav-link"
                        >Blog</router-link
                    >

                    <div
                        v-if="authStore.isAuthenticated"
                        class="space-y-2 border-t pt-4"
                    >
                        <router-link to="/profile" class="block nav-link"
                            >Profile</router-link
                        >
                        <router-link
                            v-if="authStore.isAdmin"
                            to="/admin"
                            class="block nav-link"
                            >Admin</router-link
                        >
                        <button
                            @click="handleLogout"
                            class="block nav-link w-full text-left"
                        >
                            Logout
                        </button>
                    </div>

                    <div v-else class="space-y-2 border-t pt-4">
                        <router-link to="/login" class="block nav-link"
                            >Login</router-link
                        >
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import {
    UserCircleIcon,
    ChevronDownIcon,
    Bars3Icon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";

const router = useRouter();
const authStore = useAuthStore();

const showUserMenu = ref(false);
const showMobileMenu = ref(false);

const handleLogout = async () => {
    await authStore.logout();
    router.push("/");
    showUserMenu.value = false;
    showMobileMenu.value = false;
};
</script>

<style scoped>
.nav-link {
    @apply text-neutral-charcoal hover:text-primary transition-colors duration-200 font-medium;
}

.dropdown-item {
    @apply block px-4 py-2 text-sm text-neutral-charcoal hover:bg-neutral-light transition-colors duration-200;
}

.router-link-active {
    @apply text-primary;
}
</style>
