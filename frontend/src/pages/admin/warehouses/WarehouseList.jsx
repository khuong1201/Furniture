import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Edit, Trash2, Search, ChevronLeft, ChevronRight, Plus } from 'lucide-react';
import WarehouseService from '@/services/admin/WarehouseService';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import './WarehouseList.css';

const WarehouseList = ({ isManagerMode = false }) => {
    const navigate = useNavigate();
    const [warehouses, setWarehouses] = useState([]);
    const [pagination, setPagination] = useState({});
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [deleteDialog, setDeleteDialog] = useState({
        isOpen: false, item: null, isDeleting: false
    });

    const fetchWarehouses = async (page = 1) => {
        try {
            setLoading(true);
            const response = await WarehouseService.getWarehouses({
                page, per_page: 15, search: searchTerm
            });
            const data = response.data || [];
            setWarehouses(Array.isArray(data) ? data : data.data || []);
            setPagination(response.meta || {});
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        const t = setTimeout(() => fetchWarehouses(1), 500);
        return () => clearTimeout(t);
    }, [searchTerm]);

    const handleDelete = async () => {
        if (!deleteDialog.item) return;
        setDeleteDialog(prev => ({ ...prev, isDeleting: true }));
        try {
            await WarehouseService.deleteWarehouse(deleteDialog.item.uuid);
            setDeleteDialog({ isOpen: false, item: null, isDeleting: false });
            fetchWarehouses(pagination.current_page || 1);
        } catch (err) {
            alert(err.message || 'Cannot delete warehouse');
            setDeleteDialog(prev => ({ ...prev, isDeleting: false }));
        }
    };

    return (
        <div className="warehouse_list">
            {!isManagerMode && (
                <div className="page-header">
                    <div className="header-left">
                        <h1>Warehouse Management</h1>
                        <p>Manage storage locations and stock distribution</p>
                    </div>
                    <button
                        onClick={() => navigate('/admin/warehouses/create')}
                        className="btn-primary-action"
                    >
                        <Plus size={18} /> Add New Warehouse
                    </button>
                </div>
            )}

            <div className="filters-section">
                <div className="search-box">
                    <Search className="search-icon" size={18} />
                    <input
                        type="text"
                        placeholder="Search by name or location..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
            </div>

            <div className="table-container">
                <div className="overflow-auto">
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Warehouse Name</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th style={{ textAlign: 'right' }}>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr>
                                    <td colSpan="4" className="text-center p-8">
                                        Loading data...
                                    </td>
                                </tr>
                            ) : warehouses.length > 0 ? (
                                warehouses.map(wh => (
                                    <tr key={wh.uuid}>
                                        <td className="cell-name">{wh.name}</td>
                                        <td>{wh.location}</td>
                                        <td>
                                            <span className={`status-badge ${wh.is_active ? 'status-active' : 'status-inactive'}`}>
                                                {wh.is_active ? 'Active' : 'Hidden'}
                                            </span>
                                        </td>
                                        {/* FIXED ACTIONS */}
                                        <td>
                                            <div className="table-actions">
                                                <button
                                                    className="table-action-btn"
                                                    onClick={() => navigate(`/admin/warehouses/${wh.uuid}/edit`)}
                                                >
                                                    <Edit size={16} />
                                                </button>
                                                <button
                                                    className="table-action-btn delete"
                                                    onClick={() => setDeleteDialog({ isOpen: true, item: wh })}
                                                >
                                                    <Trash2 size={16} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="4" className="text-center p-8 italic">
                                        No warehouses found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {pagination.last_page > 1 && (
                    <div className="pagination-fixed">
                        <div className="page-info">
                            Page <strong>{pagination.current_page}</strong> / {pagination.last_page}
                        </div>
                        <div className="page-controls">
                            <button
                                className="page-btn"
                                disabled={pagination.current_page === 1}
                                onClick={() => fetchWarehouses(pagination.current_page - 1)}
                            >
                                <ChevronLeft size={18} />
                            </button>
                            <span className="page-current">{pagination.current_page}</span>
                            <button
                                className="page-btn"
                                disabled={pagination.current_page === pagination.last_page}
                                onClick={() => fetchWarehouses(pagination.current_page + 1)}
                            >
                                <ChevronRight size={18} />
                            </button>
                        </div>
                        <div className="pagination-spacer"></div>
                    </div>
                )}
            </div>

            <ConfirmDialog
                isOpen={deleteDialog.isOpen}
                onClose={() => setDeleteDialog({ isOpen: false, item: null })}
                onConfirm={handleDelete}
                title="Delete Warehouse"
                message={`Are you sure you want to delete "${deleteDialog.item?.name}"?`}
                type="danger"
                isLoading={deleteDialog.isDeleting}
            />
        </div>
    );
};

export default WarehouseList;