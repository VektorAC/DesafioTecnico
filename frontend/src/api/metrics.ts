import api from '../lib/axios'

export type MonthlyRow = { month: string; orders: number; total: number }
export type TopRow = { sku: string; title: string; qty: number; revenue: number }

export type MetricsResponse = {
  range: { from: string; to: string }
  kpis: { orders: number; revenue: number; aov: number }
  monthly_sales: MonthlyRow[]
  top_products: TopRow[]
  status_breakdown: Record<string, number>
  currency: string
}
export async function getMetrics(params: { shop_id?: number|null; from?: string; to?: string }) {
  const { data } = await api.get<MetricsResponse>('/api/metrics', { params })
  return data
}
