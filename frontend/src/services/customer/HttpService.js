export default class HttpService {
    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.defaultHeaders = {
            'Accept': 'application/json',
            // KHÔNG set 'Content-Type': 'application/json' ở đây cứng
        };
    }

    getToken() {
        return localStorage.getItem('access_token');
    }

    async request(endpoint, options = {}) {
        const token = this.getToken();
        const headers = { ...this.defaultHeaders, ...options.headers };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        // --- QUAN TRỌNG: Xử lý Content-Type ---
        if (!(options.body instanceof FormData)) {
            // Nếu không phải FormData (tức là JSON), thì mới set header này
            if (!headers['Content-Type']) {
                headers['Content-Type'] = 'application/json';
            }
        } else {
            // Nếu là FormData, BẮT BUỘC phải để trình duyệt tự set (để có boundary)
            delete headers['Content-Type'];
        }

        // --- Xử lý Query Params ---
        const { params, ...fetchOptions } = options;
        let url = `${this.baseUrl}${endpoint}`;
        
        if (params) {
            const cleanParams = Object.fromEntries(
                Object.entries(params).filter(([_, v]) => v != null && v !== '')
            );
            const queryString = new URLSearchParams(cleanParams).toString();
            if (queryString) url += `?${queryString}`;
        }

        const config = {
            ...fetchOptions,
            headers,
        };

        try {
            const response = await fetch(url, config);

            if (response.status === 204) return null;

            const result = await response.json();

            if (!response.ok) {
                if (response.status === 401) {
                    localStorage.removeItem('access_token');
                    window.location.href = '/admin/login'; 
                }
                throw new Error(result.message || `API Error: ${response.status}`);
            }
            return result;
        } catch (error) {
            console.error(`HTTP Request Error (${endpoint}):`, error);
            throw error;
        }
    }
}