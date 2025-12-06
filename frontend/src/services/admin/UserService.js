class UserService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!UserService._instance) {
            UserService._instance = new UserService();
        }
        return UserService._instance;
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
            console.error(`User Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get users list (admin)
    async getUsers(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/admin/users${queryString ? `?${queryString}` : ''}`);
    }

    // Get single user
    async getUser(uuid) {
        return this._request(`/admin/users/${uuid}`);
    }

    // Get current user profile
    async getProfile() {
        return this._request('/profile');
    }

    // Create user (admin)
    async createUser(data) {
        return this._request('/admin/users', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // Update user (admin)
    async updateUser(uuid, data) {
        return this._request(`/admin/users/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    // Delete user (admin)
    async deleteUser(uuid) {
        return this._request(`/admin/users/${uuid}`, {
            method: 'DELETE',
        });
    }

    // Static methods
    static async getUsers(params) {
        return UserService.instance.getUsers(params);
    }

    static async getUser(uuid) {
        return UserService.instance.getUser(uuid);
    }

    static async getProfile() {
        return UserService.instance.getProfile();
    }

    static async createUser(data) {
        return UserService.instance.createUser(data);
    }

    static async updateUser(uuid, data) {
        return UserService.instance.updateUser(uuid, data);
    }

    static async deleteUser(uuid) {
        return UserService.instance.deleteUser(uuid);
    }
}

export default UserService;
