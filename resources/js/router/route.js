import { createRouter,createWebHistory } from "vue-router";
import { useAuthStore } from "../authStore";
import MainLayout from "../components/layout/MainLayout.vue";
import Home from "../components/pages/Home.vue";
import Example from "../components/pages/Example.vue";
import Test from "../components/pages/Test.vue";
import AddNews from "../components/pages/AddNews.vue";
import Login from "../components/pages/Login.vue";
import Register from "../components/pages/Register.vue";
import EmailVerify from "../components/pages/EmailVerify.vue";
import EmailVerifySubmit from "../components/pages/EmailVerifySubmit.vue";
// FOR FREELANCE MODULE
import FreelanceCreate from "../components/pages/Freelance/Create.vue";
import FreelanceView from "../components/pages/Freelance/View.vue";
import FreelanceApply from "../components/pages/Freelance/Apply.vue";
// FOR CLIENT MODULE
import ClientViewProjects from "../components/pages/Client/ViewProjects.vue";
import ClientViewSingleProject from "../components/pages/Client/ViewProjectSingle.vue";
import ClientViewProposals from "../components/pages/Client/ViewProposals.vue";
// FOR PROFILE MODULE 
import ViewProfile from "../components/pages/Profile/ViewProfile.vue";
// FOR MESSAGE MODULE
import Message from "../components/pages/Message.vue";

const routes = [
    {
        path: "/",
        component: MainLayout,
        children: [
            { 
                path:"", 
                component: Home 
            },
            {
                path: '/:slug',
                component: FreelanceView,
                name: "freelance-details"
            },
            {
                path: '/:slug/apply',
                component: FreelanceApply,
                name: "freelance-apply",
            },
            {
                path: '/projects',
                component: ClientViewProjects,
                name: "client-view-projects"
            },
            {
                path: '/projects/:slug',
                component: ClientViewSingleProject,
                name: "client-view-single-project"
            },
            { 
                path: '/:slug/proposals',
                component: ClientViewProposals,
                name: "client-view-proposals"
            },
            {
                path: '/profile/:username',
                component: ViewProfile,
                name: "view-profile"
            },
            // {
            //     path: '/:username/message',
            //     component: Message,
            //     name: "message"
            // }
            // OTHER PATHS THAT WILL USE NAVBAR + FOOTER
        ]
    },
    {
        path: '/inbox/:username?',
        component: Message,
        name: "inbox"
    },
    {
        path: '/example',
        component: Example
    }, 
    {
        path: '/test',
        component: Test
    },
    {
        path: '/test/create',
        component: AddNews,
        meta: { requiresAuth: true} // Protected route
    },
    {
        path: '/login',
        component: Login,
        meta: { requiresGuest: true } // Only accessible by guests
    },
    {
        path: '/register',
        component: Register,
        meta: { requiresGuest: true }
    },
    {
        path: '/verify', //For displaying that you need email verification to fully access the site
        component: EmailVerify,
        // meta: { requiresAuth: true }
    },
    {
        path: '/verify-submit', //Once the mail has been clicked, the user will be forwarded to this link
        component:  EmailVerifySubmit
    },
    {
        path: '/error', //error page if the path matches nothing 
        // component: ,
    },
    {
        path: '/freelance/create',
        component: FreelanceCreate,
        meta: { requiresAuth:true }
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
        next("/example"); //case if the user is already logged in but want to access log in page
    // } else if (to.meta.requiresAuth && authStore.user['email_verified_at'] == null && to.path !== "/verify"){
    //     next("/verify"); //case if the user is already logged in but email not yet verified
    // } else if (to.meta.requiresAuth && authStore.user['email_verified_at'] != null && to.path == "/verify") {
    //     next("/example"); //case if the user is already logged in + email is verified but want to access verify email page
    } else {
        next(); //case if no problems arise, just direct to the page
    }
});

export default route;