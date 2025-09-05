import api from '../lib/axios'
import type { AxiosError } from 'axios'

export type ShopRow = { id:number; provider:string; domain:string; status:string; created_at:string }

export async function listShops(): Promise<ShopRow[]> {
  try {
    const { data } = await api.get('/api/shops')
    return data.data as ShopRow[]
  } catch (err) {
    const e = err as AxiosError
    if (e.response?.status === 401) return []
    throw err
  }
}

export function connectShopify(shopDomain: string) {
  const base = import.meta.env.VITE_API_URL || window.location.origin
  window.location.href = `${base}/shops/connect?shop=${encodeURIComponent(shopDomain)}`
}

export async function connectWoo(input: { domain: string; ck: string; cs: string }) {
  const { data } = await api.post<{ ok: boolean; shop_id: number }>('/shops/woo', input)
  return data
}
