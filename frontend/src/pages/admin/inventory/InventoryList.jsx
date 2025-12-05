import React, { useEffect, useState } from 'react';
import { Search, Package, AlertTriangle, CheckCircle, XCircle, Warehouse } from 'lucide-react';
import InventoryService from '@/services/admin/InventoryService';
import './InventoryList.css';

const InventoryList = () => {
    const [inventories, setInventories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [warehouseFilter, setWarehouseFilter] = useState('');

    const fetchInventories = async () => {
        try {
            setLoading(true);
            setError(null);

            const params = {};
            if (searchTerm) params.search = searchTerm;
            if (warehouseFilter) params.warehouse_uuid = warehouseFilter;

            const response = await InventoryService.getInventories(params);

            if (response.success && response.data) {
                // Handle both paginated and direct array response
                const inventoryList = Array.isArray(response.data)
                    ? response.data
                    : response.data.data || [];
                setInventories(inventoryList);
            }
        } catch (err) {
            setError(err.message || 'Không thể tải danh sách tồn kho');
            console.error('Error fetching inventories:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchInventories();
    }, [searchTerm, warehouseFilter]);

    const getStatusBadge = (stock) => {
        if (!stock.quantity || stock.quantity <= 0) {
            return (
                <span className="badge badge-danger">
                    <XCircle size={14} /> Hết hàng
                </span>
            );
        }
        if (stock.quantity <= stock.min_threshold) {
            return (
                <span className="badge badge-warning">
                    <AlertTriangle size={14} /> Sắp hết
                </span>
            );
        }
        return (
            <span className="badge badge-success">
                <CheckCircle size={14} /> Còn hàng
            </span>
        );
    };

    return (
        <div className="inventory_list">
            <div className="page-header">
                <div>
                    <h1><Warehouse size={24} /> Quản lý Tồn kho</h1>
                    <p className="page-subtitle">Theo dõi số lượng tồn kho theo kho hàng</p>
                </div>
            </div>

            <div className="filters-section">
                <div className="search-box">
                    <Search size={20} />
                    <input
                        type="text"
                        placeholder="Tìm theo tên sản phẩm hoặc SKU..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
            </div>

            <div className="table-container">
                {loading ? (
                    <div className="loading-state">
                        <div className="spinner"></div>
                        <p>Đang tải dữ liệu...</p>
                    </div>
                ) : error ? (
                    <div className="error-state">
                        <p>{error}</p>
                        <button onClick={fetchInventories} className="btn btn-secondary">
                            Thử lại
                        </button>
                    </div>
                ) : inventories.length === 0 ? (
                    <div className="empty-state">
                        <Package size={48} color="#9ca3af" />
                        <p>Chưa có dữ liệu tồn kho</p>
                    </div>
                ) : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Sản phẩm</th>
                                <th>Kho hàng</th>
                                <th>Số lượng</th>
                                <th>Ngưỡng tối thiểu</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            {inventories.map((stock) => (
                                <tr key={stock.uuid || stock.id}>
                                    <td>
                                        <code>{stock.variant?.sku || '-'}</code>
                                    </td>
                                    <td>
                                        <div className="product-info">
                                            <strong>{stock.variant?.product?.name || 'N/A'}</strong>
                                            {stock.variant?.attribute_values && stock.variant.attribute_values.length > 0 && (
                                                <span className="variant-info">
                                                    {stock.variant.attribute_values.map(av => av.value).join(' / ')}
                                                </span>
                                            )}
                                        </div>
                                    </td>
                                    <td>{stock.warehouse?.name || '-'}</td>
                                    <td>
                                        <strong className={stock.quantity <= stock.min_threshold ? 'text-warning' : ''}>
                                            {stock.quantity || 0}
                                        </strong>
                                    </td>
                                    <td>{stock.min_threshold || 0}</td>
                                    <td>{getStatusBadge(stock)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
        </div>
    );
};

export default InventoryList;
