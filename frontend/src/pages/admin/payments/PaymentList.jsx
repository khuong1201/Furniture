import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { CreditCard, Search, RefreshCw, ChevronLeft, ChevronRight, MoreHorizontal, Calendar } from 'lucide-react';
import { usePayment } from '@/hooks/admin/usePayment';
import PaymentActions from '@/components/admin/payments/PaymentActions'; 
import './PaymentList.css';

const PaymentList = () => {
    const navigate = useNavigate();
    const { 
        payments, loading, pagination, fetchPayments 
    } = usePayment();

    const [filters, setFilters] = useState({ 
        status: '', 
        search: '',
        date_from: '',
        date_to: ''
    });

    useEffect(() => { 
        loadData(1); 
        // eslint-disable-next-line
    }, []);

    const loadData = (page = 1, customFilters = null) => {
        const currentFilters = customFilters || filters;
        const params = { page };
        
        if (currentFilters.status) params.status = currentFilters.status;
        if (currentFilters.search) params.search = currentFilters.search;
        if (currentFilters.date_from) params.date_from = currentFilters.date_from;
        if (currentFilters.date_to) params.date_to = currentFilters.date_to;

        fetchPayments(params);
    };

    const handleFilterChange = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
        if (key === 'status') {
             loadData(1, { ...filters, [key]: value });
        }
    };

    const handleSearchKeyDown = (e) => {
        if (e.key === 'Enter') loadData(1);
    };

    const handleRefresh = () => {
        const reset = { status: '', search: '', date_from: '', date_to: '' };
        setFilters(reset);
        loadData(1, reset);
    };

    const handlePageChange = (newPage) => {
        if (newPage >= 1 && newPage <= (pagination?.last_page || 1) && newPage !== pagination?.current_page) {
            loadData(newPage);
        }
    };

    // --- SMART PAGINATION LOGIC ---
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

    const getStatusBadge = (status) => {
        const statusMap = {
            paid: 'p-badge-success',
            pending: 'p-badge-warning',
            failed: 'p-badge-danger',
            refunded: 'p-badge-info',
        };
        return <span className={`p-status-badge ${statusMap[status] || 'p-badge-secondary'}`}>
            {status?.toUpperCase()}
        </span>;
    };

    return (
        <div className="p-list-container">
            <div className="p-page-header">
                <div className="p-header-title">
                    <CreditCard size={24} className="p-text-gold" />
                    <div>
                        <h1>Payment Transactions</h1>
                        <p>Monitor revenue and transaction status</p>
                    </div>
                </div>
            </div>

            <div className="p-list-wrapper">
                {/* --- FILTERS BAR --- */}
                <div className="p-filters-bar">
                    <div className="p-search-wrapper">
                        <Search size={18} className="p-search-icon" />
                        <input
                            placeholder="Search Order Code, Txn ID..."
                            value={filters.search}
                            onChange={(e) => handleFilterChange('search', e.target.value)}
                            onKeyDown={handleSearchKeyDown}
                        />
                    </div>

                    {/* Date Range */}
                    <div className="p-date-group">
                        <div className="p-date-input">
                            <input type="date" value={filters.date_from} onChange={(e) => handleFilterChange('date_from', e.target.value)} />
                        </div>
                        <span className="p-date-sep">-</span>
                        <div className="p-date-input">
                            <input type="date" value={filters.date_to} onChange={(e) => handleFilterChange('date_to', e.target.value)} />
                        </div>
                        <button className="p-btn-filter" onClick={() => loadData(1)}>
                            <Search size={14} /> Filter
                        </button>
                    </div>

                    <div className="p-select-wrapper">
                        <select
                            className="p-status-select"
                            value={filters.status}
                            onChange={(e) => handleFilterChange('status', e.target.value)}
                        >
                            <option value="">-- All Statuses --</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>

                    <button className="p-btn-refresh" onClick={handleRefresh}>
                        <RefreshCw size={18} className={loading ? 'animate-spin' : ''} />
                    </button>
                </div>

                <div className="p-table-area">
                    <div className="p-table-scroll">
                        {loading && payments.length === 0 ? (
                            <div className="p-loading-state"><div className="p-spinner"></div></div>
                        ) : (!payments || payments.length === 0) ? (
                            <div className="p-empty-state">No transactions found.</div>
                        ) : (
                            <table className="p-modern-table">
                                <thead>
                                    <tr>
                                        <th style={{ width: '20%' }}>Order Code</th>
                                        <th style={{ width: '20%' }}>Date</th>
                                        <th style={{ width: '15%' }}>Txn ID</th>
                                        <th style={{ width: '10%' }}>Method</th>
                                        <th style={{ width: '15%' }}>Amount</th>
                                        <th style={{ textAlign: 'center', width: '10%' }}>Status</th>
                                        <th style={{ textAlign: 'right', width: '10%' }}>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {payments.map((p) => (
                                        <tr key={p.uuid}>
                                            <td><span className="p-order-code">#{p.order?.code || 'N/A'}</span></td>
                                            <td>
                                                <div className="p-date-cell">
                                                    <span className="p-main-date">{p.dates?.created_at || 'N/A'}</span>
                                                </div>
                                            </td>
                                            <td className="text-gray-500 font-mono text-xs">
                                                {p.transaction_id || '-'}
                                            </td>
                                            <td><span className="p-method-tag">{p.method?.toUpperCase() || 'COD'}</span></td>
                                            <td><span className="p-amount-text">{p.amount_formatted}</span></td>
                                            <td style={{ textAlign: 'center' }}>{getStatusBadge(p.status)}</td>
                                            <td style={{ textAlign: 'right' }}>
                                                <PaymentActions 
                                                    payment={p} 
                                                    onUpdate={() => loadData(pagination.current_page)}
                                                    onViewDetail={() => navigate(`/admin/payments/${p.uuid}`)} 
                                                />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </div>

                    {/* --- PAGINATION (UPDATED) --- */}
                    {pagination && pagination.total > 0 && (
                        <div className="p-pagination">
                            <div className="p-pagination-info">Showing <b>{payments.length}</b> of <b>{pagination.total}</b> results</div>
                            <div className="p-pagination-btns">
                                <button disabled={pagination.current_page === 1} onClick={() => handlePageChange(pagination.current_page - 1)}><ChevronLeft size={16}/></button>
                                
                                {getPageNumbers().map((page, index) => (
                                    page === '...' ? (
                                        <span key={`dots-${index}`} className="p-page-dots"><MoreHorizontal size={14}/></span>
                                    ) : (
                                        <button 
                                            key={page} 
                                            className={page === pagination.current_page ? 'active' : ''}
                                            onClick={() => handlePageChange(page)}
                                        >
                                            {page}
                                        </button>
                                    )
                                ))}

                                <button disabled={pagination.current_page === pagination.last_page} onClick={() => handlePageChange(pagination.current_page + 1)}><ChevronRight size={16}/></button>
                            </div>
                            <div className="p-pagination-spacer"></div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default PaymentList;