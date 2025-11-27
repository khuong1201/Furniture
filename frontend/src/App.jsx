import { useState } from 'react'
import './App.css'
import Header from './components/Header';

function App() {
  const [count, setCount] = useState(0)
  return (
    <div>
      <Header/>
      <main>
        <h1> Chào mừng đến với Furniture Web</h1>
        <p> Nơi cung cấp nội thất cho your house.</p>
      </main>
    </div>
  )
}

export default App
