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
        this.user = response.data.data;
        console.log("response from check auth method: ", response.data.data);
        return response.data.data;
      } catch (error) {
        this.user = null;
        console.log(error);
      }
    },
    async logout() {
      return new Promise((resolve) => {
        setTimeout(async() => {
          try {
            const response = await api.post("/auth/logout", null, {
              withCredentials: true,
            });
            this.user = null; // Clear user state
          } catch (error) {
            console.log(error);
          } finally {
            resolve();
          }
        }, 2000);
      });
    }
  },
  getters: {
    isAuthenticated: (state) => !!state.user,
    getUser: (state) => state.user,
    getUserRole: (state) => state.user.user_type,
    //below returns error!
    // getUserName: (state) => state.user?.data.name, 
    // isFreelancer: (state) => state.user?.data.user_type === 'freelancer',
    // isClient: (state) => state?.user.data.user_type === 'client',
    // isEmailVerified: (state) => state.user?.email_verified === true, // ✅ Check email verification
  },
  persist: true, // Enable Pinia persist
});
