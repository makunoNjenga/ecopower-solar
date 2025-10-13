<template>
    <div id="app" class="min-h-screen bg-neutral-light">
        <!-- Navigation -->
        <NavBar />

        <!-- Main Content -->
        <main class="min-h-screen">
            <RouterView />
        </main>

        <!-- Footer -->
        <Footer />

        <!-- Loading Overlay -->
        <LoadingOverlay v-if="isLoading" />

        <!-- Toast Notifications -->
        <ToastNotifications />
    </div>
</template>

<script>
import { computed, watch } from "vue";
import { RouterView } from "vue-router";
import { useAppStore } from "@/stores/app";
import { useAuthStore } from "@/stores/auth";
import { useSessionGuard } from "@/composables/useSessionGuard";
import NavBar from "@/components/layout/NavBar.vue";
import Footer from "@/components/layout/Footer.vue";
import LoadingOverlay from "@/components/ui/LoadingOverlay.vue";
import ToastNotifications from "@/components/ui/ToastNotifications.vue";

export default {
    name: "App",
    components: {
        RouterView,
        NavBar,
        Footer,
        LoadingOverlay,
        ToastNotifications,
    },
    setup() {
        const appStore = useAppStore();
        const authStore = useAuthStore();

        // Initialize session guard with 3-minute check interval
        const { startSessionMonitoring, stopSessionMonitoring } =
            useSessionGuard(3 * 60 * 1000);

        const isLoading = computed(() => appStore.isLoading);

        // Watch authentication state and start/stop monitoring accordingly
        watch(
            () => authStore.isAuthenticated,
            (isAuthenticated) => {
                if (isAuthenticated) {
                    startSessionMonitoring();
                } else {
                    stopSessionMonitoring();
                }
            },
            { immediate: true }
        );

        return {
            isLoading,
        };
    },
};
</script>
