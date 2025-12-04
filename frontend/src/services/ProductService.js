class ProductService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!ProductService._instance) {
            ProductService._instance = new ProductService();
        }
        return ProductService._instance;
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
            console.error(`Product Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get products list (admin)
    async getProducts(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/admin/products${queryString ? `?${queryString}` : ''}`);
    }

    // Get single product
    async getProduct(uuid) {
        return this._request(`/public/products/${uuid}`);
    }

    // Create product
    async createProduct(data) {
        return this._request('/admin/products', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // Update product
    async updateProduct(uuid, data) {
        return this._request(`/admin/products/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    // Delete product
    async deleteProduct(uuid) {
        return this._request(`/admin/products/${uuid}`, {
            method: 'DELETE',
        });
    }

    // Static methods
    static async getProducts(params) {
        return ProductService.instance.getProducts(params);
    }

    static async getProduct(uuid) {
        return ProductService.instance.getProduct(uuid);
    }

    static async createProduct(data) {
        return ProductService.instance.createProduct(data);
    }

    static async updateProduct(uuid, data) {
        return ProductService.instance.updateProduct(uuid, data);
    }

    static async deleteProduct(uuid) {
        return ProductService.instance.deleteProduct(uuid);
    }
}

export default ProductService;
