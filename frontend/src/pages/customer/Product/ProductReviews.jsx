// components/product/ProductReviews.jsx
import React, { useEffect, useMemo, useState } from 'react';
import { Star, ThumbsUp } from 'lucide-react';
import { useReview } from '@/hooks/useReview'; 
import ReviewItem from './ReviewItem';
import styles from './ProductReviews.module.css';

const ProductReviews = ({ productId }) => {
  // 1. Gọi hook xử lý API
  const { reviews, loading, error, pagination, fetchReviews } = useReview(productId);
  const [filterType, setFilterType] = useState('All');

  // 2. Fetch dữ liệu khi có productId
  useEffect(() => {
    if (productId) {
      fetchReviews();
    }
  }, [productId, fetchReviews]);

  // 3. Tính toán thống kê (Rating Score & Bars) dựa trên dữ liệu reviews
  const stats = useMemo(() => {
    const total = reviews.length;
    if (total === 0) return { average: 0, distribution: [] };

    const sum = reviews.reduce((acc, r) => acc + r.rating, 0);
    const average = (sum / total).toFixed(1);

    // Tính % cho từng mức sao (5, 4, 3, 2, 1)
    const distribution = [5, 4, 3, 2, 1].map(star => {
      const count = reviews.filter(r => r.rating === star).length;
      const percent = total > 0 ? Math.round((count / total) * 100) : 0;
      return { star, percent: `${percent}%`, count };
    });

    return { average, distribution };
  }, [reviews]);

  if (loading && reviews.length === 0) return <div className={styles.loading}>Đang tải đánh giá...</div>;
  if (error) return <div className={styles.error}>Lỗi tải đánh giá: {error}</div>;

  return (
    <div className={styles.wrapper}>
      <h3 className={styles.title}>Product Rating</h3>

      <div className={styles.ratingContainer}>
        {/* --- 1. Tổng quan điểm số (Bên trái) --- */}
        <div className={styles.ratingOverview}>
          <div className={styles.ratingScore}>
            <span className={styles.scoreNum}>{stats.average || 0}</span>
            <div className={styles.scoreStars}>
              {[1, 2, 3, 4, 5].map((s) => (
                <Star 
                  key={s} 
                  size={20} 
                  fill={s <= Math.round(stats.average) ? "#ffc107" : "#e4e5e9"} 
                  color={s <= Math.round(stats.average) ? "#ffc107" : "#e4e5e9"} 
                />
              ))}
            </div>
            <span className={styles.scoreCount}>{pagination?.total || 0} Ratings</span>
          </div>

          {/* --- Biểu đồ thanh ngang (Bên phải) --- */}
          <div className={styles.ratingBars}>
            {stats.distribution.map((item) => (
              <div key={item.star} className={styles.barRow}>
                <span className={styles.starLabel}>
                  {item.star} <Star size={12} fill="#ffc107" color="#ffc107"/>
                </span>
                <div className={styles.progressBg}>
                  <div className={styles.progressFill} style={{ width: item.percent }}></div>
                </div>
                <span className={styles.percentLabel}>{item.percent}</span>
              </div>
            ))}
          </div>
        </div>

        {/* --- 2. Bộ lọc --- */}
        <div className={styles.ratingFilters}>
          {['All', 'With Photos', '5 Star', '4 Star', '3 Star', '2 Star', '1 Star'].map((filter, idx) => (
            <button 
              key={idx} 
              className={`${styles.filterBtn} ${filterType === filter ? styles.active : ''}`}
              onClick={() => setFilterType(filter)}
            >
              {filter}
            </button>
          ))}
        </div>

        {/* --- 3. Danh sách đánh giá --- */}
        <div className={styles.reviewList}>
          {reviews.length > 0 ? (
            reviews.map((item) => {
                // Map dữ liệu từ API (item) sang format của ReviewItem component
                const mappedReview = {
                    id: item.id,
                    name: item.user?.name || 'Ẩn danh', // Cần backend trả về user relation
                    avatar: item.user?.avatar || 'https://placehold.co/100',
                    rating: item.rating,
                    date: new Date(item.created_at).toLocaleDateString('vi-VN'),
                    content: item.comment,
                    images: item.images || [],
                    likes: 0 // Backend chưa có thì để 0
                };
                return <ReviewItem key={item.uuid} review={mappedReview} />;
            })
          ) : (
            <p className={styles.emptyText}>Chưa có đánh giá nào cho sản phẩm này.</p>
          )}
        </div>

        {pagination?.total > reviews.length && (
            <button className={styles.btnViewAll}>Xem thêm đánh giá</button>
        )}
      </div>
    </div>
  );
};

export default ProductReviews;