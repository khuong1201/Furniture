import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Plus,
  Edit,
  Trash2,
  FolderTree,
  ChevronRight,
  ChevronDown,
  Eye,
  Layers,
  Package,
  Settings,
  Search
} from 'lucide-react';
import CategoryService from '@/services/admin/CategoryService';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import './CategoryManagement.css';

const CategoryList = () => {
  const navigate = useNavigate();
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [expandedCategories, setExpandedCategories] = useState(new Set());
  const [searchTerm, setSearchTerm] = useState('');
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
      const response = await CategoryService.getCategoryTree();
      if (response.success && response.data) {
        setCategories(response.data);
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

  const toggleCategory = (uuid) => {
    const newExpanded = new Set(expandedCategories);
    if (newExpanded.has(uuid)) {
      newExpanded.delete(uuid);
    } else {
      newExpanded.add(uuid);
    }
    setExpandedCategories(newExpanded);
  };

  const handleDelete = (uuid, name) => {
    setConfirmDialog({
      isOpen: true,
      title: 'Xóa danh mục',
      message: `Bạn có chắc muốn xóa danh mục "${name}"? Hành động này sẽ xóa tất cả danh mục con và không thể hoàn tác.`,
      confirmText: 'Xóa',
      cancelText: 'Hủy',
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

  const filteredCategories = categories.filter(category =>
    category.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    category.slug.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const getCategoryIcon = (categoryName) => {
    const name = categoryName.toLowerCase();
    if (name.includes('giường') || name.includes('bed')) return '🛏️';
    if (name.includes('ghế') || name.includes('chair')) return '🪑';
    if (name.includes('đèn') || name.includes('light')) return '💡';
    if (name.includes('sofa')) return '🛋️';
    if (name.includes('bàn') || name.includes('table')) return '🪟';
    if (name.includes('kệ') || name.includes('shelf')) return '📚';
    if (name.includes('tủ') || name.includes('wardrobe')) return '🚪';
    if (name.includes('ngoài trời') || name.includes('outdoor')) return '🌳';
    return '📁'; // Icon mặc định
  };

  const renderCategoryCard = (category, depth = 0) => {
    const hasChildren = category.all_children && category.all_children.length > 0;
    const isExpanded = expandedCategories.has(category.uuid);
    const icon = getCategoryIcon(category.name);

    return (
      <div className="category_tree" key={category.uuid}>
        <div
          className={`category_item ${depth > 0 ? 'child' : ''}`}
          style={{ paddingLeft: `${depth * 32 + 16}px` }}
        >
          <div className="category_header">
            <button
              className="category_expand-btn"
              onClick={() => toggleCategory(category.uuid)}
              disabled={!hasChildren}
            >
              {hasChildren ? (
                isExpanded ? <ChevronDown size={18} /> : <ChevronRight size={18} />
              ) : (
                <div className="dot" />
              )}
            </button>

            <div className="category_icon">
              <span className="category_icon-text">{icon}</span>
            </div>

            <div className="category_info">
              <div className="category_main">
                <h4 className="category_name">{category.name}</h4>
                {category.description && (
                  <p className="category_description">{category.description}</p>
                )}
              </div>

              <div className="category_meta">
                <span className="category_slug">/{category.slug}</span>
                <div className="category_stats">
                  <span className="stat-item">
                    <Package size={14} />
                    {category.products_count || 0} sản phẩm
                  </span>
                  {hasChildren && (
                    <span className="stat-item">
                      <Layers size={14} />
                      {category.all_children.length} danh mục con
                    </span>
                  )}
                </div>
              </div>
            </div>
          </div>

          <div className="category_details">
            <div className="category_detail-item">
              <span className="detail-label">ID:</span>
              <span className="detail-value">{category.id}</span>
            </div>
            <div className="category_detail-item">
              <span className="detail-label">Ngày tạo:</span>
              <span className="detail-value">
                {new Date(category.created_at).toLocaleDateString('vi-VN')}
              </span>
            </div>
          </div>

          <div className="category_actions">
            <button
              className="category_action-btn category_view-btn"
              onClick={() => navigate(`/admin/products?category=${category.uuid}`)}
              title="Xem sản phẩm"
            >
              <Eye size={16} />
              <span className="category_action-label">Xem</span>
            </button>
            <button
              className="category_action-btn category_edit-btn"
              onClick={() => navigate(`/admin/categories/${category.uuid}/edit`)}
              title="Chỉnh sửa"
            >
              <Edit size={16} />
              <span className="category_action-label">Sửa</span>
            </button>
            <button
              className="category_action-btn category_delete-btn"
              onClick={() => handleDelete(category.uuid, category.name)}
              title="Xóa"
            >
              <Trash2 size={16} />
              <span className="category_action-label">Xóa</span>
            </button>
          </div>
        </div>

        {hasChildren && isExpanded && (
          <div className="category_children-container">
            {category.all_children.map(child => renderCategoryCard(child, depth + 1))}
          </div>
        )}
      </div>
    );
  };

  // Tính toán số liệu thống kê
  const totalCategories = categories.length;
  const totalProducts = categories.reduce((sum, cat) => sum + (cat.products_count || 0), 0);
  const activeCategories = categories.filter(cat => cat.is_active !== false).length;

  // Tìm độ sâu tối đa của danh mục
  const getMaxDepth = (cats, depth = 0) => {
    let maxDepth = depth;
    cats.forEach(cat => {
      if (cat.all_children && cat.all_children.length > 0) {
        const childDepth = getMaxDepth(cat.all_children, depth + 1);
        if (childDepth > maxDepth) maxDepth = childDepth;
      }
    });
    return maxDepth;
  };
  const maxDepth = getMaxDepth(categories);

  return (
    <div className="category_management">
      {/* Header Section */}
      <div className="category_header-section">
        <div className="category_header-left">
          <div className="category_page-header">
            <h1>
              <FolderTree size={28} />
              Quản lý Danh mục
            </h1>
            <p className="category_page-subtitle">Tổ chức và quản lý danh mục sản phẩm của bạn</p>
          </div>
        </div>

        <div className="category_header-right">
          <div className="category_search-wrapper">
            <Search size={18} className="category_search-icon" />
            <input
              type="text"
              placeholder="Tìm kiếm danh mục..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="category_search-input"
            />
            {searchTerm && (
              <button
                className="category_clear-search"
                onClick={() => setSearchTerm('')}
                title="Xóa tìm kiếm"
              >
                ✕
              </button>
            )}
          </div>

          <button
            className="category_category_btn category_btn-primary"
            onClick={() => navigate('/admin/categories/create')}
          >
            <Plus size={20} />
            Thêm danh mục
          </button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="category_stats-grid">
        <div className="category_stat-card">
          <div className="category_stat-icon total">
            <FolderTree size={24} />
          </div>
          <div className="category_stat-content">
            <h3>{totalCategories}</h3>
            <p>Tổng danh mục</p>
          </div>
        </div>

        <div className="category_stat-card">
          <div className="category_stat-icon products">
            <Package size={24} />
          </div>
          <div className="category_stat-content">
            <h3>{totalProducts}</h3>
            <p>Tổng sản phẩm</p>
          </div>
        </div>

        <div className="category_stat-card">
          <div className="category_stat-icon active">
            <Layers size={24} />
          </div>
          <div className="category_stat-content">
            <h3>{activeCategories}</h3>
            <p>Đang hoạt động</p>
          </div>
        </div>

        <div className="category_stat-card">
          <div className="category_stat-icon levels">
            <Settings size={24} />
          </div>
          <div className="category_stat-content">
            <h3>{maxDepth + 1}</h3>
            <p>Cấp danh mục</p>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="category_content">
        {loading ? (
          <div className="category_loading-state">
            <div className="category_spinner-gold"></div>
            <p>Đang tải danh mục...</p>
          </div>
        ) : error ? (
          <div className="category_error-state">
            <div className="error-icon">⚠️</div>
            <p>{error}</p>
            <button onClick={fetchCategories} className="category_category_btn category_btn-secondary">
              Thử lại
            </button>
          </div>
        ) : filteredCategories.length === 0 ? (
          <div className="category_empty-state">
            <FolderTree size={64} color="#fbbf24" />
            <h3>Không tìm thấy danh mục</h3>
            <p>{searchTerm ? 'Thử tìm kiếm với từ khóa khác' : 'Bắt đầu bằng cách thêm danh mục đầu tiên'}</p>
            <button
              onClick={() => navigate('/admin/categories/create')}
              className="category_category_btn category_btn-primary"
            >
              <Plus size={18} />
              Thêm danh mục mới
            </button>
          </div>
        ) : (
          <div className="categories_list">
            <div className="category_list-header">
              <div className="category_header-col name">Danh mục</div>
              <div className="category_header-col details">Chi tiết</div>
              <div className="category_header-col actions">Thao tác</div>
            </div>

            <div className="categories_container">
              {filteredCategories.map(category => renderCategoryCard(category))}
            </div>

            <div className="category_list-footer">
              <p>
                Hiển thị {filteredCategories.length} trong tổng số {categories.length} danh mục
                {searchTerm && ` • Kết quả tìm kiếm cho "${searchTerm}"`}
              </p>
            </div>
          </div>
        )}
      </div>

      <ConfirmDialog
        isOpen={confirmDialog.isOpen}
        onClose={() => setConfirmDialog(prev => ({ ...prev, isOpen: false }))}
        onConfirm={confirmDialog.onConfirm}
        title={confirmDialog.title}
        message={confirmDialog.message}
        confirmText={confirmDialog.confirmText}
        cancelText={confirmDialog.cancelText}
        isLoading={confirmDialog.isLoading}
      />
    </div>
  );
};

export default CategoryList;