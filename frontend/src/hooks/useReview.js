import { useState, useCallback } from 'react';
import ReviewService from '@/services/customer/ReviewService';

export const useReview = (productUuid = null) => {
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [message, setMessage] = useState(null);
  const [pagination, setPagination] = useState(null);

  // ✅ LẤY DANH SÁCH REVIEW THEO PRODUCT
  const fetchReviews = useCallback(async (params = {}) => {
    setLoading(true);
    setError(null);

    try {
      const data = await ReviewService.getReviews({
        product_uuid: productUuid,
        ...params,
      });

      // Nếu API trả về dạng paginate
      const items = data?.data || data || [];
      setReviews(items);

      if (data?.meta) {
        setPagination(data.meta);
      }

      return items;
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
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

      // ✅ Reload lại review để sync UI
      await fetchReviews();

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

      // ✅ Update local state cho mượt
      setReviews(prev =>
        prev.map(item =>
          item.uuid === uuid ? { ...item, ...result } : item
        )
      );

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

      // ✅ Xóa luôn trong state
      setReviews(prev =>
        prev.filter(item => item.uuid !== uuid)
      );
    } catch (err) {
      setError(err.message);
      alert(err.message);
    } finally {
      setLoading(false);
    }
  };

  return {
    reviews,
    loading,
    error,
    message,
    pagination,

    fetchReviews,
    createReview,
    updateReview,
    deleteReview,
  };
};
