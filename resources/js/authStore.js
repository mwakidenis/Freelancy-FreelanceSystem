import { defineStore } from "pinia";
import api from "./api";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null, // Store user data (but NOT JWT token)
  }),
  actions: {
    async login(email, password) {
      try {
          const response = await api.post("/auth/login", {
              email: email.value,
              password: password.value,
          }, { withCredentials: true });
  
          if (response.status === 200) {
              await this.checkAuth(); // Fetch user data after successful login
              return true; // Return success
          }
      } catch (error) {

          return false;

      }
    },
    async checkAuth() {
      try {
        const response = await api.get("/auth/user", {
          withCredentials: true,
        });
        this.user = response.data;
        // console.log("user data is:",this.user);
        return response.data;
      } catch (error) {
        this.user = null;
        console.log(error);
      }
    },
    async logout() {
      try {
        const response = await api.post("/auth/logout", null, {
          withCredentials: true,
        });
        // console.log("Response is", response);

        this.user = null; // Clear user state
      } catch (error) {
        console.log(error);
      }
    }
  },
  getters: {
    isAuthenticated: (state) => !!state.user,
    getUser: (state) => state.user,
    getUserRole: (state) => state.user?.role,
    getUserName: (state) => state.user?.name,
    isFreelancer: (state) => state.user?.role === 'freelancer',
    isClient: (state) => state?.user.role === 'client',
    // isEmailVerified: (state) => state.user?.email_verified === true, // ✅ Check email verification
  },
  persist: true, // Enable Pinia persist
});
