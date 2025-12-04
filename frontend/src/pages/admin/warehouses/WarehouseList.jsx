import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Edit, Trash2, Package, Search } from 'lucide-react';
import WarehouseService from '@/services/WarehouseService';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import '../products/ProductList.css';

const WarehouseList = () => {
    const navigate = useNavigate();
    const [warehouses, setWarehouses] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [deleteDialog, setDeleteDialog] = useState({ open: false, warehouse: null });

    const fetchWarehouses = async () => {
        try {
            setLoading(true);
            setError(null);

            const params = {};
            if (searchTerm) params.search = searchTerm;

            const response = await WarehouseService.getWarehouses(params);

            if (response.success && response.data) {
                const warehouseList = Array.isArray(response.data)
                    ? response.data
                    : response.data.data || [];
                setWarehouses(warehouseList);
            }
        } catch (err) {
            setError(err.message || 'Không thể tải danh sách kho hàng');
            console.error('Error fetching warehouses:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchWarehouses();
    }, [searchTerm]);

    const handleDelete = async () => {
        if (!deleteDialog.warehouse) return;

        try {
            await WarehouseService.deleteWarehouse(deleteDialog.warehouse.uuid);
            alert('Xóa kho hàng thành công!');
            fetchWarehouses();
        } catch (error) {
            console.error('Error deleting warehouse:', error);
            alert(error.message || 'Có lỗi xảy ra khi xóa kho hàng');
        } finally {
            setDeleteDialog({ open: false, warehouse: null });
        }
    };

    return (
        <div className="product-list">
            <div className="page-header">
                <div>
                    <h1>Quản lý Kho hàng</h1>
                    <p className="page-subtitle">Danh sách tất cả kho hàng trong hệ thống</p>
                </div>
                <button
                    className="btn btn-primary"
                    onClick={() => navigate('/admin/warehouses/create')}
                >
                    <Plus size={20} />
                    Thêm kho hàng
                </button>
            </div>

            <div className="filters-section">
                <div className="search-box">
                    <Search size={20} />
                    <input
                        type="text"
                        placeholder="Tìm theo tên kho hoặc địa điểm..."
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
                        <button onClick={fetchWarehouses} className="btn btn-secondary">
                            Thử lại
                        </button>
                    </div>
                ) : warehouses.length === 0 ? (
                    <div className="empty-state">
                        <Package size={48} color="#9ca3af" />
                        <p>Chưa có kho hàng nào</p>
                        <button
                            className="btn btn-primary"
                            onClick={() => navigate('/admin/warehouses/create')}
                        >
                            <Plus size={20} />
                            Thêm kho hàng đầu tiên
                        </button>
                    </div>
                ) : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Tên kho</th>
                                <th>Địa điểm</th>
                                <th>Ngày tạo</th>
                                <th className="text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            {warehouses.map((warehouse) => (
                                <tr key={warehouse.uuid}>
                                    <td>
                                        <strong>{warehouse.name}</strong>
                                    </td>
                                    <td>{warehouse.location || '-'}</td>
                                    <td>
                                        {warehouse.created_at
                                            ? new Date(warehouse.created_at).toLocaleDateString('vi-VN')
                                            : '-'
                                        }
                                    </td>
                                    <td className="text-right">
                                        <div className="action-buttons">
                                            <button
                                                className="btn-icon"
                                                onClick={() => navigate(`/admin/warehouses/${warehouse.uuid}/edit`)}
                                                title="Chỉnh sửa"
                                            >
                                                <Edit size={16} />
                                            </button>
                                            <button
                                                className="btn-icon btn-danger"
                                                onClick={() => setDeleteDialog({ open: true, warehouse })}
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
                )}
            </div>

            <ConfirmDialog
                isOpen={deleteDialog.open}
                title="Xác nhận xóa"
                message={`Bạn có chắc chắn muốn xóa kho "${deleteDialog.warehouse?.name}"? Hành động này không thể hoàn tác.`}
                onConfirm={handleDelete}
                onCancel={() => setDeleteDialog({ open: false, warehouse: null })}
            />
        </div>
    );
};

export default WarehouseList;
