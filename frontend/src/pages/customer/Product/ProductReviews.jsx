// components/product/ProductReviews.jsx
import React, { useEffect, useMemo, useState } from 'react';
import { Star } from 'lucide-react';
// 1. Import hook
import { useReview } from '@/hooks/useReview'; 
import ReviewItem from './ReviewItem';
import styles from './ProductReviews.module.css';

const ProductReviews = ({ productId }) => {
  // 2. Lấy thêm 'stats' và 'fetchReviewStats' từ hook
  const { 
    reviews, 
    stats: apiStats, // Đổi tên biến này để tránh trùng
    loading, 
    error, 
    pagination, 
    fetchReviews,
    fetchReviewStats // Hàm gọi API thống kê
  } = useReview(productId);

  const [filterType, setFilterType] = useState('All');

  // 3. Fetch cả Review List và Review Stats
  useEffect(() => {
    if (productId) {
      fetchReviews();
      fetchReviewStats(); // ✅ Gọi API lấy thống kê
    }
  }, [productId, fetchReviews, fetchReviewStats]);

  // 4. Chuẩn hóa dữ liệu từ API Stats để hiển thị
  // Không tự tính toán (reduce) nữa, mà map dữ liệu từ API vào
  const stats = useMemo(() => {
    // Giá trị mặc định nếu API chưa tải xong
    if (!apiStats) {
        return { 
            average: 0, 
            total: 0,
            distribution: [5, 4, 3, 2, 1].map(s => ({ star: s, percent: '0%', count: 0 }))
        };
    }

    return {
      average: apiStats.average_rating, // Lấy từ field API: average_rating
      total: apiStats.total_reviews,    // Lấy từ field API: total_reviews
      
      // Map distribution từ API (JSON có mảng distribution rồi)
      distribution: apiStats.distribution.map(item => ({
        star: item.star,
        count: item.count,
        percent: `${item.percent}%` // ✅ Thêm dấu % vì API trả về số (100)
      }))
    };
  }, [apiStats]);

  if (loading && !reviews.length && !apiStats) return <div className={styles.loading}>Đang tải đánh giá...</div>;
  if (error) return <div className={styles.error}>Lỗi tải đánh giá: {error}</div>;

  return (
    <div className={styles.wrapper}>
      <h3 className={styles.title}>Product Rating</h3>

      <div className={styles.ratingContainer}>
        {/* --- 1. Tổng quan điểm số --- */}
        <div className={styles.ratingOverview}>
          <div className={styles.ratingScore}>
            {/* Hiển thị điểm trung bình từ API */}
            <span className={styles.scoreNum}>{stats.average}</span>
            <div className={styles.scoreStars}>
              {[1, 2, 3, 4, 5].map((s) => (
                <Star 
                  key={s} 
                  size={20} 
                  // So sánh với điểm trung bình đã làm tròn
                  fill={s <= Math.round(stats.average) ? "#ffc107" : "#e4e5e9"} 
                  color={s <= Math.round(stats.average) ? "#ffc107" : "#e4e5e9"} 
                />
              ))}
            </div>
            {/* Hiển thị tổng số đánh giá từ API */}
            <span className={styles.scoreCount}>{stats.total} Ratings</span>
          </div>

          {/* --- Biểu đồ thanh ngang --- */}
          <div className={styles.ratingBars}>
            {stats.distribution.map((item) => (
              <div key={item.star} className={styles.barRow}>
                <span className={styles.starLabel}>
                  {item.star} <Star size={12} fill="#ffc107" color="#ffc107"/>
                </span>
                <div className={styles.progressBg}>
                  {/* Style width cần string có % */}
                  <div className={styles.progressFill} style={{ width: item.percent }}></div>
                </div>
                <span className={styles.percentLabel}>{item.percent}</span>
              </div>
            ))}
          </div>
        </div>

        {/* --- 2. Bộ lọc (Logic chưa đổi) --- */}
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
                // Map dữ liệu từng item review
                const mappedReview = {
                    id: item.id,
                    uuid: item.uuid,
                    name: item.user?.name || 'Guest User',
                    avatar: item.user?.avatar || 'https://placehold.co/100',
                    rating: item.rating,
                    date: new Date(item.created_at).toLocaleDateString('vi-VN'),
                    content: item.comment,
                    images: item.images || [],
                    likes: 0 
                };
                return <ReviewItem key={item.uuid} review={mappedReview} />;
            })
          ) : (
            <p className={styles.emptyText}>Chưa có đánh giá nào.</p>
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