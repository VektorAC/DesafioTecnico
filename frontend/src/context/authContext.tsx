import { createContext, useContext, useEffect, useState } from 'react'
import { loginDev, logoutDev, me } from '../api/auth'
import { setAuthToken } from '../lib/axios'

type Ctx = { authed: boolean; loading: boolean; login: (u:string,p:string)=>Promise<void>; signout: ()=>Promise<void> }
const AuthCtx = createContext<Ctx | null>(null)

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [authed, setAuthed] = useState(false)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const t = localStorage.getItem('token')
    if (t) setAuthToken(t);
    (async () => {
      try { if (t) await me(); setAuthed(!!t) } catch { setAuthed(false) }
      finally { setLoading(false) }
    })()
  }, [])

  const login = async (u: string, p: string) => {
    const token = await loginDev(u, p)
    localStorage.setItem('token', token)
    setAuthed(true)
  }

  const signout = async () => {
    try { await logoutDev() } finally {
      localStorage.removeItem('token')
      setAuthToken(null)
      setAuthed(false)
    }
  }

  return <AuthCtx.Provider value={{ authed, loading, login, signout }}>{children}</AuthCtx.Provider>
}

// eslint-disable-next-line react-refresh/only-export-components
export const useAuth = () => {
  const ctx = useContext(AuthCtx)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}
