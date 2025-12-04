class RoleService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!RoleService._instance) {
            RoleService._instance = new RoleService();
        }
        return RoleService._instance;
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
            console.error(`Role Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get roles list (admin)
    async getRoles(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/admin/roles${queryString ? `?${queryString}` : ''}`);
    }

    // Get single role
    async getRole(uuid) {
        return this._request(`/admin/roles/${uuid}`);
    }

    // Create role (admin)
    async createRole(data) {
        return this._request('/admin/roles', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // Update role (admin)
    async updateRole(uuid, data) {
        return this._request(`/admin/roles/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    // Delete role (admin)
    async deleteRole(uuid) {
        return this._request(`/admin/roles/${uuid}`, {
            method: 'DELETE',
        });
    }

    // Static methods
    static async getRoles(params) {
        return RoleService.instance.getRoles(params);
    }

    static async getRole(uuid) {
        return RoleService.instance.getRole(uuid);
    }

    static async createRole(data) {
        return RoleService.instance.createRole(data);
    }

    static async updateRole(uuid, data) {
        return RoleService.instance.updateRole(uuid, data);
    }

    static async deleteRole(uuid) {
        return RoleService.instance.deleteRole(uuid);
    }
}

export default RoleService;
