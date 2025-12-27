import HttpService from './HttpService'; 

class InventoryService extends HttpService {
    static _instance = null;

    static get instance() {
        if (!InventoryService._instance) {
            InventoryService._instance = new InventoryService();
        }
        return InventoryService._instance;
    }

    // 1. Lấy danh sách
    async getInventories(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/admin/inventories?${query}`);
    }

    // 2. Lấy chi tiết
    async getInventory(uuid) {
        return this.request(`/admin/inventories/${uuid}`);
    }

    // 3. Dashboard: Stats Cards
    async getDashboardStats(warehouseUuid) {
        const params = new URLSearchParams();
        if (warehouseUuid) params.append('warehouse_uuid', warehouseUuid);
        return this.request(`/admin/inventories/dashboard-stats?${params.toString()}`);
    }

    // 4. Dashboard: Chart Data
    async getMovementChart(warehouseUuid, period = 'week', month = null, year = null) {
        const params = new URLSearchParams();
        if (warehouseUuid) params.append('warehouse_uuid', warehouseUuid);
        if (period) params.append('period', period);
        if (month) params.append('month', month);
        if (year) params.append('year', year);
        
        return this.request(`/admin/inventories/movements-chart?${params.toString()}`);
    }

    // 5. Điều chỉnh kho (+/-)
    async adjustStock(data) {
        return this.request('/admin/inventories/adjust', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // 6. Kiểm kê (Set cứng)
    async upsertStock(data) {
        return this.request('/admin/inventories/upsert', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }
}

export default InventoryService.instance;