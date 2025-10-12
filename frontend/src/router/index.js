import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Import views
import HomeView from '@/views/HomeView.vue'
import ProductsView from '@/views/ProductsView.vue'
import ProductDetailView from '@/views/ProductDetailView.vue'
import BlogsListView from '@/views/BlogsListView.vue'
import BlogDetailView from '@/views/BlogDetailView.vue'
import CheckoutView from '@/views/CheckoutView.vue'
import LoginView from '@/views/auth/LoginView.vue'
import RegisterView from '@/views/auth/RegisterView.vue'
import ProfileView from '@/views/user/ProfileView.vue'
import OrdersView from '@/views/user/OrdersView.vue'
import OrderDetailView from '@/views/user/OrderDetailView.vue'
import AdminDashboard from '@/views/admin/DashboardView.vue'
import AdminProducts from '@/views/admin/ProductsView.vue'
import AdminProductImages from '@/views/admin/ProductImagesView.vue'
import AdminBlogs from '@/views/admin/BlogsView.vue'
import AdminBlogForm from '@/views/admin/BlogFormView.vue'
import AdminBlogImages from '@/views/admin/BlogImagesView.vue'
import AdminOrders from '@/views/admin/OrdersView.vue'
import AdminUsers from '@/views/admin/UsersView.vue'
import AdminCategories from '@/views/admin/CategoriesView.vue'
import NotFoundView from '@/views/NotFoundView.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
    meta: { title: 'Home - Eco Power Tech Global' }
  },
  {
    path: '/products',
    name: 'products',
    component: ProductsView,
    meta: { title: 'Products - Eco Power Tech Global' }
  },
  {
    path: '/products/:slug',
    name: 'product-detail',
    component: ProductDetailView,
    props: true,
    meta: { title: 'Product Details - Eco Power Tech Global' }
  },
  {
    path: '/blogs',
    name: 'blogs',
    component: BlogsListView,
    meta: { title: 'Blog - Eco Power Tech Global' }
  },
  {
    path: '/blogs/:slug',
    name: 'blog-detail',
    component: BlogDetailView,
    props: true,
    meta: { title: 'Blog - Eco Power Tech Global' }
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: CheckoutView,
    meta: {
      title: 'Checkout - Eco Power Tech Global',
      requiresAuth: true
    }
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: {
      title: 'Login - Eco Power Tech Global',
      guestOnly: true
    }
  },
  // Registration disabled
  // {
  //   path: '/register',
  //   name: 'register',
  //   component: RegisterView,
  //   meta: {
  //     title: 'Register - Eco Power Tech Global',
  //     guestOnly: true
  //   }
  // },
  {
    path: '/profile',
    name: 'profile',
    component: ProfileView,
    meta: {
      title: 'My Profile - Eco Power Tech Global',
      requiresAuth: true
    }
  },
  {
    path: '/orders',
    name: 'orders',
    component: OrdersView,
    meta: {
      title: 'My Orders - Eco Power Tech Global',
      requiresAuth: true
    }
  },
  {
    path: '/orders/:id',
    name: 'order-detail',
    component: OrderDetailView,
    props: true,
    meta: {
      title: 'Order Details - Eco Power Tech Global',
      requiresAuth: true
    }
  },
  {
    path: '/admin',
    name: 'admin',
    component: AdminDashboard,
    meta: {
      title: 'Admin Dashboard - Eco Power Tech Global',
      requiresAuth: true,
      requiresAdmin: true
    }
  },
  {
    path: '/admin/products',
    name: 'admin-products',
    component: AdminProducts,
    meta: {
      title: 'Manage Products - Admin - Eco Power Tech Global',
      requiresAuth: true,
      requiresAdmin: true
    }
  },
  {
    path: '/admin/products/:id/images',
    name: 'admin-product-images',
    component: AdminProductImages,
    props: true,
    meta: {
      title: 'Manage Product Images - Admin - Eco Power Tech Global',
      requiresAuth: true,
      requiresAdmin: true
    }
  },
  {
    path: '/admin/orders',
    name: 'admin-orders',
    component: AdminOrders,
    meta: {
      title: 'Manage Orders - Admin - Eco Power Tech Global',
      requiresAuth: true,
      requiresAdmin: true
    }
  },
  {
    path: '/admin/users',
    name: 'admin-users',
    component: AdminUsers,
    meta: {
      title: 'Manage Users - Admin - Eco Power Tech Global',
      requiresAuth: true,
      requiresAdmin: true
    }
  },
  {
    path: '/admin/categories',
    name: 'admin-categories',
    component: AdminCategories,
    meta: {
      title: 'Manage Categories - Admin - Eco Power Tech Global',
      requiresAuth: true,
      requiresAdmin: true
    }
  },
  {
    path: '/admin/blogs',
    name: 'admin-blogs',
    component: AdminBlogs,
    meta: {
      title: 'Manage Blogs - Admin - Eco Power Tech Global',
      requiresAuth: true,
      requiresAdmin: true
    }
  },
  {
    path: '/admin/blogs/create',
    name: 'admin-blog-create',
    component: AdminBlogForm,
    meta: {
      title: 'Create Blog - Admin - Eco Power Tech Global',
      requiresAuth: true,
      requiresAdmin: true
    }
  },
  {
    path: '/admin/blogs/:id/edit',
    name: 'admin-blog-edit',
    component: AdminBlogForm,
    props: true,
    meta: {
      title: 'Edit Blog - Admin - Eco Power Tech Global',
      requiresAuth: true,
      requiresAdmin: true
    }
  },
  {
    path: '/admin/blogs/:id/images',
    name: 'admin-blog-images',
    component: AdminBlogImages,
    props: true,
    meta: {
      title: 'Manage Blog Images - Admin - Eco Power Tech Global',
      requiresAuth: true,
      requiresAdmin: true
    }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFoundView,
    meta: { title: 'Page Not Found - Eco Power Tech Global' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Wait for auth initialization
  if (!authStore.isInitialized) {
    await authStore.initialize()
  }

  // Set page title
  document.title = to.meta.title || 'Eco Power Tech Global'

  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  // Check if route is for guests only
  if (to.meta.guestOnly && authStore.isAuthenticated) {
    next({ name: 'home' })
    return
  }

  // Check if route requires admin access
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next({ name: 'home' })
    return
  }

  next()
})

export default router