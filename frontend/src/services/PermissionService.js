class PermissionService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!PermissionService._instance) {
            PermissionService._instance = new PermissionService();
        }
        return PermissionService._instance;
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
            console.error(`Permission Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get all permissions
    async getAll(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/admin/permissions${queryString ? `?${queryString}` : ''}`);
    }

    // Get single permission
    async getById(uuid) {
        return this._request(`/admin/permissions/${uuid}`);
    }

    // Create permission
    async create(data) {
        return this._request('/admin/permissions', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // Update permission
    async update(uuid, data) {
        return this._request(`/admin/permissions/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    // Delete permission
    async delete(uuid) {
        return this._request(`/admin/permissions/${uuid}`, {
            method: 'DELETE',
        });
    }

    // Get my permissions (current user)
    async getMyPermissions() {
        return this._request('/admin/my-permissions');
    }

    // Static methods
    static getAll(params) { return PermissionService.instance.getAll(params); }
    static getById(uuid) { return PermissionService.instance.getById(uuid); }
    static create(data) { return PermissionService.instance.create(data); }
    static update(uuid, data) { return PermissionService.instance.update(uuid, data); }
    static delete(uuid) { return PermissionService.instance.delete(uuid); }
    static getMyPermissions() { return PermissionService.instance.getMyPermissions(); }
}

export default PermissionService;
