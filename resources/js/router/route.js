import { createRouter,createWebHistory } from "vue-router";
import { useAuthStore } from "../authStore";

import MainLayout from "../components/layout/MainLayout.vue";
// FOR AUTH
import Login from "../components/pages/Login.vue";
import EmailVerify from "../components/pages/EmailVerify.vue";
import EmailVerifySubmit from "../components/pages/EmailVerifySubmit.vue";
// FOR GENERAL ROUTE
import Home from "../components/pages/Home.vue";
import FreelanceView from "../components/pages/Freelance/View.vue";
// FOR FREELANCER MODULE
import FreelanceViewProposals from "../components/pages/Freelance/ViewProposals.vue";
import FreelanceApply from "../components/pages/Freelance/Apply.vue";
// FOR CLIENT MODULE
import ClientViewProjects from "../components/pages/Client/ViewProjects.vue";
// FOR PROFILE MODULE 
import ViewProfile from "../components/pages/Profile/ViewProfile.vue";
import EditProfile from "../components/pages/Profile/EditProfile.vue";
// FOR MESSAGE MODULE
import Message from "../components/pages/Message.vue";

const routes = [
    {
        path: "/",
        component: MainLayout,
        children: [
            { 
                path:"", 
                component: Home,
                name: "home", 
            },
            {
                path: '/:slug',
                component: FreelanceView,
                name: "freelance-details",
            },
            {
                path: '/:slug/apply',
                component: FreelanceApply,
                name: "freelance-apply",
                meta: { requiresAuth: true }
            },
            // CLIENT
            {
                path: '/projects',
                component: ClientViewProjects,
                name: "client-view-projects",
                meta: { requiresAuth: true }
            },
            // FREELANCERS
            {
                path: '/proposals',
                component: FreelanceViewProposals,
                name: "freelancer-view-proposals",
                meta: { requiresAuth: true }
            },
            // PROFILE
            {
                path: '/profile/:username',
                component: ViewProfile,
                name: "view-profile"
            },
            {
                path: '/profile/edit',
                component: EditProfile,
                name: "edit-profile",
                meta: { requiresAuth: true }
            },
        ]
    },
    {
        path: '/inbox/:username?',
        component: Message,
        name: "inbox",
        meta: { requiresAuth: true }
    },
    {
        path: '/inbox/new',
        component: Message,
        name: "new-message",
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        component: Login,
        name: "login",
        meta: { requiresGuest: true } // Only accessible by guests
    },
    {
        path: '/verify', //For displaying that you need email verification to fully access the site
        component: EmailVerify,
        meta: { requiresAuth: true }
    },
    {
        path: '/verify-submit', //Once the mail has been clicked, the user will be forwarded to this link
        component:  EmailVerifySubmit
    },
    {
        path: '/error', //error page if the path matches nothing 
        // component: ,
    },
];

const route = createRouter({
    history: createWebHistory(),
    routes,
});

route.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore(); 

    if (to.meta.requiresAuth && !authStore.isAuthenticated){ 
        next("/login"); //case if the user is not logged in but want to access authenticated pages
    } else if (to.meta.requiresGuest && authStore.isAuthenticated){ 
        next("/home"); //case if the user is already logged in but want to access log in page
    } else if (to.meta.requiresAuth && authStore.isEmailVerified == false && to.path !== "/verify"){
        console.log("i reached here");
        next("/verify"); //case if the user is already logged in but email not yet verified
    } else if (to.meta.requiresAuth && authStore.isEmailVerified == true && to.path == "/verify") {
        next("/home"); //case if the user is already logged in + email is verified but want to access verify email page
    } else {
        next(); //case if no problems arise, just direct to the page
    }
});

export default route;