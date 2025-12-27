import HttpService from './HttpService';

class CategoryService extends HttpService {
    static _instance = null;
    constructor() { super(); }

    static get instance() {
        if (!CategoryService._instance) CategoryService._instance = new CategoryService();
        return CategoryService._instance;
    }

    async getCategories(params = {}) { return this.request('/public/categories', { params }); }
    async getCategoryTree() { return this.request('/public/categories'); }
    async getCategory(uuid) { return this.request(`/public/categories/${uuid}`); }

    async createCategory(data) {
        const isFormData = data instanceof FormData;
        return this.request('/admin/categories', {
            method: 'POST',
            body: isFormData ? data : JSON.stringify(data),
            headers: isFormData ? { 'Content-Type': undefined } : {}
        });
    }

    async updateCategory(uuid, data) {
        return this.request(`/admin/categories/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    async deleteCategory(uuid) { return this.request(`/admin/categories/${uuid}`, { method: 'DELETE' }); }

    // Static
    static getCategories(params) { return CategoryService.instance.getCategories(params); }
    static getCategoryTree() { return CategoryService.instance.getCategoryTree(); }
    static getCategory(uuid) { return CategoryService.instance.getCategory(uuid); }
    static createCategory(data) { return CategoryService.instance.createCategory(data); }
    static updateCategory(uuid, data) { return CategoryService.instance.updateCategory(uuid, data); }
    static deleteCategory(uuid) { return CategoryService.instance.deleteCategory(uuid); }
}

export default CategoryService;