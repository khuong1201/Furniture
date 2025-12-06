import { Routes, Route } from 'react-router-dom';
import './App.css'
import CustomerLayout from './layouts/CustomerLayout';
import AdminLayout from './layouts/AdminLayout';

// Customer Pages
import HomePage from './pages/customer/homePage/HomePage';
import Register from './pages/customer/register/RegisterForm';
import LogIn from './pages/customer/login/LoginForm';
import ProductDetail from './pages/customer/Product/ProductDetail';
import SearchPage from './pages/customer/searchPage/SearchPage';
import CartPage from './pages/customer/cart/CartPage';
import AddressForm from './pages/customer/address/AddressForm';
import Orders from './pages/customer/order/OrderDetail';

// Admin Pages
import Dashboard from './pages/admin/dashboard/Dashboard';
import ProductList from './pages/admin/products/ProductList';
import ProductForm from './pages/admin/products/ProductForm';
import AdminProductDetail from './pages/admin/products/ProductDetail';
import CategoryList from './pages/admin/categories/CategoryList';
import CategoryForm from './pages/admin/categories/CategoryForm';
import OrderList from './pages/admin/orders/OrderList';
import OrderForm from './pages/admin/orders/OrderForm';
import OrderDetail from './pages/admin/orders/OrderDetail';
import UserList from './pages/admin/users/UserList';
import UserForm from './pages/admin/users/UserForm';
import UserDetail from './pages/admin/users/UserDetail';
import InventoryList from './pages/admin/inventory/InventoryList';
import WarehouseList from './pages/admin/warehouses/WarehouseList';
import WarehouseForm from './pages/admin/warehouses/WarehouseForm';
import PromotionList from './pages/admin/promotions/PromotionList';
import PromotionForm from './pages/admin/promotions/PromotionForm';
import RoleList from './pages/admin/roles/RoleList';
import RoleForm from './pages/admin/roles/RoleForm';
import PermissionList from './pages/admin/permissions/PermissionList';
import LogList from './pages/admin/activity-logs/LogList';
import AttributeList from './pages/admin/attributes/AttributeList';
import PaymentList from './pages/admin/payments/PaymentList';
import ReviewList from './pages/admin/reviews/ReviewList';
import CollectionList from './pages/admin/collections/CollectionList';
import CollectionForm from './pages/admin/collections/CollectionForm';
import ShippingList from './pages/admin/shippings/ShippingList';
import ShippingForm from './pages/admin/shippings/ShippingForm';
import SettingsPage from './pages/admin/settings/SettingsPage';
import AdminProfile from './pages/admin/profile/AdminProfile';
import AdminLogin from './pages/admin/login/AdminLogin';
import NotificationList from './pages/admin/notifications/NotificationList';

function App() {
  return (
    <Routes>

      {/*CUSTOMER */}
      <Route path="/customer" element={<CustomerLayout />}>

        <Route index element={<HomePage />} />
        <Route path="register" element={<Register />} />
        <Route path="login" element={<LogIn />} />
        <Route path="product/:id" element={<ProductDetail />} />
        <Route path="product" element={<SearchPage />} />
        <Route path="cart" element={<CartPage />} />
        <Route path="address" element={<AddressForm />} />
        <Route path="orders" element={<Orders />} />

      </Route>

      {/* ADMIN LOGIN - Outside AdminLayout */}
      <Route path="/admin/login" element={<AdminLogin />} />

      {/* ADMIN */}
      <Route path='/admin' element={<AdminLayout />}>
        <Route index element={<Dashboard />} />
        <Route path="products" element={<ProductList />} />
        <Route path="products/create" element={<ProductForm />} />
        <Route path="products/:uuid/edit" element={<ProductForm />} />
        <Route path="products/:uuid" element={<AdminProductDetail />} />
        <Route path="categories" element={<CategoryList />} />
        <Route path="categories/create" element={<CategoryForm />} />
        <Route path="categories/:uuid/edit" element={<CategoryForm />} />
        <Route path="orders" element={<OrderList />} />
        <Route path="orders/create" element={<OrderForm />} />
        <Route path="orders/:uuid" element={<OrderDetail />} />
        <Route path="users" element={<UserList />} />
        <Route path="users/create" element={<UserForm />} />
        <Route path="users/:uuid" element={<UserDetail />} />
        <Route path="users/:uuid/edit" element={<UserForm />} />
        <Route path="inventory" element={<InventoryList />} />
        <Route path="warehouses" element={<WarehouseList />} />
        <Route path="warehouses/create" element={<WarehouseForm />} />
        <Route path="warehouses/:uuid/edit" element={<WarehouseForm />} />
        <Route path="promotions" element={<PromotionList />} />
        <Route path="promotions/create" element={<PromotionForm />} />
        <Route path="promotions/:uuid/edit" element={<PromotionForm />} />
        <Route path="roles" element={<RoleList />} />
        <Route path="roles/create" element={<RoleForm />} />
        <Route path="roles/:uuid/edit" element={<RoleForm />} />
        <Route path="permissions" element={<PermissionList />} />
        <Route path="logs" element={<LogList />} />
        <Route path="attributes" element={<AttributeList />} />
        <Route path="payments" element={<PaymentList />} />
        <Route path="reviews" element={<ReviewList />} />
        <Route path="collections" element={<CollectionList />} />
        <Route path="collections/create" element={<CollectionForm />} />
        <Route path="collections/:uuid/edit" element={<CollectionForm />} />
        <Route path="shippings" element={<ShippingList />} />
        <Route path="shippings/create" element={<ShippingForm />} />
        <Route path="shippings/:uuid/edit" element={<ShippingForm />} />
        <Route path="settings" element={<SettingsPage />} />
        <Route path="notifications" element={<NotificationList />} />
        <Route path="notifications/:uuid" element={<NotificationList />} />
        <Route path="profile" element={<AdminProfile />} />
      </Route>
    </Routes>
  );
}

export default App;


