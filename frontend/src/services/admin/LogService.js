import HttpService from './HttpService';

class LogService extends HttpService {
    static _instance = null;
    constructor() { super(); }

    static get instance() {
        if (!LogService._instance) LogService._instance = new LogService();
        return LogService._instance;
    }

    async getAll(params = {}) { return this.request('/admin/logs', { params }); }
    async getById(uuid) { return this.request(`/admin/logs/${uuid}`); }

    // Static
    static getAll(params) { return LogService.instance.getAll(params); }
    static getById(uuid) { return LogService.instance.getById(uuid); }
}

export default LogService;