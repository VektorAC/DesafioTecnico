import { Routes, Route, Navigate } from 'react-router-dom'
import Layout from './components/layout'
import LoginPage from './pages/LoginPage'
import ProductsPage from './pages/ProductPage'
import OrdersPage from './pages/OrdersPage'
import ConnectPage from './pages/ConnectPage'
import RequireAuth from './components/requireAuth'

export default function App() {
  return (
    <Routes>
      <Route element={<Layout />}>
        {/* index redirige a /products */}
        <Route index element={<Navigate to="/products" replace />} />

        <Route path="/login" element={<LoginPage />} />

        {/* Rutas protegidas */}
        <Route element={<RequireAuth />}>
          <Route path="/products" element={<ProductsPage />} />
          <Route path="/orders" element={<OrdersPage />} />
          <Route path="/connect" element={<ConnectPage />} />
        </Route>

        {/* catch-all */}
        <Route path="*" element={<Navigate to="/products" replace />} />
      </Route>
    </Routes>
  )
}
