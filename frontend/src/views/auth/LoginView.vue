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
import { ref, computed } from "vue";
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

const isLoading = computed(() => appStore.isLoading);

const handleLogin = async () => {
    const result = await authStore.login({
        email: form.value.email,
        password: form.value.password,
    });

    if (result.success) {
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
