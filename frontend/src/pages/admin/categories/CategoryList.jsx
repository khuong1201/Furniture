import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Edit, Trash2, FolderTree } from 'lucide-react';
import CategoryService from '@/services/CategoryService';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import '../products/ProductList.css';

const CategoryList = () => {
    const navigate = useNavigate();
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [confirmDialog, setConfirmDialog] = useState({
        isOpen: false,
        title: '',
        message: '',
        onConfirm: null,
        isLoading: false
    });

    const fetchCategories = async () => {
        try {
            setLoading(true);
            setError(null);

            const response = await CategoryService.getCategories();

            if (response.success && response.data) {
                // Check if response.data is paginated (has .data property) or is direct array
                const categoriesData = Array.isArray(response.data) ? response.data : (response.data.data || []);
                setCategories(categoriesData);
            }
        } catch (err) {
            setError(err.message || 'Không thể tải danh sách danh mục');
            console.error('Error fetching categories:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchCategories();
    }, []);

    const handleDelete = (uuid, name) => {
        setConfirmDialog({
            isOpen: true,
            title: 'Xóa danh mục',
            message: `Bạn có chắc muốn xóa danh mục "${name}"? Hành động này không thể hoàn tác.`,
            onConfirm: async () => {
                setConfirmDialog(prev => ({ ...prev, isLoading: true }));
                try {
                    await CategoryService.deleteCategory(uuid);
                    setConfirmDialog(prev => ({ ...prev, isOpen: false }));
                    fetchCategories();
                } catch (err) {
                    setConfirmDialog(prev => ({ ...prev, isOpen: false }));
                    alert('Lỗi khi xóa danh mục: ' + err.message);
                }
            }
        });
    };

    return (
        <div className="product-list">
            <div className="page-header">
                <div>
                    <h1>Quản lý Danh mục</h1>
                    <p className="page-subtitle">Danh sách tất cả danh mục sản phẩm</p>
                </div>
                <button
                    className="btn btn-primary"
                    onClick={() => navigate('/admin/categories/create')}
                >
                    <Plus size={20} />
                    Thêm danh mục
                </button>
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
                        <button onClick={fetchCategories} className="btn btn-secondary">
                            Thử lại
                        </button>
                    </div>
                ) : categories.length === 0 ? (
                    <div className="empty-state">
                        <FolderTree size={48} color="#9ca3af" />
                        <p>Chưa có danh mục nào</p>
                        <button
                            onClick={() => navigate('/admin/categories/create')}
                            className="btn btn-primary"
                        >
                            Thêm danh mục đầu tiên
                        </button>
                    </div>
                ) : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Tên danh mục</th>
                                <th>Slug</th>
                                <th>Danh mục cha</th>
                                <th>Số sản phẩm</th>
                                <th className="text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            {categories.map((category) => (
                                <tr key={category.uuid}>
                                    <td>
                                        <strong>{category.name}</strong>
                                    </td>
                                    <td>{category.slug}</td>
                                    <td>{category.parent?.name || '-'}</td>
                                    <td>{category.products_count || 0}</td>
                                    <td className="text-right">
                                        <div className="action-buttons">
                                            <button
                                                className="btn-icon"
                                                onClick={() => navigate(`/admin/categories/${category.uuid}/edit`)}
                                                title="Chỉnh sửa"
                                            >
                                                <Edit size={16} />
                                            </button>
                                            <button
                                                className="btn-icon btn-danger"
                                                onClick={() => handleDelete(category.uuid, category.name)}
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
                isOpen={confirmDialog.isOpen}
                onClose={() => setConfirmDialog(prev => ({ ...prev, isOpen: false }))}
                onConfirm={confirmDialog.onConfirm}
                title={confirmDialog.title}
                message={confirmDialog.message}
                isLoading={confirmDialog.isLoading}
            />
        </div >
    );
};

export default CategoryList;
