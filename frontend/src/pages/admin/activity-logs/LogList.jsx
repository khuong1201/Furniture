import React, { useState, useEffect } from 'react';
import {
    Search,
    Activity,
    User,
    Clock,
    Eye,
    ChevronLeft,
    ChevronRight,
    Loader,
    AlertCircle
} from 'lucide-react';
import LogService from '@/services/admin/LogService';
import Modal from '@/components/admin/shared/Modal';
import './LogList.css';

const LogList = () => {
    const [logs, setLogs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [searchTerm, setSearchTerm] = useState('');
    const [filterType, setFilterType] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);

    const [selectedLog, setSelectedLog] = useState(null);
    const [isDetailOpen, setIsDetailOpen] = useState(false);

    useEffect(() => {
        fetchLogs();
    }, [currentPage, filterType]);

    const fetchLogs = async () => {
        try {
            setLoading(true);
            const params = { page: currentPage };
            if (filterType) params.type = filterType;

            const response = await LogService.getAll(params);
            setLogs(response.data || []);
            setTotalPages(response.meta?.last_page || 1);
        } catch (err) {
            setError('Không thể tải nhật ký hoạt động');
        } finally {
            setLoading(false);
        }
    };

    const handleViewDetail = async (log) => {
        try {
            const response = await LogService.getById(log.uuid);
            setSelectedLog(response.data || log);
        } catch (err) {
            setSelectedLog(log);
        }
        setIsDetailOpen(true);
    };

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

    const filteredLogs = logs.filter(log =>
        log.description?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        log.user?.name?.toLowerCase().includes(searchTerm.toLowerCase())
    );

    if (loading && logs.length === 0) {
        return (
            <div className="log_loading-state">
                <Loader className="spinner" size={40} />
                <p>Đang tải...</p>
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
                    <button onClick={() => setError('')}>×</button>
                </div>
            )}

            <div className="search-filters">
                <div className="log_search-box">
                    <Search size={20} className="search-icon" />
                    <input
                        type="text"
                        placeholder="Tìm kiếm hoạt động..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
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

            <div className="log-table-wrapper">
                {filteredLogs.length === 0 ? (
                    <div className="log_empty-state">
                        <Activity size={64} />
                        <h3>Chưa có hoạt động nào</h3>
                        <p>Nhật ký sẽ được ghi lại khi có hoạt động</p>
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
                            {filteredLogs.map((log) => (
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

            {totalPages > 1 && (
                <div className="log_pagination">
                    <button disabled={currentPage === 1} onClick={() => setCurrentPage(p => p - 1)}>
                        <ChevronLeft size={18} />
                    </button>
                    {[...Array(Math.min(5, totalPages))].map((_, i) => {
                        const pageNum = currentPage > 3 ? currentPage - 2 + i : i + 1;
                        if (pageNum > totalPages) return null;
                        return (
                            <button key={pageNum} className={currentPage === pageNum ? 'active' : ''}
                                onClick={() => setCurrentPage(pageNum)}>{pageNum}</button>
                        );
                    })}
                    <button disabled={currentPage === totalPages} onClick={() => setCurrentPage(p => p + 1)}>
                        <ChevronRight size={18} />
                    </button>
                </div>
            )}

            <Modal isOpen={isDetailOpen} onClose={() => setIsDetailOpen(false)} title="Chi tiết hoạt động" size="md">
                {selectedLog && (
                    <div className="log-detail">
                        <div className="detail-row"><label>Thời gian:</label><span>{formatDate(selectedLog.created_at)}</span></div>
                        <div className="detail-row"><label>Người dùng:</label><span>{selectedLog.user?.name || 'System'}</span></div>
                        <div className="detail-row"><label>Hành động:</label>
                            <span className={`log_action-badge ${getActionColor(selectedLog.action)}`}>{selectedLog.action}</span>
                        </div>
                        <div className="detail-row"><label>Mô tả:</label><span>{selectedLog.description}</span></div>
                        {selectedLog.properties && (
                            <div className="detail-row full"><label>Dữ liệu:</label>
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
