import HttpService from './HttpService';

class BrandService extends HttpService {
    static _instance = null;
    constructor() { super(); }

    static get instance() {
        if (!BrandService._instance) BrandService._instance = new BrandService();
        return BrandService._instance;
    }

    async getBrands(params = {}) { return this.request('/admin/brands', { params }); }
    async getBrand(uuid) { return this.request(`/admin/brands/${uuid}`); }

    async createBrand(formData) {
        return this.request('/admin/brands', {
            method: 'POST',
            body: formData,
            headers: { 'Content-Type': undefined } // Để browser tự handle boundary
        });
    }

    async updateBrand(uuid, formData) {
        // Laravel yêu cầu method POST + _method: PUT khi upload file
        formData.append('_method', 'PUT'); 
        return this.request(`/admin/brands/${uuid}`, {
            method: 'POST',
            body: formData,
            headers: { 'Content-Type': undefined }
        });
    }

    async deleteBrand(uuid) {
        return this.request(`/admin/brands/${uuid}`, { method: 'DELETE' });
    }
}

export default BrandService;