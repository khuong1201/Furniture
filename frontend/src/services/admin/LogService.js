class LogService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!LogService._instance) {
            LogService._instance = new LogService();
        }
        return LogService._instance;
    }

    setToken(token) {
        if (token) {
            this.headers['Authorization'] = `Bearer ${token}`;
        } else {
            delete this.headers['Authorization'];
        }
    }

    async _request(endpoint, options = {}) {
        try {
            const token = localStorage.getItem('access_token');
            if (token) {
                this.setToken(token);
            }

            const url = `${this.baseUrl}${endpoint}`;
            const config = {
                ...options,
                headers: {
                    ...this.headers,
                    ...options.headers,
                },
            };

            const response = await fetch(url, config);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || `API Error: ${response.status}`);
            }

            return result;
        } catch (error) {
            console.error(`Log Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get all logs with pagination and filters
    async getAll(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/admin/logs${queryString ? `?${queryString}` : ''}`);
    }

    // Get single log detail
    async getById(uuid) {
        return this._request(`/admin/logs/${uuid}`);
    }

    // Static methods
    static getAll(params) { return LogService.instance.getAll(params); }
    static getById(uuid) { return LogService.instance.getById(uuid); }
}

export default LogService;
