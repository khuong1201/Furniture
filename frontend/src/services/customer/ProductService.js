class ProductService {
  static _instance = null;

  constructor() {
    // ✅ Đảm bảo Base URL trỏ đúng vào /public theo Swagger
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/public';
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  static get instance() {
    if (!ProductService._instance) {
      ProductService._instance = new ProductService();
    }
    return ProductService._instance;
  }

  // --- HÀM PRIVATE XỬ LÝ REQUEST ---
  async _request(endpoint, options = {}) {
    try {
      // ✅ Tự động lấy Token để hỗ trợ các tính năng cần đăng nhập (nếu có)
      const token = localStorage.getItem('access_token');
      const headers = { ...this.headers, ...options.headers };

      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const url = `${this.baseUrl}${endpoint}`;
      const response = await fetch(url, { ...options, headers });

      if (!response.ok) {
        throw new Error(`Lỗi API (${response.status}): ${response.statusText}`);
      }

      const result = await response.json();

      // ✅ Swagger trả về: { success: true, data: { ... } }
      // Trả về phần 'data' để bên ngoài dễ xử lý
      return result.data || result; 
    } catch (error) {
      console.error(`ProductService Error (${endpoint}):`, error);
      throw error;
    }
  }

  // --- 1. LẤY DANH SÁCH & TÌM KIẾM (GỘP CHUNG) ---
  // Params: { page, per_page, search, category_uuid, sort_by, ... }
  async getAllProducts(params = {}) {
    const validParams = Object.fromEntries(
      Object.entries(params).filter(([_, v]) => v != null && v !== '')
    );

    const queryString = new URLSearchParams(validParams).toString();

    return this._request(`/products?${queryString}`, { method: 'GET' });
  }

  // --- CHI TIẾT SẢN PHẨM ---
  async getProductDetail(id) {
    return this._request(`/products/${id}`, { method: 'GET' });
  }

  // --- WRAPPER TÌM KIẾM ---
  async searchProducts(keyword) {
    return this.getAllProducts({ 
      search: keyword,
      page: 1 
    });
  }

  static async getAllProducts(params) { return ProductService.instance.getAllProducts(params); }
  static async getProductDetail(id) { return ProductService.instance.getProductDetail(id); }
  static async searchProducts(keyword) { return ProductService.instance.searchProducts(keyword); }
}

export default ProductService;