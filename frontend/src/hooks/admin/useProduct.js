import { useState, useCallback } from 'react';
import ProductService from '@/services/admin/ProductService';

export const useProduct = () => {
    const [products, setProducts] = useState([]);
    const [productDetail, setProductDetail] = useState(null);
    const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [loading, setLoading] = useState(false);
    const [generating, setGenerating] = useState(false);

    // 1. Fetch List
    const fetchProducts = useCallback(async (params = {}) => {
        setLoading(true);
        try {
            const queryParams = { page: 1, per_page: 15, ...params };
            const res = await ProductService.instance.getProducts(queryParams);
            setProducts(res.data || []);
            if (res.meta) setMeta(res.meta);
        } catch (err) {
            console.error("Fetch Products Error:", err);
            setProducts([]);
        } finally {
            setLoading(false);
        }
    }, []);

    // 2. Detail
    const getDetail = useCallback(async (uuid) => {
        setLoading(true);
        try {
            const res = await ProductService.instance.getProduct(uuid);
            setProductDetail(res.data);
            return res.data;
        } catch (err) {
            console.error("Get Detail Error:", err);
            return null;
        } finally {
            setLoading(false);
        }
    }, []);

    // 3. Create
    const createProduct = async (data) => {
        setLoading(true);
        try {
            const res = await ProductService.instance.createProduct(data);
            return res.data;
        } catch (err) {
            throw err;
        } finally {
            setLoading(false);
        }
    };

    // 4. Update
    const updateProduct = async (uuid, data) => {
        setLoading(true);
        try {
            const res = await ProductService.instance.updateProduct(uuid, data);
            return res.data;
        } catch (err) {
            throw err;
        } finally {
            setLoading(false);
        }
    };

    // 5. Delete
    const deleteProduct = async (uuid) => {
        setLoading(true);
        try {
            await ProductService.instance.deleteProduct(uuid);
            return true;
        } catch (err) {
            throw err;
        } finally {
            setLoading(false);
        }
    };

    // 6. Upload Images (FIXED LOGIC)
    // Input: Mảng các object { file: File, is_primary: boolean }
    const uploadImages = async (productUuid, mediaItems) => {
        try {
            await Promise.all(mediaItems.map(item => {
                // Truyền đúng 3 tham số: UUID, File, Boolean
                return ProductService.instance.uploadImage(productUuid, item.file, item.is_primary);
            }));
        } catch (err) {
            console.error("Upload Image Error:", err);
            throw err; // Ném lỗi để Form catch được
        }
    };

    // 7. AI
    const generateDescription = async (payload) => {
        setGenerating(true);
        try {
            const res = await ProductService.instance.generateDescription(payload);
            return res.data?.description || res.description || "";
        } catch (err) {
            throw err;
        } finally {
            setGenerating(false);
        }
    };

    return {
        products, productDetail, meta, loading, generating,
        fetchProducts, getDetail, createProduct, updateProduct, deleteProduct,
        uploadImages, generateDescription
    };
};