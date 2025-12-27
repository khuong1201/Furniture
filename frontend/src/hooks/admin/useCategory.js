import { useState, useCallback } from 'react';
import CategoryService from '@/services/admin/CategoryService';

export const useCategory = () => {
    const [loading, setLoading] = useState(false);
    const [categories, setCategories] = useState([]); // Dạng phẳng (cho list)
    const [categoryTree, setCategoryTree] = useState([]); // Dạng cây (cho select box)
    const [pagination, setPagination] = useState({});

    // Lấy danh sách danh mục (Phẳng)
    const fetchCategories = useCallback(async (params = {}) => {
        setLoading(true);
        try {
            const response = await CategoryService.getCategories(params);
            
            // --- FIX QUAN TRỌNG: Map đúng cấu trúc JSON { data: [], meta: {} } ---
            // API trả về: { success: true, data: [...], meta: {...} }
            // Code cũ sai vì gọi response.data.data
            setCategories(response.data || []); 
            setPagination(response.meta || {});
            
        } catch (err) {
            console.error("Lỗi fetch categories:", err);
            setCategories([]); // Fallback rỗng nếu lỗi
        } finally {
            setLoading(false);
        }
    }, []);

    // Lấy dạng cây (nếu backend hỗ trợ)
    const fetchCategoryTree = useCallback(async () => {
        setLoading(true);
        try {
            // Giả sử API có endpoint hoặc param tree
            // Nếu dùng chung endpoint thì gọi lại fetchCategories là được
            const response = await CategoryService.instance._request('/categories?tree=true');
            setCategoryTree(response.data || []);
        } catch (err) {
            console.error("Lỗi fetch category tree:", err);
        } finally {
            setLoading(false);
        }
    }, []);

    const createCategory = async (data) => {
        setLoading(true);
        try {
            await CategoryService.createCategory(data);
            await fetchCategories(); // Refresh lại list sau khi tạo
        } catch (error) {
            throw error; // Ném lỗi để component xử lý (ví dụ: hiện toast)
        } finally {
            setLoading(false);
        }
    };

    const updateCategory = async (uuid, data) => {
        setLoading(true);
        try {
            await CategoryService.updateCategory(uuid, data);
            await fetchCategories(); 
        } catch (error) {
            throw error;
        } finally {
            setLoading(false);
        }
    };

    const deleteCategory = async (uuid) => {
        setLoading(true);
        try {
            await CategoryService.deleteCategory(uuid);
            // Xóa optimistic trên UI cho nhanh
            setCategories(prev => prev.filter(c => c.uuid !== uuid));
            // Hoặc gọi lại API để chắc chắn: await fetchCategories();
        } catch (error) {
            throw error;
        } finally {
            setLoading(false);
        }
    };

    return {
        loading,
        categories,
        categoryTree,
        pagination,
        fetchCategories,
        fetchCategoryTree,
        createCategory,
        updateCategory,
        deleteCategory
    };
};