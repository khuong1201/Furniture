import React, { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useProduct } from '@/hooks/useProduct';
import ProductCard from '@/pages/customer/components/ProductCard';
import {
  Search, Filter, Grid, List, SlidersHorizontal,
  ChevronDown, X, Package
} from 'lucide-react';
import './SearchPage.css';

const SearchPage = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const keyword = searchParams.get('search') || '';
  const categorySlug = searchParams.get('category') || '';

  const { products, loading, error, searchProducts, getProducts } = useProduct();

  const [showFilters, setShowFilters] = useState(false);
  const [sortBy, setSortBy] = useState('newest');
  const [priceRange, setPriceRange] = useState({ min: '', max: '' });
  const [viewMode, setViewMode] = useState('grid');

  useEffect(() => {
    if (keyword) {
      searchProducts(keyword);
    } else if (categorySlug) {
      getProducts({ category: categorySlug });
    } else {
      getProducts();
    }
  }, [keyword, categorySlug]);

  const sortedProducts = [...(products || [])].sort((a, b) => {
    switch (sortBy) {
      case 'price-asc': return (a.price || 0) - (b.price || 0);
      case 'price-desc': return (b.price || 0) - (a.price || 0);
      case 'popular': return (b.sold_count || 0) - (a.sold_count || 0);
      case 'rating': return (b.rating || 0) - (a.rating || 0);
      default: return 0;
    }
  });

  const filteredProducts = sortedProducts.filter(item => {
    if (priceRange.min && item.price < parseInt(priceRange.min)) return false;
    if (priceRange.max && item.price > parseInt(priceRange.max)) return false;
    return true;
  });

  const clearFilters = () => {
    setPriceRange({ min: '', max: '' });
    setSortBy('newest');
  };

  const getPageTitle = () => {
    if (keyword) return `Kết quả tìm kiếm: "${keyword}"`;
    if (categorySlug) {
      const categoryNames = {
        'nhan': 'Nhẫn',
        'day-chuyen': 'Dây chuyền',
        'bong-tai': 'Bông tai',
        'vong-tay': 'Vòng tay'
      };
      return categoryNames[categorySlug] || 'Sản phẩm';
    }
    return 'Tất cả sản phẩm';
  };

  return (
    <div className="search-page">
      <div className="search-container">
        {/* Header */}
        <div className="search-header">
          <div className="header-left">
            <h1>{getPageTitle()}</h1>
            <span className="result-count">{filteredProducts.length} sản phẩm</span>
          </div>
          <div className="header-right">
            <button
              className={`filter-toggle ${showFilters ? 'active' : ''}`}
              onClick={() => setShowFilters(!showFilters)}
            >
              <SlidersHorizontal size={18} />
              Bộ lọc
            </button>
            <div className="view-modes">
              <button
                className={viewMode === 'grid' ? 'active' : ''}
                onClick={() => setViewMode('grid')}
              >
                <Grid size={18} />
              </button>
              <button
                className={viewMode === 'list' ? 'active' : ''}
                onClick={() => setViewMode('list')}
              >
                <List size={18} />
              </button>
            </div>
          </div>
        </div>

        {/* Filters Bar */}
        {showFilters && (
          <div className="filters-bar">
            <div className="filter-group">
              <label>Sắp xếp</label>
              <select value={sortBy} onChange={(e) => setSortBy(e.target.value)}>
                <option value="newest">Mới nhất</option>
                <option value="popular">Bán chạy</option>
                <option value="rating">Đánh giá cao</option>
                <option value="price-asc">Giá: Thấp → Cao</option>
                <option value="price-desc">Giá: Cao → Thấp</option>
              </select>
            </div>
            <div className="filter-group">
              <label>Khoảng giá</label>
              <div className="price-inputs">
                <input
                  type="number"
                  placeholder="Từ"
                  value={priceRange.min}
                  onChange={(e) => setPriceRange(prev => ({ ...prev, min: e.target.value }))}
                />
                <span>-</span>
                <input
                  type="number"
                  placeholder="Đến"
                  value={priceRange.max}
                  onChange={(e) => setPriceRange(prev => ({ ...prev, max: e.target.value }))}
                />
              </div>
            </div>
            <button className="clear-filters" onClick={clearFilters}>
              <X size={16} /> Xóa bộ lọc
            </button>
          </div>
        )}

        {/* Content */}
        {error && <div className="error-message">{error}</div>}

        {loading ? (
          <div className="loading-state">
            <div className="spinner"></div>
            <p>Đang tải sản phẩm...</p>
          </div>
        ) : filteredProducts.length > 0 ? (
          <div className={`products-grid ${viewMode}`}>
            {filteredProducts.map(item => (
              <ProductCard key={item.uuid || item.id} item={item} />
            ))}
          </div>
        ) : (
          <div className="empty-state">
            <Package size={64} />
            <h3>Không tìm thấy sản phẩm</h3>
            <p>Thử thay đổi bộ lọc hoặc tìm với từ khóa khác</p>
          </div>
        )}
      </div>
    </div>
  );
};

export default SearchPage;