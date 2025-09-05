import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { useAuth } from '../context/authContext'

export default function RequireAuth() {
  const { loading, authed } = useAuth()
  const loc = useLocation()
  if (loading) return null
  if (!authed) return <Navigate to="/login" replace state={{ from: loc.pathname }} />
  return <Outlet />
}
