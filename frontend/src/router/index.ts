import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior(_to, _from, saved) {
    return saved ?? { top: 0 }
  },
  routes: [
    {
      path: '/',
      component: () => import('@/layouts/DefaultLayout.vue'),
      children: [
        { path: '', name: 'home', component: () => import('@/pages/HomePage.vue') },
        { path: 'about', name: 'about', component: () => import('@/pages/AboutPage.vue') },
        { path: 'products', name: 'products', component: () => import('@/pages/ProductsPage.vue') },
        { path: 'products/:slug', name: 'product', component: () => import('@/pages/ProductDetailPage.vue') },
        { path: 'brands', name: 'brands', component: () => import('@/pages/BrandsPage.vue') },
        { path: 'brands/:slug', name: 'brand', component: () => import('@/pages/BrandDetailPage.vue') },
        { path: 'solutions', name: 'solutions', component: () => import('@/pages/SolutionsPage.vue') },
        { path: 'solutions/:slug', name: 'solution', component: () => import('@/pages/SolutionDetailPage.vue') },
        { path: 'news', name: 'news', component: () => import('@/pages/NewsPage.vue') },
        { path: 'news/:slug', name: 'news-detail', component: () => import('@/pages/NewsDetailPage.vue') },
        { path: 'careers', name: 'careers', component: () => import('@/pages/CareersPage.vue') },
        { path: 'contact', name: 'contact', component: () => import('@/pages/ContactPage.vue') },
        { path: 'support', name: 'support', component: () => import('@/pages/SupportPage.vue') },
        { path: 'inquiry', name: 'inquiry', component: () => import('@/pages/InquiryPage.vue') },
        { path: 'login', name: 'login', component: () => import('@/pages/LoginPage.vue') },
        { path: 'register', name: 'register', component: () => import('@/pages/RegisterPage.vue') },
      ],
    },
    {
      path: '/portal',
      component: () => import('@/layouts/PortalLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        { path: '', name: 'portal', component: () => import('@/pages/portal/DashboardPage.vue') },
        { path: 'downloads', name: 'portal-downloads', component: () => import('@/pages/portal/DownloadsPage.vue') },
        { path: 'tickets', name: 'portal-tickets', component: () => import('@/pages/portal/TicketsPage.vue') },
        { path: 'repairs', name: 'portal-repairs', component: () => import('@/pages/portal/RepairsPage.vue') },
        { path: 'inquiries', name: 'portal-inquiries', component: () => import('@/pages/portal/InquiriesPage.vue') },
        { path: 'profile', name: 'portal-profile', component: () => import('@/pages/portal/ProfilePage.vue') },
      ],
    },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('@/pages/NotFoundPage.vue') },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
})

export default router
