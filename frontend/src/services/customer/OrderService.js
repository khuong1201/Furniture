import HttpService from './HttpService'; // Đảm bảo đường dẫn import đúng file base

class OrderService extends HttpService {
    static _instance = null;

    constructor() {
        super();
    }

    static get instance() {
        if (!OrderService._instance) {
            OrderService._instance = new OrderService();
        }
        return OrderService._instance;
    }

    // --- Customer Order APIs ---

    /**
     * Lấy danh sách đơn hàng của tôi
     * @param {object} params { page, status, search... }
     * @param {AbortSignal} signal Để hủy request cũ nếu spam click tab
     */
    async getMyOrders(params = {}, signal = null) {
        // Gọi qua lớp cha HttpService để tự động handle token & headers
        return this.request('/orders', { 
            method: 'GET', 
            params, 
            signal 
        });
    }

    /**
     * Lấy chi tiết đơn hàng theo UUID
     */
    async getOrderDetail(uuid) {
        return this.request(`/orders/${uuid}`, { method: 'GET' });
    }

    /**
     * Tạo đơn hàng Checkout từ Giỏ hàng
     */
    async checkout(data) {
        return this.request('/orders/checkout', { 
            method: 'POST', 
            body: JSON.stringify(data) 
        });
    }

    /**
     * Mua ngay (Buy Now) - Bỏ qua giỏ hàng
     */
    async buyNow(data) {
        return this.request('/orders/buy-now', { 
            method: 'POST', 
            body: JSON.stringify(data) 
        });
    }

    /**
     * Hủy đơn hàng
     */
    async cancelOrder(uuid) {
        return this.request(`/orders/${uuid}/cancel`, { method: 'POST' });
    }
    
    // (Legacy) Tạo đơn thường nếu còn dùng
    async createOrder(data) {
        return this.request('/orders', { method: 'POST', body: JSON.stringify(data) });
    }
}

// Export instance singleton để dùng chung toàn app
export default OrderService.instance;