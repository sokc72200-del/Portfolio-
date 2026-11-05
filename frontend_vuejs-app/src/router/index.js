import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

import PublicLayout from "@/layouts/PublicLayout.vue";
import AdminLayout from "@/layouts/AdminLayout.vue";

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    {
      path: "/",
      component: PublicLayout,
      children: [
        {
          path: "",
          name: "home",
          component: () => import("@/pages/public/HomePage.vue"),
        },
        {
          path: "projects",
          name: "projects",
          component: () => import("@/pages/public/ProjectsPage.vue"),
        },
        {
          path: "projects/:slug",
          name: "project-detail",
          component: () => import("@/pages/public/ProjectDetailPage.vue"),
        },
        {
          path: "about",
          name: "about",
          component: () => import("@/pages/public/AboutPage.vue"),
        },
        {
          path: "contact",
          name: "contact",
          component: () => import("@/pages/public/ContactPage.vue"),
        },
        { path: "login", redirect: { name: "admin-login" } },
      ],
    },
    {
      path: "/admin/login",
      name: "admin-login",
      component: () => import("@/pages/admin/loginPage.vue"),
      meta: { guestOnly: true },
    },
    {
      path: "/admin",
      component: AdminLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: "",
          name: "admin-dashboard",
          component: () => import("@/pages/admin/DashboardPage.vue"),
        },
        {
          path: "projects",
          name: "admin-projects",
          component: () => import("@/pages/admin/ProjectsListPage.vue"),
        },
        {
          path: "projects/new",
          name: "admin-project-create",
          component: () => import("@/pages/admin/ProjectFormPage.vue"),
        },
        {
          path: "projects/:slug/edit",
          name: "admin-project-edit",
          component: () => import("@/pages/admin/ProjectFormPage.vue"),
        },
        {
          path: "skills",
          name: "admin-skills",
          component: () => import("@/pages/admin/SkillsPage.vue"),
        },
        {
          path: "experience",
          name: "admin-experience",
          component: () => import("@/pages/admin/ExperiencePage.vue"),
        },
        {
          path: "education",
          name: "admin-education",
          component: () => import("@/pages/admin/EducationPage.vue"),
        },
        {
          path: "profile",
          name: "admin-profile",
          component: () => import("@/pages/admin/ProfilePage.vue"),
        },
        {
          path: "social-links",
          name: "admin-social-links",
          component: () => import("@/pages/admin/SocialLinksPage.vue"),
        },
        {
          path: "messages",
          name: "admin-messages",
          component: () => import("@/pages/admin/MessagesPage.vue"),
        },
      ],
    },
    {
      path: "/:pathMatch(.*)*",
      name: "not-found",
      component: () => import("@/pages/NotFoundPage.vue"),
    },
  ],
});

router.beforeEach(async (to) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth) {
    if (!authStore.isLoggedIn) return { name: "admin-login" };

    if (!authStore.user) {
      try {
        await authStore.fetchUser();
      } catch {
        authStore.clear();
        return { name: "admin-login" };
      }
    }

    if (!authStore.isAdmin) return { name: "home" };
  }

  if (to.meta.guestOnly && authStore.isAdmin)
    return { name: "admin-dashboard" };
});

export default router;
