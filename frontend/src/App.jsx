import { Routes, Route } from 'react-router-dom';
import './App.css'
import CustomerLayout from './layouts/CustomerLayout';
import AdminLayout from './layouts/AdminLayout';
import HomePage from './pages/customer/homePage/HomePage';
import Register from './pages/customer/register/RegisterForm';
import LogIn from './pages/customer/login/LoginForm';
import ProductDetail from './pages/customer/Product/ProductDetail';
import Dashboard from './pages/admin/dashboard/Dashboard';
import ProductList from './pages/admin/products/ProductList';
import AdminProductDetail from './pages/admin/products/ProductDetail';
import CategoryList from './pages/admin/categories/CategoryList';
import CategoryForm from './pages/admin/categories/CategoryForm';
import OrderList from './pages/admin/orders/OrderList';
import OrderDetail from './pages/admin/orders/OrderDetail';
import UserList from './pages/admin/users/UserList';

function App() {
  return (
    <Routes>

      {/*CUSTOMER */}
      <Route path="/customer" element={<CustomerLayout />}>

        <Route index element={<HomePage />} />
        <Route path="register" element={<Register />} />
        <Route path="login" element={<LogIn />} />
        <Route path="product/:id" element={<ProductDetail />} />

      </Route>

      {/* ADMIN */}
      <Route path='/admin' element={<AdminLayout />}>
        <Route index element={<Dashboard />} />
        <Route path="products" element={<ProductList />} />
        <Route path="products/:uuid" element={<AdminProductDetail />} />
        <Route path="categories" element={<CategoryList />} />
        <Route path="categories/create" element={<CategoryForm />} />
        <Route path="categories/:uuid/edit" element={<CategoryForm />} />
        <Route path="orders" element={<OrderList />} />
        <Route path="orders/:uuid" element={<OrderDetail />} />
        <Route path="users" element={<UserList />} />
      </Route>
    </Routes>
  );
}

export default App;
