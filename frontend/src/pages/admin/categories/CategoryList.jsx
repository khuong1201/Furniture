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
import CategoryService from '@/services/CategoryService';
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
      <div className="category-tree" key={category.uuid}>
        <div 
          className={`category-item ${depth > 0 ? 'child' : ''}`}
          style={{ paddingLeft: `${depth * 32 + 16}px` }}
        >
          <div className="category-header">
            <button 
              className="expand-btn"
              onClick={() => toggleCategory(category.uuid)}
              disabled={!hasChildren}
            >
              {hasChildren ? (
                isExpanded ? <ChevronDown size={18} /> : <ChevronRight size={18} />
              ) : (
                <div className="dot" />
              )}
            </button>

            <div className="category-icon">
              <span className="category-icon-text">{icon}</span>
            </div>

            <div className="category-info">
              <div className="category-main">
                <h4 className="category-name">{category.name}</h4>
                {category.description && (
                  <p className="category-description">{category.description}</p>
                )}
              </div>
              
              <div className="category-meta">
                <span className="category-slug">/{category.slug}</span>
                <div className="category-stats">
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

          <div className="category-details">
            <div className="category-detail-item">
              <span className="detail-label">ID:</span>
              <span className="detail-value">{category.id}</span>
            </div>
            <div className="category-detail-item">
              <span className="detail-label">Ngày tạo:</span>
              <span className="detail-value">
                {new Date(category.created_at).toLocaleDateString('vi-VN')}
              </span>
            </div>
          </div>

          <div className="category-actions">
            <button
              className="action-btn view-btn"
              onClick={() => navigate(`/admin/products?category=${category.uuid}`)}
              title="Xem sản phẩm"
            >
              <Eye size={16} />
              <span className="action-label">Xem</span>
            </button>
            <button
              className="action-btn edit-btn"
              onClick={() => navigate(`/admin/categories/${category.uuid}/edit`)}
              title="Chỉnh sửa"
            >
              <Edit size={16} />
              <span className="action-label">Sửa</span>
            </button>
            <button
              className="action-btn delete-btn"
              onClick={() => handleDelete(category.uuid, category.name)}
              title="Xóa"
            >
              <Trash2 size={16} />
              <span className="action-label">Xóa</span>
            </button>
          </div>
        </div>

        {hasChildren && isExpanded && (
          <div className="children-container">
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
    <div className="category-management">
      {/* Header Section */}
      <div className="category-header-section">
        <div className="header-left">
          <div className="page-header">
            <h1>
              <FolderTree size={28} />
              Quản lý Danh mục
            </h1>
            <p className="page-subtitle">Tổ chức và quản lý danh mục sản phẩm của bạn</p>
          </div>
        </div>

        <div className="header-right">
          <div className="search-wrapper">
            <Search size={18} className="search-icon" />
            <input
              type="text"
              placeholder="Tìm kiếm danh mục..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="search-input"
            />
            {searchTerm && (
              <button 
                className="clear-search" 
                onClick={() => setSearchTerm('')}
                title="Xóa tìm kiếm"
              >
                ✕
              </button>
            )}
          </div>

          <button
            className="btn btn-primary"
            onClick={() => navigate('/admin/categories/create')}
          >
            <Plus size={20} />
            Thêm danh mục
          </button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="stats-grid">
        <div className="stat-card">
          <div className="stat-icon total">
            <FolderTree size={24} />
          </div>
          <div className="stat-content">
            <h3>{totalCategories}</h3>
            <p>Tổng danh mục</p>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon products">
            <Package size={24} />
          </div>
          <div className="stat-content">
            <h3>{totalProducts}</h3>
            <p>Tổng sản phẩm</p>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon active">
            <Layers size={24} />
          </div>
          <div className="stat-content">
            <h3>{activeCategories}</h3>
            <p>Đang hoạt động</p>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon levels">
            <Settings size={24} />
          </div>
          <div className="stat-content">
            <h3>{maxDepth + 1}</h3>
            <p>Cấp danh mục</p>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="category-content">
        {loading ? (
          <div className="loading-state">
            <div className="spinner-gold"></div>
            <p>Đang tải danh mục...</p>
          </div>
        ) : error ? (
          <div className="error-state">
            <div className="error-icon">⚠️</div>
            <p>{error}</p>
            <button onClick={fetchCategories} className="btn btn-secondary">
              Thử lại
            </button>
          </div>
        ) : filteredCategories.length === 0 ? (
          <div className="empty-state">
            <FolderTree size={64} color="#fbbf24" />
            <h3>Không tìm thấy danh mục</h3>
            <p>{searchTerm ? 'Thử tìm kiếm với từ khóa khác' : 'Bắt đầu bằng cách thêm danh mục đầu tiên'}</p>
            <button
              onClick={() => navigate('/admin/categories/create')}
              className="btn btn-primary"
            >
              <Plus size={18} />
              Thêm danh mục mới
            </button>
          </div>
        ) : (
          <div className="categories-list">
            <div className="list-header">
              <div className="header-col name">Danh mục</div>
              <div className="header-col details">Chi tiết</div>
              <div className="header-col actions">Thao tác</div>
            </div>

            <div className="categories-container">
              {filteredCategories.map(category => renderCategoryCard(category))}
            </div>

            <div className="list-footer">
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