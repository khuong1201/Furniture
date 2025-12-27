import HttpService from './HttpService';

class PaymentService extends HttpService {
    static _instance = null;
    constructor() { super(); }

    static get instance() {
        if (!PaymentService._instance) PaymentService._instance = new PaymentService();
        return PaymentService._instance;
    }

    async getAll(params = {}) { return this.request('/payments', { params }); }
    async getById(uuid) { return this.request(`/payments/${uuid}`); }
    
    async create(data) {
        return this.request('/payments', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    async update(uuid, data) {
        return this.request(`/payments/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    // --- FIX: Bổ sung method updateStatus để khớp với logic Backend ---
    async updateStatus(uuid, status) {
        return this.request(`/payments/${uuid}/status`, {
            method: 'PATCH', // Dùng PATCH cho cập nhật từng phần (status)
            body: JSON.stringify({ status }),
        });
    }

    // Static mapping - Đảm bảo tất cả method instance đều có static tương ứng
    static getAll(params) { return PaymentService.instance.getAll(params); }
    static getById(uuid) { return PaymentService.instance.getById(uuid); }
    static create(data) { return PaymentService.instance.create(data); }
    static update(uuid, data) { return PaymentService.instance.update(uuid, data); }
    static updateStatus(uuid, status) { return PaymentService.instance.updateStatus(uuid, status); }
}

export default PaymentService;