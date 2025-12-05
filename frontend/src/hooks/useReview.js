import { useState, useCallback } from 'react';
import ReviewService from '@/services/customer/ReviewService';

export const useReview = (productUuid = null) => {
  const [reviews, setReviews] = useState([]);
  const [stats, setStats] = useState(null); // ✅ State mới cho thống kê
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [message, setMessage] = useState(null);
  const [pagination, setPagination] = useState(null);

  // ✅ LẤY DANH SÁCH REVIEW (List)
  const fetchReviews = useCallback(async (params = {}) => {
    setLoading(true);
    setError(null);

    try {
      // Gọi API Get List
      const data = await ReviewService.getReviews({
        product_uuid: productUuid,
        ...params,
      });

      const items = data?.data || data || [];
      setReviews(items);

      if (data?.meta) {
        setPagination(data.meta);
      }
      return items;
    } catch (err) {
      console.error("Load reviews error:", err);
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [productUuid]);

  // ✅ LẤY THỐNG KÊ REVIEW (Stats) - MỚI
  const fetchReviewStats = useCallback(async () => {
    if (!productUuid) return;
    
    try {
      const data = await ReviewService.getReviewStats(productUuid);
      setStats(data);
    } catch (err) {
      console.error("Load stats error:", err);
      // Không set Error chung để tránh che mất danh sách review nếu chỉ lỗi stats
    }
  }, [productUuid]);

  // ✅ TẠO REVIEW
  const createReview = async (payload) => {
    setLoading(true);
    setError(null);
    setMessage(null);

    try {
      const result = await ReviewService.createReview(payload);
      setMessage('✅ Gửi đánh giá thành công!');

      // Reload lại cả list và stats để đồng bộ dữ liệu mới
      await Promise.all([
        fetchReviews(),
        fetchReviewStats()
      ]);

      return result;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // ✅ UPDATE REVIEW
  const updateReview = async (uuid, payload) => {
    setLoading(true);
    setError(null);
    setMessage(null);

    try {
      const result = await ReviewService.updateReview(uuid, payload);
      setMessage('✅ Cập nhật đánh giá thành công!');

      // Update local state danh sách cho mượt
      setReviews(prev =>
        prev.map(item =>
          item.uuid === uuid ? { ...item, ...result } : item
        )
      );
      
      // Reload stats vì rating có thể thay đổi
      fetchReviewStats(); 

      return result;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // ✅ DELETE REVIEW
  const deleteReview = async (uuid) => {
    if (!window.confirm('Bạn có chắc muốn xóa đánh giá này?')) return;

    setLoading(true);
    setError(null);
    setMessage(null);

    try {
      await ReviewService.deleteReview(uuid);
      setMessage('✅ Đã xóa đánh giá!');

      // Xóa trong state danh sách
      setReviews(prev => prev.filter(item => item.uuid !== uuid));
      
      // Reload stats để trừ đi sao của bài vừa xóa
      fetchReviewStats(); 

    } catch (err) {
      setError(err.message);
      alert(err.message);
    } finally {
      setLoading(false);
    }
  };

  return {
    reviews,
    stats, // ✅ Return stats ra ngoài
    loading,
    error,
    message,
    pagination,

    fetchReviews,
    fetchReviewStats, // ✅ Return hàm fetch stats
    createReview,
    updateReview,
    deleteReview,
  };
};