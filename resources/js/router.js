import { createRouter, createWebHistory } from "vue-router";
import defaultLayout from "./layouts/Default.vue";
import authLayout from "./layouts/Auth.vue";
import { useAuthStore } from "./stores/auth";
import Blank from "./layouts/blank.vue";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: "/journals",
            name: "journal_directory",
            component: () => import("./pages/JournalDirectory.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/journals/:slug",
            name: "journal_details",
            component: () => import("./pages/JournalDetail.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/",
            name: "home",
            component: () => import("./pages/Home.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/courses",
            name: "list",
            component: () => import("./pages/List.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/details/:id",
            name: "details",
            component: () => import("./pages/Details.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/play/:course_id",
            name: "play",
            component: () => import("./pages/Play.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/instructor/:id",
            name: "instructor",
            component: () => import("./pages/Instructor.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/login",
            name: "login",
            component: () => import("./pages/Login.vue"),
            meta: {
                layout: Blank,
            },
        },
        {
            path: "/register",
            name: "register",
            component: () => import("./pages/Register.vue"),
            meta: {
                layout: Blank,
            },
        },
        {
            path: "/reset_password",
            name: "reset_password",
            component: () => import("./pages/ResetPassword.vue"),
            meta: {
                layout: Blank,
            },
        },
        {
            path: "/verify_otp",
            name: "verify_otp",
            component: () => import("./pages/VerifyOtp.vue"),
            meta: {
                layout: Blank,
            },
        },
        {
            path: "/new_password",
            name: "new_password",
            component: () => import("./pages/NewPassword.vue"),
            meta: {
                layout: Blank,
            },
        },
        {
            path: "/checkout/:id",
            name: "checkout",
            component: () => import("./pages/Checkout.vue"),
            meta: {
                layout: Blank,
                requiresAuth: true,
            },
        },
        {
            path: "/enroll_status",
            name: "enroll_status",
            component: () => import("./pages/EnrollStatus.vue"),
            meta: {
                layout: Blank,
            },
        },
        {
            path: "/dashboard",
            name: "dashboard",
            component: () => import("./pages/Dashboard.vue"),
            meta: {
                layout: authLayout,
                requiresAuth: true,
            },
        },
        {
            path: "/page/:slug",
            name: "page",
            component: () => import("./pages/Page.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/journals/:slug/register",
            name: "journal_register",
            component: () => import("./pages/journal/Register.vue"),
            meta: {
                layout: defaultLayout,
                requiresAuth: true,
            },
        },
        {
            path: "/journals/:slug/membership/status",
            name: "journal_membership_status",
            component: () => import("./pages/journal/MembershipStatus.vue"),
            meta: {
                layout: defaultLayout,
                requiresAuth: true,
            },
        },
        {
            path: "/author",
            component: () => import("./pages/author/Workspace.vue"),
            meta: {
                layout: defaultLayout,
                requiresAuth: true,
            },
            children: [
                {
                    path: "",
                    name: "author_workspace_overview",
                    component: () => import("./pages/author/Overview.vue"),
                },
                {
                    path: "journals",
                    name: "author_workspace_journals",
                    component: () => import("./pages/author/Journals.vue"),
                },
                {
                    path: "profile",
                    name: "author_workspace_profile",
                    component: () => import("./components/DashboardProfile.vue"),
                },
                {
                    path: "submissions",
                    name: "author_submission_list",
                    component: () => import("./pages/author/SubmissionList.vue"),
                },
                {
                    path: "submissions/create",
                    name: "author_submission_create",
                    component: () => import("./pages/author/SubmissionForm.vue"),
                },
                {
                    path: "submissions/:id",
                    name: "author_submission_details",
                    component: () => import("./pages/author/SubmissionDetail.vue"),
                }
            ]
        },
        {
            path: "/dashboard/journals/:slug/overview",
            name: "dashboard_journal_workspace",
            component: () => import("./pages/dashboard/DashboardJournalWorkspace.vue"),
            meta: {
                layout: defaultLayout,
                requiresAuth: true,
            },
        },
        {
            path: "/dashboard/journals/:slug/submissions",
            name: "dashboard_journal_submissions",
            component: () => import("./pages/dashboard/DashboardJournalSubmissions.vue"),
            meta: {
                layout: defaultLayout,
                requiresAuth: true,
            },
        },
        {
            path: "/dashboard/journals/:slug/submissions/:id",
            name: "dashboard_journal_submission_details",
            component: () => import("./pages/dashboard/DashboardJournalSubmissionDetails.vue"),
            meta: {
                layout: defaultLayout,
                requiresAuth: true,
            },
        },
        {
            path: "/dashboard/journals/:slug/editorial-process",
            name: "dashboard_journal_editorial_process",
            component: () => import("./pages/dashboard/DashboardJournalEditorialProcess.vue"),
            meta: {
                layout: defaultLayout,
                requiresAuth: true,
            },
        },
        {
            path: "/dashboard/journals/:slug/members",
            name: "dashboard_journal_members",
            component: () => import("./pages/dashboard/DashboardJournalMembers.vue"),
            meta: {
                layout: defaultLayout,
                requiresAuth: true,
            },
        },
        {
            path: "/dashboard/journals/:slug/settings",
            name: "dashboard_journal_settings",
            component: () => import("./pages/dashboard/DashboardJournalSettings.vue"),
            meta: {
                layout: defaultLayout,
                requiresAuth: true,
            },
        },
        {
            path: "/about-us",
            name: "about_us",
            component: () => import("./pages/AboutUs.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/exam/:id",
            name: "exam",
            component: () => import("./pages/Exam.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/quiz/:id",
            name: "quiz",
            component: () => import("./pages/Quiz.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/contact-us",
            name: "contact_us",
            component: () => import("./pages/ContactUs.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/faq",
            name: "faq",
            component: () => import("./pages/FAQ.vue"),
            meta: {
                layout: defaultLayout,
            },
        },
        {
            path: "/:catchAll(.*)",
            name: "notFound",
            component: () => import("./pages/PageNotFound.vue"),
            meta: {
                layout: Blank,
            },
        },
    ],
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore(); // Get auth store instance

    if (to.meta.requiresAuth && !authStore.userData) {
        return next({ name: "login" });
    } else if (
        (to.name === "login" || to.name === "register") &&
        authStore.userData
    ) {
        return next({ name: "home" });
    } else if (
        from.name === "checkout" &&
        authStore.userData &&
        to.name !== "details" &&
        to.name !== "enroll_status"
    ) {
        return next({ name: "details", params: { id: to.params.id } });
    }

    next(); // Proceed to the next route
});

export default router;
