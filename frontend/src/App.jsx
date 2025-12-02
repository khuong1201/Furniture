import { useState } from 'react'
import './App.css'
import { Routes, Route } from 'react-router-dom';
import Header from './components/header/Header';
import HomePage from './components/homePage/HomePage';
import Register from './components/register/RegisterForm';
import LogIn from './components/login/LoginForm';
import ProductDetail from './components/Product/ProductDetail';

function App() {
  const [count, setCount] = useState(0)
  return (
    <div>
      <Header/>
      <Routes>x
        {/* HomePage */}
        <Route path="/" element={<HomePage />} />

        {/* Auth */}
        <Route path="/register" element={<Register />} />
        <Route path="/login" element={<LogIn />} />
        
        {/* Product */}
        <Route path="/product/:id" element={<ProductDetail/>} />
      </Routes>
    </div>
  )
}

export default App
