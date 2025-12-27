import HttpService from './HttpService';

class WarehouseService extends HttpService {
    async getWarehouses(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/admin/warehouses?${query}`);
    }

    async getWarehouse(uuid) {
        return this.request(`/admin/warehouses/${uuid}`);
    }

    async getWarehouseStats(uuid) {
        return this.request(`/admin/warehouses/${uuid}/stats`);
    }

    async createWarehouse(data) {
        return this.request('/admin/warehouses', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    async updateWarehouse(uuid, data) {
        return this.request(`/admin/warehouses/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    async deleteWarehouse(uuid) {
        return this.request(`/admin/warehouses/${uuid}`, {
            method: 'DELETE',
        });
    }
}

export default new WarehouseService();