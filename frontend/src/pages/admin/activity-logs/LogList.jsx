import React, { useState, useEffect } from 'react';
import {
    Search, Activity, User, Clock, Eye,
    ChevronLeft, ChevronRight, Loader, AlertCircle
} from 'lucide-react';
import { useLog } from '@/hooks/admin/useLog'; // Import hook mới
import Modal from '@/components/admin/shared/Modal';
import './LogList.css';

const LogList = () => {
    const { 
        logs, 
        selectedLog, 
        meta, 
        loading, 
        error, 
        fetchLogs, 
        fetchLogById, 
        setError,
        setSelectedLog 
    } = useLog();

    // Local State cho UI/Filter
    const [searchTerm, setSearchTerm] = useState('');
    const [filterType, setFilterType] = useState('');
    const [isDetailOpen, setIsDetailOpen] = useState(false);

    // 1. Load Data khi page hoặc filter thay đổi
    // Lưu ý: search term xử lý riêng ở onKeyDown để tránh spam API
    useEffect(() => {
        loadData(1);
    }, [filterType]);

    const loadData = (page = 1, search = searchTerm) => {
        const params = { 
            page, 
            per_page: 20, // Số lượng item mỗi trang
        };
        
        if (filterType) params.type = filterType;
        if (search) params.q = search; // Gửi từ khóa tìm kiếm lên server

        fetchLogs(params);
    };

    // 2. Handlers
    const handleSearchKeyDown = (e) => {
        if (e.key === 'Enter') {
            loadData(1); // Reset về trang 1 khi tìm kiếm
        }
    };

    const handlePageChange = (newPage) => {
        if (newPage >= 1 && newPage <= meta.last_page) {
            loadData(newPage);
        }
    };

    const handleViewDetail = async (log) => {
        // Nếu log đã có đủ data thì set luôn, không thì fetch lại để lấy full properties
        if (log.properties) {
            setSelectedLog(log);
        } else {
            await fetchLogById(log.uuid);
        }
        setIsDetailOpen(true);
    };

    // Helpers UI
    const getActionColor = (action) => {
        const colors = {
            'create': 'success', 'update': 'warning',
            'delete': 'error', 'login': 'info', 'logout': 'neutral'
        };
        return colors[action?.toLowerCase()] || 'neutral';
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleString('vi-VN');
    };

    // Loading State (chỉ hiện khi chưa có data lần đầu)
    if (loading && logs.length === 0) {
        return (
            <div className="log_loading-state">
                <Loader className="spinner" size={40} />
                <p>Đang tải dữ liệu...</p>
            </div>
        );
    }

    return (
        <div className="log_list-page">
            <div className="log_page-header">
                <div className="page-title">
                    <h1>Nhật ký hoạt động</h1>
                    <p className="log_page-subtitle">Xem lịch sử hoạt động trong hệ thống</p>
                </div>
            </div>

            {error && (
                <div className="error-alert">
                    <AlertCircle size={20} />
                    <span>{error}</span>
                    <button onClick={() => setError(null)}>×</button>
                </div>
            )}

            {/* Filters Bar */}
            <div className="search-filters">
                <div className="log_search-box">
                    <Search size={20} className="search-icon" />
                    <input
                        type="text"
                        placeholder="Tìm kiếm hoạt động (Enter)..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        onKeyDown={handleSearchKeyDown}
                    />
                </div>
                <select
                    className="log_filter-select"
                    value={filterType}
                    onChange={(e) => setFilterType(e.target.value)}
                >
                    <option value="">Tất cả hành động</option>
                    <option value="create">Tạo mới</option>
                    <option value="update">Cập nhật</option>
                    <option value="delete">Xóa</option>
                    <option value="login">Đăng nhập</option>
                </select>
            </div>

            {/* Table */}
            <div className="log-table-wrapper">
                {logs.length === 0 ? (
                    <div className="log_empty-state">
                        <Activity size={64} />
                        <h3>Không tìm thấy hoạt động nào</h3>
                        <p>Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                    </div>
                ) : (
                    <table className="log-table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Người dùng</th>
                                <th>Hành động</th>
                                <th>Mô tả</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {logs.map((log) => (
                                <tr key={log.uuid || log.id}>
                                    <td>
                                        <div className="time-cell">
                                            <Clock size={14} />
                                            <span>{formatDate(log.created_at)}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div className="user-cell">
                                            <div className="user-avatar"><User size={14} /></div>
                                            <span>{log.user?.name || log.causer?.name || 'System'}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span className={`log_action-badge ${getActionColor(log.action || log.event)}`}>
                                            {log.action || log.event || '-'}
                                        </span>
                                    </td>
                                    <td className="desc-cell">{log.description || '-'}</td>
                                    <td>
                                        <button
                                            className="action-btn btn-view"
                                            onClick={() => handleViewDetail(log)}
                                            title="Xem chi tiết"
                                        >
                                            <Eye size={16} />
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>

            {/* Pagination */}
            {meta.last_page > 1 && (
                <div className="log_pagination">
                    <button 
                        disabled={meta.current_page === 1} 
                        onClick={() => handlePageChange(meta.current_page - 1)}
                    >
                        <ChevronLeft size={18} />
                    </button>
                    
                    {/* Logic hiển thị số trang đơn giản */}
                    <span className="page-info">
                        Trang {meta.current_page} / {meta.last_page}
                    </span>

                    <button 
                        disabled={meta.current_page === meta.last_page} 
                        onClick={() => handlePageChange(meta.current_page + 1)}
                    >
                        <ChevronRight size={18} />
                    </button>
                </div>
            )}

            {/* Modal Chi Tiết */}
            <Modal 
                isOpen={isDetailOpen} 
                onClose={() => setIsDetailOpen(false)} 
                title="Chi tiết hoạt động" 
                size="md"
            >
                {selectedLog && (
                    <div className="log-detail">
                        <div className="detail-row">
                            <label>Thời gian:</label>
                            <span>{formatDate(selectedLog.created_at)}</span>
                        </div>
                        <div className="detail-row">
                            <label>Người dùng:</label>
                            <span>{selectedLog.user?.name || 'System'}</span>
                        </div>
                        <div className="detail-row">
                            <label>Hành động:</label>
                            <span className={`log_action-badge ${getActionColor(selectedLog.action || selectedLog.event)}`}>
                                {selectedLog.action || selectedLog.event}
                            </span>
                        </div>
                        <div className="detail-row">
                            <label>Mô tả:</label>
                            <span>{selectedLog.description}</span>
                        </div>
                        
                        {/* Hiển thị Properties (Payload/Changes) */}
                        {selectedLog.properties && Object.keys(selectedLog.properties).length > 0 && (
                            <div className="detail-row full">
                                <label>Dữ liệu chi tiết:</label>
                                <pre>{JSON.stringify(selectedLog.properties, null, 2)}</pre>
                            </div>
                        )}
                    </div>
                )}
            </Modal>
        </div>
    );
};

export default LogList;