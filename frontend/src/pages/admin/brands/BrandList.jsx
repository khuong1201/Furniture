import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Search, Edit, Trash2, RefreshCw, ChevronLeft, ChevronRight, Image as ImageIcon } from 'lucide-react';
import { useBrand } from '@/hooks/admin/useBrand';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import LoadingSpinner from '@/components/admin/shared/LoadingSpinner';
import './BrandList.css';

const BrandList = () => {
    const navigate = useNavigate();
    const { brands, pagination, loading, fetchBrands, deleteBrand } = useBrand();

    const [searchTerm, setSearchTerm] = useState('');
    const [confirmDialog, setConfirmDialog] = useState({ isOpen: false, item: null });
    const [isRefreshing, setIsRefreshing] = useState(false);

    useEffect(() => {
        fetchBrands({ page: 1 });
    }, [fetchBrands]);

    const handlePageChange = (newPage) => {
        if (newPage >= 1 && newPage <= pagination.last_page) {
            fetchBrands({ page: newPage, search: searchTerm });
        }
    };

    const handleSearch = (e) => {
        e.preventDefault();
        fetchBrands({ page: 1, search: searchTerm });
    };

    const handleRefresh = async () => {
        setIsRefreshing(true);
        setSearchTerm('');
        await fetchBrands({ page: 1, search: '' });
        setTimeout(() => setIsRefreshing(false), 500);
    };

    const handleDelete = async () => {
        if (!confirmDialog.item) return;
        await deleteBrand(confirmDialog.item.uuid);
        fetchBrands({ page: pagination.current_page, search: searchTerm });
        setConfirmDialog({ isOpen: false, item: null });
    };

    if (loading && brands.length === 0 && !isRefreshing) return <LoadingSpinner />;

    return (
        <div className="brand_list-page">
            {/* Filter */}
            <div className="filter-bar">
                <form onSubmit={handleSearch} className="search-group">
                    <Search size={18} className="search-icon" />
                    <input
                        className="filter-input"
                        placeholder="Search brand..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </form>
                <button className="btn-refresh" onClick={handleRefresh}>
                    <RefreshCw size={18} className={isRefreshing ? 'animate-spin' : ''} />
                </button>
            </div>

            {/* Table */}
            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th style={{ width: 80 }}>Logo</th>
                            <th>Brand Name</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th style={{ textAlign: 'right' }}>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {brands.length > 0 ? brands.map(brand => (
                            <tr key={brand.uuid}>
                                <td>
                                    <div className="brand-logo-cell">
                                        {brand.logo_url
                                            ? <img src={brand.logo_url} alt={brand.name} />
                                            : <ImageIcon size={18} />}
                                    </div>
                                </td>
                                <td className="font-medium">{brand.name}</td>
                                <td className="text-mono">{brand.slug}</td>
                                <td>
                                    <span className={`status-badge ${brand.is_active ? 'active' : 'hidden'}`}>
                                        {brand.is_active ? 'Active' : 'Hidden'}
                                    </span>
                                </td>
                                <td>
                                    <div className="table-actions">
                                        <button
                                            className="table-action-btn"
                                            onClick={() => navigate(`/admin/brands/${brand.uuid}/edit`)}
                                        >
                                            <Edit size={16} />
                                        </button>
                                        <button
                                            className="table-action-btn delete"
                                            onClick={() => setConfirmDialog({ isOpen: true, item: brand })}
                                        >
                                            <Trash2 size={16} />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        )) : (
                            <tr>
                                <td colSpan="5" className="empty-state">No brands found.</td>
                            </tr>
                        )}
                    </tbody>
                </table>

                {pagination.total > 0 && (
                    <div className="pagination-bar">
                        <div className="pagination-info">
                            Showing <b>{brands.length}</b> of <b>{pagination.total}</b>
                        </div>
                        <div className="pagination-controls">
                            <button
                                className="page-btn"
                                disabled={pagination.current_page === 1}
                                onClick={() => handlePageChange(pagination.current_page - 1)}
                            >
                                <ChevronLeft size={18} />
                            </button>
                            <span className="page-current">
                                Page {pagination.current_page} / {pagination.last_page}
                            </span>
                            <button
                                className="page-btn"
                                disabled={pagination.current_page === pagination.last_page}
                                onClick={() => handlePageChange(pagination.current_page + 1)}
                            >
                                <ChevronRight size={18} />
                            </button>
                        </div>
                        <div className="pagination-spacer" />
                    </div>
                )}
            </div>

            <ConfirmDialog
                isOpen={confirmDialog.isOpen}
                onClose={() => setConfirmDialog({ isOpen: false, item: null })}
                onConfirm={handleDelete}
                title="Delete Brand"
                message={`Delete "${confirmDialog.item?.name}"?`}
                type="danger"
            />
        </div>
    );
};

export default BrandList;