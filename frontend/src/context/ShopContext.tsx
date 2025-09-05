import { createContext, useContext, useEffect, useMemo, useState, } from 'react'
import { listShops } from '../api/shops'
import type { ShopRow } from '../api/shops'
import { useAuth } from '../context/authContext'

type Ctx = {
  shops: ShopRow[]
  activeShopId: number | null
  setActiveShopId: (id: number | null) => void
  refresh: () => Promise<void>
}

const ShopCtx = createContext<Ctx | null>(null)

export function ShopProvider({ children }: { children: React.ReactNode }) {
  const { authed } = useAuth()               // 👈 clave
  const [shops, setShops] = useState<ShopRow[]>([])
  const [activeShopId, setActiveShopIdState] = useState<number | null>(() => {
    const raw = localStorage.getItem('activeShopId')
    return raw ? Number(raw) : null
  })

  const setActiveShopId = (id: number | null) => {
    setActiveShopIdState(id)
    if (id == null) localStorage.removeItem('activeShopId')
    else localStorage.setItem('activeShopId', String(id))
  }

  const refresh = async () => {
    const s = await listShops()
    setShops(s)
    if (!activeShopId && s.length > 0) setActiveShopId(s[0].id)
  }

  useEffect(() => {
    if (authed) {
      void refresh()
    } else {
      setShops([])
      setActiveShopId(null)
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [authed])

  const value = useMemo(() => ({ shops, activeShopId, setActiveShopId, refresh }), [shops, activeShopId])
  return <ShopCtx.Provider value={value}>{children}</ShopCtx.Provider>
}

// eslint-disable-next-line react-refresh/only-export-components
export function useShop() {
  const ctx = useContext(ShopCtx)
  if (!ctx) throw new Error('useShop must be used within ShopProvider')
  return ctx
}
