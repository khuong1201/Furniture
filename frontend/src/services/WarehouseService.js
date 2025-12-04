class WarehouseService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!WarehouseService._instance) {
            WarehouseService._instance = new WarehouseService();
        }
        return WarehouseService._instance;
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
            console.error(`Warehouse Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get warehouses list (admin)
    async getWarehouses(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/admin/warehouses${queryString ? `?${queryString}` : ''}`);
    }

    // Get single warehouse
    async getWarehouse(uuid) {
        return this._request(`/admin/warehouses/${uuid}`);
    }

    // Create warehouse (admin)
    async createWarehouse(data) {
        return this._request('/admin/warehouses', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // Update warehouse (admin)
    async updateWarehouse(uuid, data) {
        return this._request(`/admin/warehouses/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    // Delete warehouse (admin)
    async deleteWarehouse(uuid) {
        return this._request(`/admin/warehouses/${uuid}`, {
            method: 'DELETE',
        });
    }

    // Static methods
    static async getWarehouses(params) {
        return WarehouseService.instance.getWarehouses(params);
    }

    static async getWarehouse(uuid) {
        return WarehouseService.instance.getWarehouse(uuid);
    }

    static async createWarehouse(data) {
        return WarehouseService.instance.createWarehouse(data);
    }

    static async updateWarehouse(uuid, data) {
        return WarehouseService.instance.updateWarehouse(uuid, data);
    }

    static async deleteWarehouse(uuid) {
        return WarehouseService.instance.deleteWarehouse(uuid);
    }
}

export default WarehouseService;
