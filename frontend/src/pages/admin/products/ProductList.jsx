import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
    Plus,
    Search,
    Filter,
    Edit,
    Trash2,
    Eye,
    MoreVertical,
    ChevronLeft,
    ChevronRight
} from 'lucide-react';
import ProductService from '@/services/ProductService';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import './ProductList.css';

const ProductList = () => {
    const navigate = useNavigate();
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [confirmDialog, setConfirmDialog] = useState({
        isOpen: false,
        title: '',
        message: '',
        onConfirm: null,
        isLoading: false
    });

    // Pagination & Filters
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [perPage] = useState(15);
    const [searchTerm, setSearchTerm] = useState('');
    const [filterStatus, setFilterStatus] = useState('all'); // all, active, inactive

    // Fetch products
    const fetchProducts = async () => {
        try {
            setLoading(true);
            setError(null);

            const params = {
                page: currentPage,
                per_page: perPage,
            };

            if (searchTerm) {
                params.search = searchTerm;
            }

            if (filterStatus !== 'all') {
                params.is_active = filterStatus === 'active';
            }

            const response = await ProductService.getProducts(params);

            // Handle paginated response
            if (response.success && response.data) {
                setProducts(response.data);

                // Calculate total pages from meta if available
                if (response.meta) {
                    setTotalPages(response.meta.last_page || 1);
                }
            }
        } catch (err) {
            setError(err.message || 'Không thể tải danh sách sản phẩm');
            console.error('Error fetching products:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchProducts();
    }, [currentPage, filterStatus]);

    const handleSearch = (e) => {
        e.preventDefault();
        setCurrentPage(1);
        fetchProducts();
    };

    const handleDelete = (uuid, name) => {
        setConfirmDialog({
            isOpen: true,
            title: 'Xóa sản phẩm',
            message: `Bạn có chắc muốn xóa sản phẩm "${name}"? Hành động này không thể hoàn tác.`,
            onConfirm: async () => {
                setConfirmDialog(prev => ({ ...prev, isLoading: true }));
                try {
                    await ProductService.deleteProduct(uuid);
                    setConfirmDialog(prev => ({ ...prev, isOpen: false }));
                    fetchProducts();
                } catch (err) {
                    setConfirmDialog(prev => ({ ...prev, isOpen: false }));
                    alert('Lỗi khi xóa sản phẩm: ' + err.message);
                }
            }
        });
    };

    return (
        <div className="product-list">
            {/* Header */}
            <div className="page-header">
                <div>
                    <h1>Quản lý Sản phẩm</h1>
                    <p className="page-subtitle">Danh sách tất cả sản phẩm trong hệ thống</p>
                </div>
                <button
                    className="btn btn-primary"
                    onClick={() => navigate('/admin/products/create')}
                >
                    <Plus size={20} />
                    Thêm sản phẩm
                </button>
            </div>

            {/* Filters & Search */}
            <div className="filters-bar">
                <form onSubmit={handleSearch} className="search-form">
                    <div className="search-input-group">
                        <Search size={18} />
                        <input
                            type="text"
                            placeholder="Tìm kiếm theo tên hoặc SKU..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                    <button type="submit" className="btn btn-secondary">
                        Tìm kiếm
                    </button>
                </form>

                <div className="filter-group">
                    <Filter size={18} />
                    <select
                        value={filterStatus}
                        onChange={(e) => {
                            setFilterStatus(e.target.value);
                            setCurrentPage(1);
                        }}
                        className="filter-select"
                    >
                        <option value="all">Tất cả trạng thái</option>
                        <option value="active">Đang hoạt động</option>
                        <option value="inactive">Đã ẩn</option>
                    </select>
                </div>
            </div>

            {/* Table */}
            <div className="table-container">
                {loading ? (
                    <div className="loading-state">
                        <div className="spinner"></div>
                        <p>Đang tải dữ liệu...</p>
                    </div>
                ) : error ? (
                    <div className="error-state">
                        <p>{error}</p>
                        <button onClick={fetchProducts} className="btn btn-secondary">
                            Thử lại
                        </button>
                    </div>
                ) : products.length === 0 ? (
                    <div className="empty-state">
                        <p>Không tìm thấy sản phẩm nào</p>
                        <button
                            onClick={() => navigate('/admin/products/create')}
                            className="btn btn-primary"
                        >
                            Thêm sản phẩm đầu tiên
                        </button>
                    </div>
                ) : (
                    <>
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>Tên sản phẩm</th>
                                    <th>SKU</th>
                                    <th>Danh mục</th>
                                    <th>Giá</th>
                                    <th>Trạng thái</th>
                                    <th>Biến thể</th>
                                    <th className="text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                {products.map((product) => (
                                    <tr key={product.uuid}>
                                        <td>
                                            <div className="product-info">
                                                <strong>{product.name}</strong>
                                            </div>
                                        </td>
                                        <td>{product.sku || '-'}</td>
                                        <td>{product.category?.name || '-'}</td>
                                        <td>
                                            {product.price
                                                ? product.price.toLocaleString('vi-VN') + ' đ'
                                                : '-'
                                            }
                                        </td>
                                        <td>
                                            <span className={`badge ${product.is_active ? 'badge-success' : 'badge-danger'}`}>
                                                {product.is_active ? 'Hoạt động' : 'Đã ẩn'}
                                            </span>
                                        </td>
                                        <td>
                                            {product.has_variants ? (
                                                <span className="badge badge-info">Có biến thể</span>
                                            ) : (
                                                <span className="badge badge-secondary">Đơn giản</span>
                                            )}
                                        </td>
                                        <td className="text-right">
                                            <div className="action-buttons">
                                                <button
                                                    className="btn-icon"
                                                    onClick={() => navigate(`/admin/products/${product.uuid}`)}
                                                    title="Xem chi tiết"
                                                >
                                                    <Eye size={16} />
                                                </button>
                                                <button
                                                    className="btn-icon"
                                                    onClick={() => navigate(`/admin/products/${product.uuid}/edit`)}
                                                    title="Chỉnh sửa"
                                                >
                                                    <Edit size={16} />
                                                </button>
                                                <button
                                                    className="btn-icon btn-danger"
                                                    onClick={() => handleDelete(product.uuid, product.name)}
                                                    title="Xóa"
                                                >
                                                    <Trash2 size={16} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        {/* Pagination */}
                        <div className="pagination">
                            <button
                                className="btn btn-secondary"
                                onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
                                disabled={currentPage === 1}
                            >
                                <ChevronLeft size={18} />
                                Trước
                            </button>

                            <span className="pagination-info">
                                Trang {currentPage} / {totalPages}
                            </span>

                            <button
                                className="btn btn-secondary"
                                onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
                                disabled={currentPage === totalPages}
                            >
                                Sau
                                <ChevronRight size={18} />
                            </button>
                        </div>
                    </>
                )}
            </div>


            <ConfirmDialog
                isOpen={confirmDialog.isOpen}
                onClose={() => setConfirmDialog(prev => ({ ...prev, isOpen: false }))}
                onConfirm={confirmDialog.onConfirm}
                title={confirmDialog.title}
                message={confirmDialog.message}
                isLoading={confirmDialog.isLoading}
            />
        </div >
    );
};

export default ProductList;
