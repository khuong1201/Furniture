class InventoryService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!InventoryService._instance) {
            InventoryService._instance = new InventoryService();
        }
        return InventoryService._instance;
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
            // Auto-set token from localStorage
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
            console.error(`Inventory Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get inventory list (admin)
    async getInventories(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/admin/inventories${queryString ? `?${queryString}` : ''}`);
    }

    // Adjust inventory stock
    async adjustStock(data) {
        return this._request('/admin/inventories/adjust', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // Upsert inventory stock
    async upsertStock(data) {
        return this._request('/admin/inventories/upsert', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // Static methods
    static async getInventories(params) {
        return InventoryService.instance.getInventories(params);
    }

    static async adjustStock(data) {
        return InventoryService.instance.adjustStock(data);
    }

    static async upsertStock(data) {
        return InventoryService.instance.upsertStock(data);
    }
}

export default InventoryService;
