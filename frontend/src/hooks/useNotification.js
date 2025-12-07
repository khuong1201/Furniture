import { useState, useCallback } from 'react';
import NotificationService from '@/services/customer/NotificationService';

export const useNotification = () => {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0); // Quan trọng: Số lượng chưa đọc
  const [pagination, setPagination] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // --- 1. Lấy danh sách ---
  const fetchNotifications = useCallback(async (params = {}) => {
    setLoading(true);
    setError(null);
    try {
      const response = await NotificationService.getNotifications(params);
      
      // Controller trả về: { data: [], meta: { unread_count, ... } }
      if (response && Array.isArray(response.data)) {
        setNotifications(response.data);
        
        if (response.meta) {
          setUnreadCount(response.meta.unread_count || 0);
          setPagination({
            currentPage: response.meta.current_page,
            lastPage: response.meta.last_page,
            total: response.meta.total,
            perPage: response.meta.per_page
          });
        }
      } else {
        setNotifications([]);
      }
    } catch (err) {
      console.error('Fetch notifications error:', err);
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 2. Đánh dấu đã đọc (1 cái) ---
  const markAsRead = useCallback(async (uuid) => {
    try {
      // Cập nhật UI ngay lập tức (Optimistic Update)
      setNotifications(prev => prev.map(item => {
        if (item.uuid === uuid && !item.read_at) {
           // Nếu chưa đọc -> đánh dấu đã đọc & giảm count
           setUnreadCount(count => Math.max(0, count - 1));
           return { ...item, read_at: new Date().toISOString() };
        }
        return item;
      }));

      // Gọi API ngầm
      await NotificationService.markAsRead(uuid);
    } catch (err) {
      console.error('Mark read error:', err);
      // Nếu lỗi thì nên fetch lại để đồng bộ
      fetchNotifications();
    }
  }, [fetchNotifications]);

  // --- 3. Đánh dấu đã đọc TẤT CẢ ---
  const markAllAsRead = useCallback(async () => {
    try {
      // Optimistic Update
      setNotifications(prev => prev.map(item => ({ ...item, read_at: new Date().toISOString() })));
      setUnreadCount(0);

      await NotificationService.markAllAsRead();
    } catch (err) {
      console.error('Mark all read error:', err);
      fetchNotifications();
    }
  }, [fetchNotifications]);

  // --- 4. Xóa thông báo ---
  const deleteNotification = useCallback(async (uuid) => {
    try {
      // Tìm item sắp xóa để xem nó đã đọc chưa (nếu chưa đọc thì phải giảm count)
      const itemToDelete = notifications.find(n => n.uuid === uuid);
      const isUnread = itemToDelete && !itemToDelete.read_at;

      // Optimistic Update: Xóa khỏi list
      setNotifications(prev => prev.filter(item => item.uuid !== uuid));
      if (isUnread) {
        setUnreadCount(count => Math.max(0, count - 1));
      }

      await NotificationService.deleteNotification(uuid);
    } catch (err) {
      console.error('Delete notification error:', err);
      fetchNotifications();
    }
  }, [notifications, fetchNotifications]);

  return {
    notifications,
    unreadCount, // Dùng biến này hiển thị Badge đỏ
    pagination,
    loading,
    error,
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    deleteNotification
  };
};