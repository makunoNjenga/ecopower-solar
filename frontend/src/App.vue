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
import { computed } from 'vue'
import { RouterView } from 'vue-router'
import { useAppStore } from '@/stores/app'
import NavBar from '@/components/layout/NavBar.vue'
import Footer from '@/components/layout/Footer.vue'
import LoadingOverlay from '@/components/ui/LoadingOverlay.vue'
import ToastNotifications from '@/components/ui/ToastNotifications.vue'

export default {
  name: 'App',
  components: {
    RouterView,
    NavBar,
    Footer,
    LoadingOverlay,
    ToastNotifications
  },
  setup() {
    const appStore = useAppStore()

    const isLoading = computed(() => appStore.isLoading)

    return {
      isLoading
    }
  }
}
</script>