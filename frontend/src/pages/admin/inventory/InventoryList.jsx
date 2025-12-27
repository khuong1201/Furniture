import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Search, ArrowRightLeft, RefreshCw, ChevronLeft, ChevronRight, AlertCircle } from 'lucide-react'; // Xóa Box import
import { useInventory } from '@/hooks/admin/useInventory';
import { useWarehouse } from '@/hooks/admin/useWarehouse';
import './InventoryList.css';

const InventoryList = () => {
    const navigate = useNavigate();
    const { stocks, meta, loading, fetchStocks } = useInventory();
    const { warehouses, fetchWarehouses } = useWarehouse();
    
    const [filters, setFilters] = useState({ 
        warehouse_uuid: '', 
        search: '',
        status: '' 
    });

    // 1. Initial Load
    useEffect(() => {
        fetchWarehouses({ per_page: 100, is_active: 1 });
        fetchStocks({ page: 1, per_page: 20 });
    }, []);

    // 2. Handle Filter Change
    const handleWarehouseChange = (uuid) => {
        const newFilters = { ...filters, warehouse_uuid: uuid };
        setFilters(newFilters);
        fetchStocks({ ...newFilters, page: 1 });
    };

    const handleSearchKeyDown = (e) => {
        if (e.key === 'Enter') {
            fetchStocks({ ...filters, page: 1 });
        }
    };

    const handleRefresh = () => {
        const resetFilters = { warehouse_uuid: '', search: '' };
        setFilters(resetFilters);
        fetchStocks({ page: 1, per_page: 20, ...resetFilters });
    };

    const handlePageChange = (newPage) => {
        fetchStocks({ ...filters, page: newPage });
    };

    return (
        <div className="inventory-list-wrapper">
            {/* Filters */}
            <div className="inventory-filters">
                <div className="search-input-wrapper">
                    <Search size={18} className="search-icon" />
                    <input
                        placeholder="Search SKU or Product Name..."
                        value={filters.search}
                        onChange={(e) => setFilters(p => ({ ...p, search: e.target.value }))}
                        onKeyDown={handleSearchKeyDown}
                    />
                </div>

                <div className="warehouse-select-wrapper">
                    <select
                        className="warehouse-select"
                        value={filters.warehouse_uuid}
                        onChange={(e) => handleWarehouseChange(e.target.value)}
                    >
                        <option value="">-- All Warehouses --</option>
                        {warehouses.map(wh => (
                            <option key={wh.uuid} value={wh.uuid}>{wh.name}</option>
                        ))}
                    </select>
                </div>
                <div className="status-select-wrapper">
                    <select
                        className="status-select"
                        value={filters.status}
                        onChange={(e) => {
                            const newFilters = { ...filters, status: e.target.value };
                            setFilters(newFilters);
                            fetchStocks({ ...newFilters, page: 1 });
                        }}
                    >
                        <option value="">-- All Status --</option>
                        <option value="in_stock">In Stock</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                        <option value="old_stock">Old Stock</option>
                    </select>
                </div>

                <button className="btn-refresh" onClick={handleRefresh} title="Reset & Refresh">
                    <RefreshCw size={18} className={loading ? 'animate-spin' : ''} />
                </button>
            </div>

            {/* Table */}
            <div className="inventory-table-container">
                <div className="table-scroll">
                    <table className="modern-table">
                        <thead>
                            <tr>
                                {/* Thêm text-align left rõ ràng */}
                                <th style={{ width: '40%', textAlign: 'left' }}>Product / SKU</th>
                                <th style={{ width: '20%', textAlign: 'left' }}>Warehouse</th>
                                <th className="text-center" style={{ width: '15%' }}>Quantity</th>
                                <th style={{ width: '15%' }}>Status</th>
                                <th style={{ textAlign: 'right', width: '10%' }}>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr><td colSpan="5" className="empty-state"><span className="animate-spin inline-block mr-2">⟳</span> Loading inventory...</td></tr>
                            ) : stocks.length > 0 ? (
                                stocks.map(item => {
                                    const productName = item.variant?.product?.name || item.variant?.name || 'Unnamed Product';
                                    const variantLabel = (item.variant?.product?.name && item.variant?.name && item.variant.name !== item.variant.product.name) 
                                        ? `(${item.variant.name})` 
                                        : '';
                                    const sku = item.variant?.sku || 'N/A';
                                    const warehouseName = item.warehouse?.name || 'Unknown Warehouse';

                                    return (
                                        <tr key={item.uuid}>
                                            <td style={{ textAlign: 'left' }}>
                                                <div className="product-cell">
                                                    <div className="product-name">
                                                        {productName} <span className="text-gray-400 text-xs font-normal">{variantLabel}</span>
                                                    </div>
                                                    <div className="product-sku">
                                                        <span className="sku-tag">{sku}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            {/* Cột Warehouse: Đã xóa icon Box */}
                                            <td style={{ textAlign: 'left' }}>
                                                <div className="text-sm text-gray-700 font-medium">
                                                    {warehouseName}
                                                </div>
                                            </td>
                                            <td className="text-center">
                                                <span className={`qty-badge ${item.status === 'low_stock' ? 'low' : ''}`}>
                                                    {item.quantity}
                                                </span>
                                            </td>
                                            <td>
                                                <span className={`status-badge ${item.status_color}`}>
                                                    {item.status_label}
                                                </span>
                                            </td>
                                            <td>
                                                <div className="table-actions">
                                                    <button 
                                                        className="table-action-btn" 
                                                        title="Adjust Stock"
                                                        onClick={() => navigate(`/admin/inventories/${item.uuid}/adjust`)}
                                                    >
                                                        <ArrowRightLeft size={16} />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })
                            ) : (
                                <tr><td colSpan="5" className="empty-state">No inventory records found.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {meta.last_page > 1 && (
                    <div className="pagination-wrapper">
                        <div className="pagination-info">
                            Showing <b>{stocks.length}</b> of <b>{meta.total}</b> items
                        </div>
                        <div className="pagination-actions">
                            <button disabled={meta.current_page === 1} onClick={() => handlePageChange(meta.current_page - 1)}>
                                <ChevronLeft size={18} />
                            </button>
                            <span className="page-number">{meta.current_page}</span>
                            <button disabled={meta.current_page === meta.last_page} onClick={() => handlePageChange(meta.current_page + 1)}>
                                <ChevronRight size={18} />
                            </button>
                        </div>
                        <div className="pagination-spacer"></div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default InventoryList;