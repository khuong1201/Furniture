class CategoryService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!CategoryService._instance) {
            CategoryService._instance = new CategoryService();
        }
        return CategoryService._instance;
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
            console.error(`Category Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get categories list (admin)
    async getCategories(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/admin/categories${queryString ? `?${queryString}` : ''}`);
    }

    // Get category tree (public/admin)
    async getCategoryTree() {
        return this._request('/public/categories?tree=true');
    }

    // Get single category
    async getCategory(uuid) {
        return this._request(`/public/categories/${uuid}`);
    }

    // Create category (admin)
    async createCategory(data) {
        return this._request('/admin/categories', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // Update category (admin)
    async updateCategory(uuid, data) {
        return this._request(`/admin/categories/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    // Delete category (admin)
    async deleteCategory(uuid) {
        return this._request(`/admin/categories/${uuid}`, {
            method: 'DELETE',
        });
    }

    // Static methods
    static async getCategories(params) {
        return CategoryService.instance.getCategories(params);
    }

    static async getCategoryTree() {
        return CategoryService.instance.getCategoryTree();
    }

    static async getCategory(uuid) {
        return CategoryService.instance.getCategory(uuid);
    }

    static async createCategory(data) {
        return CategoryService.instance.createCategory(data);
    }

    static async updateCategory(uuid, data) {
        return CategoryService.instance.updateCategory(uuid, data);
    }

    static async deleteCategory(uuid) {
        return CategoryService.instance.deleteCategory(uuid);
    }
}

export default CategoryService;
