import HttpService from './HttpService';

class OrderService extends HttpService {
    static _instance = null;
    constructor() { super(); }

    static get instance() {
        if (!OrderService._instance) OrderService._instance = new OrderService();
        return OrderService._instance;
    }

    // --- Admin Methods ---

    async getOrders(params = {}) {
        // Axios hoặc fetch wrapper thường tự xử lý params trong object config
        return this.request('/admin/orders', { method: 'GET', params });
    }

    async getOrder(uuid) {
        return this.request(`/admin/orders/${uuid}`, { method: 'GET' });
    }

    async createOrder(data) {
        return this.request('/admin/orders/create', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async updateStatus(uuid, status) {
        return this.request(`/admin/orders/${uuid}/status`, {
            method: 'PUT',
            body: JSON.stringify({ status })
        });
    }

    // Static Helpers
    static getOrders(params) { return OrderService.instance.getOrders(params); }
    static getOrder(uuid) { return OrderService.instance.getOrder(uuid); }
    static createOrder(data) { return OrderService.instance.createOrder(data); }
    static updateStatus(uuid, status) { return OrderService.instance.updateStatus(uuid, status); }
}

export default OrderService;