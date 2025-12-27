import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Plus, Search, Edit, Trash2, Eye,
    ChevronLeft, ChevronRight, RefreshCw, UserCircle
} from 'lucide-react';
import { useUser } from '@/hooks/admin/useUser';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import './UserList.css';

const UserList = () => {
    const navigate = useNavigate();
    const { users, pagination, loading, error, fetchUsers, deleteUser } = useUser();

    // State quản lý filter
    const [filters, setFilters] = useState({
        search: '',
        status: '' // '' = All, 'active', 'inactive'
    });

    const [confirmDialog, setConfirmDialog] = useState({ isOpen: false });

    // 1. Initial Load
    useEffect(() => {
        loadData(1);
    }, []);

    const loadData = (page = 1, currentFilters = filters) => {
        const params = {
            page,
            per_page: 15,
        };

        if (currentFilters.search) params.q = currentFilters.search;
        if (currentFilters.status && currentFilters.status !== '') {
            params.is_active = currentFilters.status === 'active';
        }

        fetchUsers(params);
    };

    // 2. Handlers
    const handleSearchKeyDown = (e) => {
        if (e.key === 'Enter') {
            loadData(1);
        }
    };

    const handleRefresh = () => {
        const resetFilters = { search: '', status: '' };
        setFilters(resetFilters);
        loadData(1, resetFilters);
    };

    const handlePageChange = (newPage) => {
        if (newPage >= 1 && newPage <= pagination.last_page) {
            loadData(newPage);
        }
    };

    const handleDeleteClick = (user) => {
        setConfirmDialog({
            isOpen: true,
            title: 'Delete User',
            message: `Are you sure you want to delete user "${user.name}"? This action cannot be undone.`,
            onConfirm: async () => {
                await deleteUser(user.uuid);
                setConfirmDialog({ isOpen: false });
                loadData(pagination.current_page);
            }
        });
    };

    return (
        <div className="user-list-container">
            {/* Page Header */}
            <div className="user-page-header">
                <div className="header-title">
                    <UserCircle size={24} className="text-gold" />
                    <div>
                        <h1>User Management</h1>
                        <p>Manage system access and roles</p>
                    </div>
                </div>
                <button
                    className="btn-primary-action"
                    onClick={() => navigate('/admin/users/create')}
                >
                    <Plus size={18} /> Add User
                </button>
            </div>

            {/* Main Card Wrapper */}
            <div className="user-list-wrapper">
                {/* Filters Bar */}
                <div className="user-filters">
                    <div className="search-input-wrapper">
                        <Search size={18} className="search-icon" />
                        <input
                            placeholder="Search by name or email..."
                            value={filters.search}
                            onChange={(e) => setFilters(p => ({ ...p, search: e.target.value }))}
                            onKeyDown={handleSearchKeyDown}
                        />
                    </div>

                    <div className="status-select-wrapper">
                        <select
                            className="status-select"
                            value={filters.status}
                            onChange={(e) => {
                                const newFilters = { ...filters, status: e.target.value };
                                setFilters(newFilters);
                                loadData(1, newFilters);
                            }}
                        >
                            <option value="">-- All Status --</option>
                            <option value="active">Active</option>
                            <option value="inactive">Blocked</option>
                        </select>
                    </div>

                    <button className="btn-refresh" onClick={handleRefresh} title="Reset & Refresh">
                        <RefreshCw size={18} className={loading ? 'animate-spin' : ''} />
                    </button>
                </div>

                {/* Table Section */}
                <div className="user-table-container">
                    <div className="table-scroll">
                        <table className="modern-table">
                            <thead>
                                <tr>
                                    <th style={{ width: '35%', textAlign: 'left' }}>User Info</th>
                                    <th style={{ width: '25%', textAlign: 'left' }}>Roles</th>
                                    <th style={{ width: '15%', textAlign: 'center' }}>Status</th>
                                    <th style={{ width: '15%', textAlign: 'left' }}>Created At</th>
                                    <th style={{ width: '10%', textAlign: 'right' }}>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {loading ? (
                                    <tr><td colSpan="5" className="empty-state"><span className="animate-spin inline-block mr-2">⟳</span> Loading users...</td></tr>
                                ) : error ? (
                                    <tr><td colSpan="5" className="empty-state text-red-500">{error}</td></tr>
                                ) : users.length > 0 ? (
                                    users.map(user => (
                                        <tr key={user.uuid}>
                                            <td style={{ textAlign: 'left' }}>
                                                <div className="user-cell">
                                                    <div className="user-name">{user.name}</div>
                                                    <div className="user-email">{user.email}</div>
                                                </div>
                                            </td>
                                            <td style={{ textAlign: 'left' }}>
                                                <div className="roles-wrapper">
                                                    {user.roles?.length > 0 ? (
                                                        user.roles.map(role => (
                                                            <span key={role.id} className="role-tag">
                                                                {role.name}
                                                            </span>
                                                        ))
                                                    ) : (
                                                        <span className="role-tag member">Member</span>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="text-center">
                                                <span className={`status-badge ${user.is_active ? 'success' : 'danger'}`}>
                                                    {user.is_active ? 'Active' : 'Blocked'}
                                                </span>
                                            </td>
                                            <td style={{ textAlign: 'left' }}>
                                                <span className="date-text">
                                                    {user.created_at ? new Date(user.created_at).toLocaleDateString('en-GB') : '-'}
                                                </span>
                                            </td>
                                            <td>
                                                <div className="table-actions">
                                                    <button
                                                        className="table-action-btn"
                                                        onClick={() => navigate(`/admin/users/${user.uuid}`)}
                                                        title="View"
                                                    >
                                                        <Eye size={16} />
                                                    </button>
                                                    <button
                                                        className="table-action-btn"
                                                        onClick={() => navigate(`/admin/users/${user.uuid}/edit`)}
                                                        title="Edit"
                                                    >
                                                        <Edit size={16} />
                                                    </button>
                                                    <button
                                                        className="table-action-btn danger"
                                                        onClick={() => handleDeleteClick(user)}
                                                        title="Delete"
                                                    >
                                                        <Trash2 size={16} />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr><td colSpan="5" className="empty-state">No users found.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {pagination.last_page > 1 && (
                        <div className="pagination-wrapper">
                            <div className="pagination-info">
                                Showing <b>{users.length}</b> of <b>{pagination.total}</b> users
                            </div>
                            <div className="pagination-actions">
                                <button disabled={pagination.current_page === 1} onClick={() => handlePageChange(pagination.current_page - 1)}>
                                    <ChevronLeft size={18} />
                                </button>
                                <span className="page-number">{pagination.current_page}</span>
                                <button disabled={pagination.current_page === pagination.last_page} onClick={() => handlePageChange(pagination.current_page + 1)}>
                                    <ChevronRight size={18} />
                                </button>
                            </div>
                            <div className="pagination-spacer"></div>
                        </div>
                    )}
                </div>
            </div>

            <ConfirmDialog
                isOpen={confirmDialog.isOpen}
                onClose={() => setConfirmDialog({ isOpen: false })}
                onConfirm={confirmDialog.onConfirm}
                title={confirmDialog.title}
                message={confirmDialog.message}
            />
        </div>
    );
};

export default UserList;