import { useState } from 'react'
import './App.css'
import { Routes, Route } from 'react-router-dom';
import Header from './components/header/Header';
import HomePage from './components/homePage/HomePage';
import SignUp from './components/signUp/SignUpForm';
import LogIn from './components/login/LoginForm';

function App() {
  const [count, setCount] = useState(0)
  return (
    <div>
      <Header/>
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/signup" element={<SignUp />} />
        <Route path="/login" element={<LogIn />} />
      </Routes>
    </div>
  )
}

export default App
