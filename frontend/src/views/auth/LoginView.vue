<template>
    <div
        class="min-h-screen flex items-center justify-center bg-neutral-light py-12 px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-md w-full space-y-8">
            <div>
                <img
                    class="mx-auto h-24 w-auto"
                    src="/logo.svg"
                    alt="Eco Power Tech Global"
                />
                <h2
                    class="mt-6 text-center text-3xl font-display font-bold text-neutral-charcoal"
                >
                    Sign in to your account
                </h2>
            </div>

            <!-- Session Expired Alert -->
            <div
                v-if="sessionExpired"
                class="rounded-md bg-yellow-50 border border-yellow-200 p-4"
            >
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">
                            {{ sessionMessage }}
                        </p>
                    </div>
                </div>
            </div>

            <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="input-field"
                            placeholder="Email address"
                            :disabled="isLoading"
                        />
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="input-field"
                            placeholder="Password"
                            :disabled="isLoading"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember-me"
                            v-model="form.remember"
                            name="remember-me"
                            type="checkbox"
                            class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                        />
                        <label
                            for="remember-me"
                            class="ml-2 block text-sm text-gray-900"
                        >
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a
                            href="#"
                            class="font-medium text-primary hover:text-primary-dark"
                        >
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span
                            v-if="isLoading"
                            class="absolute left-0 inset-y-0 flex items-center pl-3"
                        >
                            <div
                                class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"
                            ></div>
                        </span>
                        {{ isLoading ? "Signing in..." : "Sign in" }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useAppStore } from "@/stores/app";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const appStore = useAppStore();

const form = ref({
    email: "",
    password: "",
    remember: false,
});

const sessionExpired = ref(false);
const sessionMessage = ref("");

const isLoading = computed(() => appStore.isLoading);

// Check for session expiry message from query params
onMounted(() => {
    if (route.query.expired === "true" && route.query.message) {
        sessionExpired.value = true;
        sessionMessage.value = route.query.message;

        // Clear the query params from URL after displaying message
        router.replace({ path: route.path });
    }
});

const handleLogin = async () => {
    const result = await authStore.login({
        email: form.value.email,
        password: form.value.password,
    });

    if (result.success) {
        // Clear session expired flag
        sessionExpired.value = false;

        // Redirect based on user role
        let redirectTo = route.query.redirect;

        if (!redirectTo) {
            // If no redirect specified, send admin to dashboard, regular users to home
            redirectTo = authStore.isAdmin ? "/admin" : "/";
        }

        router.push(redirectTo);
    }
};
</script>
