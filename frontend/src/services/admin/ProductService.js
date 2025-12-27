import HttpService from './HttpService';

class ProductService extends HttpService {
    static _instance = null;
    constructor() { super(); }

    static get instance() {
        if (!ProductService._instance) ProductService._instance = new ProductService();
        return ProductService._instance;
    }

    // --- Core API ---
    async getProducts(params = {}) {
        return this.request('/admin/products', { params });
    }

    async getProduct(uuid) {
        return this.request(`/admin/products/${uuid}`); 
    }

    async createProduct(data) {
        return this.request('/admin/products', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async updateProduct(uuid, data) {
        return this.request(`/admin/products/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async deleteProduct(uuid) {
        return this.request(`/admin/products/${uuid}`, { method: 'DELETE' });
    }

    // --- Media API (Fix Logic) ---
    async uploadImage(productUuid, file, isPrimary = false) {
        // Validation
        if (!file || !(file instanceof File)) {
            console.error("Upload Error: Invalid file object", file);
            return;
        }

        const formData = new FormData();
        formData.append('image', file); // Backend nhận field 'image'
        formData.append('is_primary', isPrimary ? '1' : '0');

        return this.request(`/admin/products/${productUuid}/images`, {
            method: 'POST',
            body: formData
            // HttpService sẽ tự xử lý headers, không cần truyền gì thêm
        });
    }

    async deleteImage(imageUuid) {
        return this.request(`/admin/product-images/${imageUuid}`, { method: 'DELETE' });
    }

    // --- AI Feature ---
    async generateDescription(payload) {
        return this.request('/admin/products/generate-ai-description', {
            method: 'POST',
            body: JSON.stringify(payload)
        });
    }
}

export default ProductService;