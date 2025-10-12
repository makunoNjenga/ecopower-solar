<template>
  <footer class="bg-neutral-charcoal text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Company Info -->
        <div class="col-span-1 md:col-span-2">
          <div class="flex items-center space-x-3 mb-4">
            <img src="/logo.svg" alt="Eco Power Tech Global" class="h-10 w-auto filter brightness-0 invert" />
            <span class="text-xl font-display font-bold">Eco Power Tech Global</span>
          </div>
          <p class="text-gray-300 mb-4">
            Leading provider of eco-friendly solar technology solutions for homes and businesses.
            Committed to a sustainable future through innovative renewable energy products.
          </p>
          <div class="flex space-x-4">
            <a href="#" class="text-gray-300 hover:text-white transition-colors">
              <span class="sr-only">Facebook</span>
              <!-- Add social icons here -->
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div>
          <h3 class="text-lg font-display font-semibold mb-4">Quick Links</h3>
          <ul class="space-y-2">
            <li><router-link to="/" class="footer-link">Home</router-link></li>
            <li><router-link to="/products" class="footer-link">Products</router-link></li>
            <li><a href="#" class="footer-link">About Us</a></li>
            <li><a href="#" class="footer-link">Contact</a></li>
          </ul>
        </div>

        <!-- Customer Service -->
        <div>
          <h3 class="text-lg font-display font-semibold mb-4">Customer Service</h3>
          <ul class="space-y-2">
            <li><a href="#" class="footer-link">Support Center</a></li>
            <li><a href="#" class="footer-link">Shipping Info</a></li>
            <li><a href="#" class="footer-link">Returns</a></li>
            <li><a href="#" class="footer-link">Warranty</a></li>
          </ul>
        </div>
      </div>

      <div class="border-t border-gray-700 pt-8 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <p class="text-gray-300 text-sm">
            &copy; {{ currentYear }} Eco Power Tech Global. All rights reserved.
          </p>
          <div class="flex space-x-6 mt-4 md:mt-0">
            <a href="#" class="footer-link text-sm">Privacy Policy</a>
            <a href="#" class="footer-link text-sm">Terms of Service</a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
.footer-link {
  @apply text-gray-300 hover:text-white transition-colors duration-200;
}
</style>