<template>
    <div class="min-h-screen bg-neutral-light py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1
                    class="text-3xl font-display font-bold text-neutral-charcoal"
                >
                    My Profile
                </h1>
                <p class="mt-2 text-gray-600">
                    Manage your personal information and security settings
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <!-- Profile Information Card -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-neutral-charcoal">
                            Personal Information
                        </h2>
                    </div>

                    <form
                        @submit.prevent="handleUpdateProfile"
                        class="p-6 space-y-6"
                    >
                        <div>
                            <label
                                for="name"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Full Name
                            </label>
                            <input
                                id="name"
                                v-model="profileForm.name"
                                type="text"
                                required
                                class="input-field"
                                placeholder="Enter your full name"
                                :disabled="isLoading"
                            />
                        </div>

                        <div>
                            <label
                                for="email"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Email Address
                            </label>
                            <input
                                id="email"
                                v-model="profileForm.email"
                                type="email"
                                required
                                class="input-field"
                                placeholder="Enter your email address"
                                :disabled="isLoading"
                            />
                        </div>

                        <div>
                            <label
                                for="phone"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Phone Number
                            </label>
                            <input
                                id="phone"
                                v-model="profileForm.phone"
                                type="tel"
                                class="input-field"
                                placeholder="Enter your phone number (optional)"
                                :disabled="isLoading"
                            />
                        </div>

                        <div class="flex items-center justify-between pt-4">
                            <p class="text-sm text-gray-600">
                                Last updated: {{ formatDate(user?.updated_at) }}
                            </p>
                            <button
                                type="submit"
                                :disabled="isLoading || !isProfileChanged"
                                class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span
                                    v-if="isLoading"
                                    class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"
                                ></span>
                                {{
                                    isLoading ? "Updating..." : "Update Profile"
                                }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password Change Card -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-neutral-charcoal">
                            Change Password
                        </h2>
                    </div>

                    <form
                        @submit.prevent="handleUpdatePassword"
                        class="p-6 space-y-6"
                    >
                        <div>
                            <label
                                for="current-password"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Current Password
                            </label>
                            <input
                                id="current-password"
                                v-model="passwordForm.current_password"
                                type="password"
                                required
                                class="input-field"
                                placeholder="Enter your current password"
                                :disabled="isLoading"
                                autocomplete="current-password"
                            />
                        </div>

                        <div>
                            <label
                                for="new-password"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                New Password
                            </label>
                            <input
                                id="new-password"
                                v-model="passwordForm.password"
                                type="password"
                                required
                                class="input-field"
                                placeholder="Enter your new password"
                                :disabled="isLoading"
                                autocomplete="new-password"
                            />
                            <p class="mt-1 text-sm text-gray-500">
                                Password must be at least 8 characters long
                            </p>
                        </div>

                        <div>
                            <label
                                for="confirm-password"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Confirm New Password
                            </label>
                            <input
                                id="confirm-password"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                required
                                class="input-field"
                                placeholder="Confirm your new password"
                                :disabled="isLoading"
                                autocomplete="new-password"
                            />
                        </div>

                        <div class="flex items-center justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="isLoading || !isPasswordFormValid"
                                class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span
                                    v-if="isLoading"
                                    class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"
                                ></span>
                                {{
                                    isLoading
                                        ? "Updating..."
                                        : "Change Password"
                                }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Account Information Card -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-neutral-charcoal">
                            Account Information
                        </h2>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700"
                                >Account Status</span
                            >
                            <span
                                :class="[
                                    'px-3 py-1 rounded-full text-xs font-semibold',
                                    user?.is_active
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800',
                                ]"
                            >
                                {{ user?.is_active ? "Active" : "Inactive" }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700"
                                >Account Type</span
                            >
                            <span
                                :class="[
                                    'px-3 py-1 rounded-full text-xs font-semibold',
                                    user?.is_admin
                                        ? 'bg-primary-light text-primary-dark'
                                        : 'bg-gray-100 text-gray-800',
                                ]"
                            >
                                {{
                                    user?.is_admin
                                        ? "Administrator"
                                        : "Customer"
                                }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700"
                                >Member Since</span
                            >
                            <span class="text-sm text-gray-600">
                                {{ formatDate(user?.created_at) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useAppStore } from "@/stores/app";

const authStore = useAuthStore();
const appStore = useAppStore();

const profileForm = ref({
    name: "",
    email: "",
    phone: "",
});

const passwordForm = ref({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const originalProfile = ref({});

const user = computed(() => authStore.user);
const isLoading = computed(() => appStore.isLoading);

const isProfileChanged = computed(() => {
    return (
        profileForm.value.name !== originalProfile.value.name ||
        profileForm.value.email !== originalProfile.value.email ||
        profileForm.value.phone !== originalProfile.value.phone
    );
});

const isPasswordFormValid = computed(() => {
    return (
        passwordForm.value.current_password &&
        passwordForm.value.password &&
        passwordForm.value.password ===
            passwordForm.value.password_confirmation &&
        passwordForm.value.password.length >= 8
    );
});

/**
 * Formats a date string to a readable format
 */
const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

/**
 * Initializes the profile form with user data
 */
const initializeForm = () => {
    if (user.value) {
        profileForm.value = {
            name: user.value.name || "",
            email: user.value.email || "",
            phone: user.value.phone || "",
        };
        originalProfile.value = { ...profileForm.value };
    }
};

/**
 * Handles profile update submission
 */
const handleUpdateProfile = async () => {
    const result = await authStore.updateProfile(profileForm.value);

    if (result.success) {
        originalProfile.value = { ...profileForm.value };
    }
};

/**
 * Handles password update submission
 */
const handleUpdatePassword = async () => {
    if (
        passwordForm.value.password !== passwordForm.value.password_confirmation
    ) {
        appStore.showError("Passwords do not match");
        return;
    }

    const result = await authStore.updatePassword({
        current_password: passwordForm.value.current_password,
        password: passwordForm.value.password,
        password_confirmation: passwordForm.value.password_confirmation,
    });

    if (result.success) {
        passwordForm.value = {
            current_password: "",
            password: "",
            password_confirmation: "",
        };
    }
};

onMounted(() => {
    initializeForm();
});
</script>
