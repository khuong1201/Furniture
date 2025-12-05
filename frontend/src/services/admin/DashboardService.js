class DashboardService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!DashboardService._instance) {
            DashboardService._instance = new DashboardService();
        }
        return DashboardService._instance;
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
            console.error(`Dashboard Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get dashboard summary
    async getSummary() {
        return this._request('/admin/dashboard/summary');
    }

    // Get revenue data
    async getRevenue(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/admin/dashboard/revenue${queryString ? `?${queryString}` : ''}`);
    }

    // Get detailed stats
    async getStats() {
        return this._request('/admin/dashboard/stats');
    }

    // Static methods
    static getSummary() { return DashboardService.instance.getSummary(); }
    static getRevenue(params) { return DashboardService.instance.getRevenue(params); }
    static getStats() { return DashboardService.instance.getStats(); }
}

export default DashboardService;
