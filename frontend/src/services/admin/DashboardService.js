import HttpService from './HttpService';

class DashboardService extends HttpService {
    static _instance = null;

    static get instance() {
        if (!DashboardService._instance) {
            DashboardService._instance = new DashboardService();
        }
        return DashboardService._instance;
    }

    /**
     * @param {object} params { range, month, year }
     */
    async getSummary(params = {}) {
        // Truyền object params vào options, HttpService sẽ xử lý nối chuỗi query
        return this.request('/admin/dashboard/summary', {
            method: 'GET',
            params: params
        });
    }
}

export default DashboardService.instance;