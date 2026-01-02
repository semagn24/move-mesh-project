import axios from 'axios';

// Define available backend nodes for failover
const NODES = [
    'http://localhost:5000/api',                  // Native Local (PC1)
    'http://192.168.137.134:5000/api',            // Failover to PC2 (Replace with PC2 IP)
    'http://192.168.137.1:5000/api'               // Back to PC1 via IP
];

let currentNodeIndex = 0;

const api = axios.create({
    baseURL: NODES[0],
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json'
    }
});

// Failover Interceptor
api.interceptors.response.use(
    response => response,
    async error => {
        const originalRequest = error.config;

        // Check if error is Network Error or Server Error (5xx)
        // ensure we don't not loop infinitely (add a custom retry count property)
        if (!originalRequest._retryCount) originalRequest._retryCount = 0;

        if (
            originalRequest._retryCount < NODES.length && // Try each node once
            (error.code === 'ERR_NETWORK' || (error.response && error.response.status >= 500))
        ) {
            originalRequest._retryCount += 1;

            // Switch to next node
            currentNodeIndex = (currentNodeIndex + 1) % NODES.length;
            const newNode = NODES[currentNodeIndex];

            console.warn(`[Failover] Primary node failed. Switching to node: ${newNode}`);

            // Update instance default for subsequent requests
            api.defaults.baseURL = newNode;
            // Update current request
            originalRequest.baseURL = newNode;

            return api(originalRequest);
        }

        return Promise.reject(error);
    }
);

export default api;
