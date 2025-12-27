import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { ShoppingBag, Search, RefreshCw, ChevronLeft, ChevronRight, MoreHorizontal, Calendar } from 'lucide-react';
import { useOrder } from '@/hooks/admin/useOrder';
import OrderActions from '@/components/admin/orders/OrderActions';
import './OrderList.css';

const OrderList = () => {
    const navigate = useNavigate();
    const { orders, loading, error, pagination, fetchOrders } = useOrder();
    
    // State quản lý filter
    const [filters, setFilters] = useState({
        status: '', 
        search: '',
        date_from: '',
        date_to: ''
    });

    // 1. Initial Load
    useEffect(() => {
        loadData(1);
        // eslint-disable-next-line
    }, []);

    // Hàm load data dùng chung
    const loadData = (page = 1, customFilters = null) => {
        const currentFilters = customFilters || filters;
        
        // Loại bỏ các key rỗng để URL sạch và backend xử lý đúng
        const params = { page };
        if (currentFilters.status) params.status = currentFilters.status;
        if (currentFilters.search) params.search = currentFilters.search;
        if (currentFilters.date_from) params.date_from = currentFilters.date_from;
        if (currentFilters.date_to) params.date_to = currentFilters.date_to;
        
        fetchOrders(params);
    };

    // --- LOGIC PHÂN TRANG THÔNG MINH ---
    const getPageNumbers = () => {
        const total = pagination?.last_page || 1;
        const current = pagination?.current_page || 1;
        const delta = 1; 
        const range = [];
        const rangeWithDots = [];
        let l;

        for (let i = 1; i <= total; i++) {
            if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
                range.push(i);
            }
        }

        for (let i of range) {
            if (l) {
                if (i - l === 2) {
                    rangeWithDots.push(l + 1);
                } else if (i - l !== 1) {
                    rangeWithDots.push('...');
                }
            }
            rangeWithDots.push(i);
            l = i;
        }
        return rangeWithDots;
    };

    // 2. Handlers

    // Xử lý thay đổi input chung
    const handleFilterChange = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
        
        // Nếu là status thì reload ngay, còn date/search thì đợi bấm nút hoặc enter
        if (key === 'status') {
             loadData(1, { ...filters, [key]: value });
        }
    };

    const handleSearchKeyDown = (e) => {
        if (e.key === 'Enter') {
            loadData(1);
        }
    };

    const handleRefresh = () => {
        const resetFilters = { status: '', search: '', date_from: '', date_to: '' };
        setFilters(resetFilters);
        loadData(1, resetFilters);
    };

    const handlePageChange = (newPage) => {
        if (newPage >= 1 && newPage <= (pagination?.last_page || 1) && newPage !== (pagination?.current_page || 1)) {
            loadData(newPage);
        }
    };

    // Helpers UI
    const getStatusBadge = (status, label) => {
        const statusMap = {
            pending: 'badge-warning',
            processing: 'badge-info',
            shipping: 'badge-primary',
            delivered: 'badge-success',
            cancelled: 'badge-danger',
        };
        const className = statusMap[status] || 'badge-secondary';
        return <span className={`status-badge ${className}`}>{label || status}</span>;
    };

    const getPaymentBadge = (status) => {
        const isPaid = status === 'paid';
        return (
            <span className={`payment-badge ${isPaid ? 'paid' : 'unpaid'}`}>
                {isPaid ? 'Paid' : 'Unpaid'}
            </span>
        );
    };

    return (
        <div className="order-list-container">
            {/* Page Header */}
            <div className="order-page-header">
                <div className="header-title">
                    <ShoppingBag size={24} className="text-gold" />
                    <div>
                        <h1>Order Management</h1>
                        <p>Manage customer orders and processing status</p>
                    </div>
                </div>
            </div>

            {/* Main Wrapper */}
            <div className="order-list-wrapper">
                {/* --- FILTERS BAR (UPDATED) --- */}
                <div className="order-filters">
                    
                    {/* Search Input */}
                    <div className="search-input-wrapper">
                        <Search size={18} className="search-icon" />
                        <input
                            placeholder="Search code, name..."
                            value={filters.search}
                            onChange={(e) => handleFilterChange('search', e.target.value)}
                            onKeyDown={handleSearchKeyDown}
                        />
                    </div>

                    {/* Date Range Filter */}
                    <div className="date-filter-group">
                        <div className="date-input-wrapper">
                            <input 
                                type="date" 
                                className="form-input-date"
                                value={filters.date_from}
                                onChange={(e) => handleFilterChange('date_from', e.target.value)}
                                title="Start Date"
                            />
                        </div>
                        <span className="date-separator">-</span>
                        <div className="date-input-wrapper">
                            <input 
                                type="date" 
                                className="form-input-date"
                                value={filters.date_to}
                                onChange={(e) => handleFilterChange('date_to', e.target.value)}
                                title="End Date"
                            />
                        </div>
                        <button 
                            className="btn-filter-apply" 
                            onClick={() => loadData(1)}
                            title="Apply Date Filter"
                        >
                            <Search size={14} /> Filter
                        </button>
                    </div>

                    {/* Status Select */}
                    <div className="status-select-wrapper">
                        <select
                            className="status-select"
                            value={filters.status}
                            onChange={(e) => handleFilterChange('status', e.target.value)}
                        >
                            <option value="">-- All Statuses --</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipping">Shipping</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    {/* Refresh Button */}
                    <button className="btn-refresh" onClick={handleRefresh} title="Reset All Filters">
                        <RefreshCw size={18} className={loading ? 'animate-spin' : ''} />
                    </button>
                </div>

                {/* Table Section */}
                <div className="order-table-container">
                    <div className="table-scroll">
                        {loading ? (
                            <div className="loading-state"><div className="spinner"></div></div>
                        ) : error ? (
                            <div className="error-state">{error}</div>
                        ) : (!orders || orders.length === 0) ? (
                            <div className="empty-state">No orders found matching criteria.</div>
                        ) : (
                            <table className="modern-table">
                                <thead>
                                    <tr>
                                        <th style={{ width: '20%', textAlign: 'left' }}>Order Code</th>
                                        <th style={{ width: '15%', textAlign: 'left' }}>Date</th>
                                        <th style={{ width: '15%', textAlign: 'left' }}>Customer</th>
                                        <th style={{ width: '15%', textAlign: 'left' }}>Total</th>
                                        <th style={{ width: '10%', textAlign: 'center' }}>Payment</th>
                                        <th style={{ width: '10%', textAlign: 'center' }}>Status</th>
                                        <th style={{ width: '15%', textAlign: 'right' }}>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {orders.map((order) => (
                                        <tr key={order.uuid}>
                                            <td style={{ textAlign: 'left' }}>
                                                <span className="font-mono font-bold text-gold">
                                                    #{order.code || order.uuid?.substring(0, 8).toUpperCase()}
                                                </span>
                                            </td>
                                            
                                            <td className="text-gray-600" style={{ textAlign: 'left' }}>
                                                {order.created_at_formatted || order.dates?.created_at}
                                            </td>

                                            <td className="text-gray-700 font-medium" style={{ textAlign: 'left' }}>
                                                {order.customer?.name || order.shipping_info?.name || 'Guest'}
                                            </td>
                                            
                                            <td style={{ textAlign: 'left' }}>
                                                <div className="price-cell">
                                                    <span className="font-bold text-navy">
                                                        {order.grand_total_formatted || order.amounts?.grand_total}
                                                    </span>
                                                    <span className="text-xs text-gray-400">
                                                        {order.items?.length || 0} items
                                                    </span>
                                                </div>
                                            </td>

                                            <td style={{ textAlign: 'center' }}>
                                                {getPaymentBadge(order.payment_status)}
                                            </td>

                                            <td style={{ textAlign: 'center' }}>
                                                {getStatusBadge(order.status, order.status_label)}
                                            </td>
                                            
                                            <td className="cell-actions text-right">
                                                <OrderActions
                                                    order={order}
                                                    onUpdate={() => loadData(pagination.current_page)}
                                                    showDetailBtn={true}
                                                />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </div>

                    {/* Pagination */}
                    {pagination && pagination.total > 0 && (
                        <div className="pagination-wrapper">
                            <div className="pagination-info">
                                Showing <b>{orders.length}</b> of <b>{pagination.total}</b> results
                            </div>
                            
                            <div className="pagination-actions">
                                <button className="page-btn nav-btn" disabled={pagination.current_page === 1} onClick={() => handlePageChange(pagination.current_page - 1)}>
                                    <ChevronLeft size={16} />
                                </button>
                                {getPageNumbers().map((page, index) => (
                                    page === '...' ? (
                                        <span key={`dots-${index}`} className="page-dots"><MoreHorizontal size={14} /></span>
                                    ) : (
                                        <button key={page} className={`page-btn ${page === pagination.current_page ? 'active' : ''}`} onClick={() => handlePageChange(page)}>
                                            {page}
                                        </button>
                                    )
                                ))}
                                <button className="page-btn nav-btn" disabled={pagination.current_page === pagination.last_page} onClick={() => handlePageChange(pagination.current_page + 1)}>
                                    <ChevronRight size={16} />
                                </button>
                            </div>
                            <div className="pagination-spacer"></div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default OrderList;