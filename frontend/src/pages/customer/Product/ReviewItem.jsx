// components/product/ReviewItem.jsx
import React from 'react';
import { Star, ThumbsUp } from 'lucide-react';
import styles from './ReviewItem.module.css'; // Import file CSS đã tách

const ReviewItem = ({ review }) => {
  return (
    <div className={styles.reviewItem}>
      {/* Avatar người dùng */}
      <img 
        src={review.avatar} 
        alt="User Avatar" 
        className={styles.userAvatar} 
      />

      <div className={styles.reviewContent}>
        {/* Header: Tên + Sao */}
        <div className={styles.reviewHeader}>
          <span className={styles.userName}>{review.name}</span>
          <div className={styles.userRating}>
            {[1, 2, 3, 4, 5].map((star) => (
              <Star 
                key={star} 
                size={12} 
                // Logic tô màu: Nếu sao hiện tại <= rating của bài review thì tô vàng, ngược lại tô xám
                fill={star <= review.rating ? "#ffc107" : "#e4e5e9"} 
                color={star <= review.rating ? "#ffc107" : "#e4e5e9"} 
              />
            ))}
          </div>
        </div>

        {/* Ngày đăng */}
        <span className={styles.reviewDate}>{review.date}</span>

        {/* Nội dung text */}
        <p className={styles.reviewText}>{review.content}</p>

        {/* Hình ảnh đính kèm (chỉ hiển thị nếu có) */}
        {review.images && review.images.length > 0 && (
          <div className={styles.reviewImages}>
            {review.images.map((img, idx) => (
              <img key={idx} src={img} alt={`Review ${idx}`} />
            ))}
          </div>
        )}

        {/* Nút Like */}
        <div>
          <button className={styles.btnLike}>
            <ThumbsUp size={14} /> {review.likes || 0}
          </button>
        </div>
      </div>
    </div>
  );
};

export default ReviewItem;