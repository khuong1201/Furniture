import HttpService from './HttpService';

class AddressService extends HttpService {
    static _instance = null;
    constructor() { super(); }

    static get instance() {
        if (!AddressService._instance) AddressService._instance = new AddressService();
        return AddressService._instance;
    }

    async getAddresses(params = {}) { return this.request('/addresses', { params }); }

    // Static
    static async getAddresses(params) {
        return AddressService.instance.getAddresses(params);
    }
}

export default AddressService;