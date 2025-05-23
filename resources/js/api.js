import axios from 'axios';

// Create Axios instance
const api = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
    withCredentials: true,
});

// Suppress 401 errors globally
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            return Promise.resolve({ data: { message: "Unauthorized" } });
        }
        return Promise.reject(error);
    }
);

export default api;
