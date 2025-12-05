import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Plus,
    Search,
    Filter,
    Edit,
    Trash2,
    Eye,
    ChevronLeft,
    ChevronRight,
    Package,
    Tag,
    RefreshCw,
    CheckCircle,
    XCircle,
    Layers,
    TrendingUp,
    MoreVertical,
    Download,
    Upload
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

    // State management
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [totalItems, setTotalItems] = useState(0);
    const [perPage] = useState(20);
    const [searchTerm, setSearchTerm] = useState('');
    const [filterStatus, setFilterStatus] = useState('all');
    const [selectedRows, setSelectedRows] = useState(new Set());
    const [sortBy, setSortBy] = useState('created_at');
    const [sortOrder, setSortOrder] = useState('desc');

    // Fetch products
    const fetchProducts = async () => {
        try {
            setLoading(true);
            setError(null);

            const params = {
                page: currentPage,
                per_page: perPage,
                sort_by: sortBy,
                sort_order: sortOrder,
            };

            if (searchTerm) {
                params.search = searchTerm;
            }

            if (filterStatus !== 'all') {
                params.is_active = filterStatus === 'active';
            }

            const response = await ProductService.getProducts(params);

            if (response.success && response.data) {
                setProducts(response.data);

                if (response.meta) {
                    setTotalPages(response.meta.last_page || 1);
                    setTotalItems(response.meta.total || response.data.length);
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
    }, [currentPage, filterStatus, sortBy, sortOrder]);

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
            confirmText: 'Xóa',
            cancelText: 'Hủy',
            onConfirm: async () => {
                setConfirmDialog(prev => ({ ...prev, isLoading: true }));
                try {
                    await ProductService.deleteProduct(uuid);
                    setConfirmDialog(prev => ({ ...prev, isOpen: false }));
                    fetchProducts();
                    setSelectedRows(new Set());
                } catch (err) {
                    setConfirmDialog(prev => ({ ...prev, isOpen: false }));
                    alert('Lỗi khi xóa sản phẩm: ' + err.message);
                }
            }
        });
    };

    const handleSelectAll = () => {
        if (selectedRows.size === products.length) {
            setSelectedRows(new Set());
        } else {
            setSelectedRows(new Set(products.map(p => p.uuid)));
        }
    };

    const handleSelectRow = (uuid) => {
        const newSelected = new Set(selectedRows);
        if (newSelected.has(uuid)) {
            newSelected.delete(uuid);
        } else {
            newSelected.add(uuid);
        }
        setSelectedRows(newSelected);
    };

    const handleSort = (column) => {
        if (sortBy === column) {
            setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc');
        } else {
            setSortBy(column);
            setSortOrder('desc');
        }
    };

    const getSortIcon = (column) => {
        if (sortBy !== column) return null;
        return sortOrder === 'asc' ? '↑' : '↓';
    };

    const formatPrice = (price) => {
        if (!price || price === '0.00') return '-';
        return parseInt(price).toLocaleString('vi-VN') + ' đ';
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    };

    const handleQuickToggle = async (product, field) => {
        try {
            await ProductService.updateProduct(product.uuid, {
                [field]: !product[field]
            });
            fetchProducts();
        } catch (err) {
            alert('Lỗi khi cập nhật: ' + err.message);
        }
    };

    return (
        <div className="product_list-container">
            {/* Header */}
            <div className="list-header">
                <div className="header-content">
                    <div>
                        <h1>
                            <Package size={24} />
                            Danh sách sản phẩm
                        </h1>
                        <p className="subtitle">
                            {totalItems} sản phẩm • Trang {currentPage}/{totalPages}
                        </p>
                    </div>

                    <div className="header-actions">
                        <div className="quick-stats">
                            <span className="stat-item">
                                <CheckCircle size={14} />
                                <span>{products.filter(p => p.is_active).length} hoạt động</span>
                            </span>
                            <span className="stat-item">
                                <Layers size={14} />
                                <span>{products.filter(p => p.has_variants).length} biến thể</span>
                            </span>
                        </div>

                        <button
                            className="btn btn-primary"
                            onClick={() => navigate('/admin/products/create')}
                        >
                            <Plus size={18} />
                            Thêm sản phẩm
                        </button>
                    </div>
                </div>

                {/* Search & Filters */}
                <div className="search-filters">
                    <form onSubmit={handleSearch} className="search-form">
                        <div className="search-box">
                            <Search size={18} />
                            <input
                                type="text"
                                placeholder="Tìm theo tên, SKU, mô tả..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                            />
                            {searchTerm && (
                                <button
                                    type="button"
                                    className="clear-btn"
                                    onClick={() => setSearchTerm('')}
                                >
                                    ✕
                                </button>
                            )}
                        </div>
                    </form>

                    <div className="filter-actions">
                        <select
                            value={filterStatus}
                            onChange={(e) => {
                                setFilterStatus(e.target.value);
                                setCurrentPage(1);
                            }}
                            className="filter-select"
                        >
                            <option value="all">Tất cả</option>
                            <option value="active">Đang bán</option>
                            <option value="inactive">Ngừng bán</option>
                            <option value="has_variants">Có biến thể</option>
                        </select>

                        <button
                            className="btn btn-secondary"
                            onClick={() => fetchProducts()}
                            title="Làm mới"
                        >
                            <RefreshCw size={16} />
                        </button>

                        {selectedRows.size > 0 && (
                            <button
                                className="btn btn-danger"
                                onClick={() => {
                                    if (window.confirm(`Xóa ${selectedRows.size} sản phẩm đã chọn?`)) {
                                        // Implement bulk delete
                                        console.log('Delete selected:', Array.from(selectedRows));
                                    }
                                }}
                            >
                                <Trash2 size={16} />
                                Xóa ({selectedRows.size})
                            </button>
                        )}
                    </div>
                </div>
            </div>

            {/* Table Container */}
            <div className="table-wrapper">
                {loading ? (
                    <div className="loading-state">
                        <div className="spinner"></div>
                        <p>Đang tải sản phẩm...</p>
                    </div>
                ) : error ? (
                    <div className="error-state">
                        <div className="error-icon">⚠️</div>
                        <p>{error}</p>
                        <button onClick={fetchProducts} className="btn btn-secondary">
                            Thử lại
                        </button>
                    </div>
                ) : products.length === 0 ? (
                    <div className="empty-state">
                        <Package size={48} />
                        <h3>Không tìm thấy sản phẩm</h3>
                        <p>{searchTerm ? 'Thử tìm kiếm với từ khóa khác' : 'Bắt đầu bằng cách thêm sản phẩm đầu tiên'}</p>
                        <button
                            onClick={() => navigate('/admin/products/create')}
                            className="btn btn-primary"
                        >
                            <Plus size={16} />
                            Thêm sản phẩm mới
                        </button>
                    </div>
                ) : (
                    <>
                        <div className="table-responsive">
                            <table className="product-table">
                                <thead>
                                    <tr>
                                        <th style={{ width: '50px' }}>
                                            <input
                                                type="checkbox"
                                                checked={selectedRows.size === products.length && products.length > 0}
                                                onChange={handleSelectAll}
                                            />
                                        </th>
                                        <th
                                            style={{ width: '300px', cursor: 'pointer' }}
                                            onClick={() => handleSort('name')}
                                        >
                                            <div className="column-header">
                                                Sản phẩm
                                                <span className="sort-indicator">{getSortIcon('name')}</span>
                                            </div>
                                        </th>
                                        <th
                                            style={{ width: '120px', cursor: 'pointer' }}
                                            onClick={() => handleSort('sku')}
                                        >
                                            <div className="column-header">
                                                SKU
                                                <span className="sort-indicator">{getSortIcon('sku')}</span>
                                            </div>
                                        </th>
                                        <th
                                            style={{ width: '150px', cursor: 'pointer' }}
                                            onClick={() => handleSort('price')}
                                        >
                                            <div className="column-header">
                                                Giá
                                                <span className="sort-indicator">{getSortIcon('price')}</span>
                                            </div>
                                        </th>
                                        <th style={{ width: '100px' }}>
                                            Trạng thái
                                        </th>
                                        <th
                                            style={{ width: '150px', cursor: 'pointer' }}
                                            onClick={() => handleSort('created_at')}
                                        >
                                            <div className="column-header">
                                                Ngày tạo
                                                <span className="sort-indicator">{getSortIcon('created_at')}</span>
                                            </div>
                                        </th>
                                        <th style={{ width: '120px' }}>
                                            Thao tác
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {products.map((product) => (
                                        <tr
                                            key={product.uuid}
                                            className={selectedRows.has(product.uuid) ? 'selected' : ''}
                                        >
                                            <td>
                                                <input
                                                    type="checkbox"
                                                    checked={selectedRows.has(product.uuid)}
                                                    onChange={() => handleSelectRow(product.uuid)}
                                                />
                                            </td>
                                            <td>
                                                <div className="product-cell">
                                                    <div className="product-image">
                                                        <Package size={20} />
                                                    </div>
                                                    <div className="product-info">
                                                        <div className="product-name">
                                                            {product.name}
                                                        </div>
                                                        <div className="product-description">
                                                            {product.description?.substring(0, 60)}
                                                            {product.description && product.description.length > 60 ? '...' : ''}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span className="sku-value">
                                                    {product.sku || 'N/A'}
                                                </span>
                                            </td>
                                            <td>
                                                <div className="price-cell">
                                                    <span className="price-value">
                                                        {formatPrice(product.price)}
                                                    </span>
                                                    {product.has_variants && (
                                                        <span className="variants-badge">
                                                            <Layers size={12} />
                                                        </span>
                                                    )}
                                                </div>
                                            </td>
                                            <td>
                                                <div className="status-cell">
                                                    <button
                                                        className={`status-toggle ${product.is_active ? 'active' : 'inactive'}`}
                                                        onClick={() => handleQuickToggle(product, 'is_active')}
                                                        title={product.is_active ? 'Đang bán' : 'Ngừng bán'}
                                                    >
                                                        {product.is_active ? (
                                                            <CheckCircle size={14} />
                                                        ) : (
                                                            <XCircle size={14} />
                                                        )}
                                                    </button>
                                                    <span className="status-label">
                                                        {product.is_active ? 'Đang bán' : 'Ngừng bán'}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div className="date-cell">
                                                    {formatDate(product.created_at)}
                                                </div>
                                            </td>
                                            <td>
                                                <div className="action-cell">
                                                    <button
                                                        className="action-btn view"
                                                        onClick={() => navigate(`/admin/products/${product.uuid}`)}
                                                        title="Xem chi tiết"
                                                    >
                                                        <Eye size={16} />
                                                    </button>
                                                    <button
                                                        className="action-btn edit"
                                                        onClick={() => navigate(`/admin/products/${product.uuid}/edit`)}
                                                        title="Chỉnh sửa"
                                                    >
                                                        <Edit size={16} />
                                                    </button>
                                                    <button
                                                        className="action-btn delete"
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
                        </div>

                        {/* Pagination */}
                        <div className="pagination">
                            <div className="pagination-info">
                                Hiển thị {(currentPage - 1) * perPage + 1} - {Math.min(currentPage * perPage, totalItems)} / {totalItems} sản phẩm
                            </div>

                            <div className="pagination-controls">
                                <button
                                    className="page-btn"
                                    onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
                                    disabled={currentPage === 1}
                                >
                                    <ChevronLeft size={18} />
                                </button>

                                {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                                    let pageNum;
                                    if (totalPages <= 5) {
                                        pageNum = i + 1;
                                    } else if (currentPage <= 3) {
                                        pageNum = i + 1;
                                    } else if (currentPage >= totalPages - 2) {
                                        pageNum = totalPages - 4 + i;
                                    } else {
                                        pageNum = currentPage - 2 + i;
                                    }

                                    return (
                                        <button
                                            key={pageNum}
                                            className={`page-btn ${currentPage === pageNum ? 'active' : ''}`}
                                            onClick={() => setCurrentPage(pageNum)}
                                        >
                                            {pageNum}
                                        </button>
                                    );
                                })}

                                <button
                                    className="page-btn"
                                    onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
                                    disabled={currentPage === totalPages}
                                >
                                    <ChevronRight size={18} />
                                </button>
                            </div>

                            <div className="per-page-select">
                                <span>Hiển thị:</span>
                                <select value={perPage} disabled>
                                    <option value="20">20/dòng</option>
                                    <option value="50">50/dòng</option>
                                    <option value="100">100/dòng</option>
                                </select>
                            </div>
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
                confirmText={confirmDialog.confirmText}
                cancelText={confirmDialog.cancelText}
                isLoading={confirmDialog.isLoading}
            />
        </div>
    );
};

export default ProductList;