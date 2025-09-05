// src/pages/MetricsPage.tsx
import { useMemo, useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Row, Col, Card, Typography, Space, DatePicker, Select } from 'antd'
import { Line, Bar } from 'react-chartjs-2'
import { useShop } from '../context/ShopContext'
import { getMetrics } from '../api/metrics'
import '../lib/charts' 
import type { TooltipItem } from 'chart.js'
import dayjs from 'dayjs'

const { Title, Text } = Typography

const todayISO = () => new Date().toISOString().slice(0,10)
const monthsAgoISO = (m: number) => {
  const d = new Date(); d.setMonth(d.getMonth() - m); d.setDate(1); return d.toISOString().slice(0,10)
}

export default function MetricsPage() {
  const { activeShopId } = useShop()
  const [from, setFrom] = useState(monthsAgoISO(6))
  const [to, setTo] = useState(todayISO())
  const [quick, setQuick] = useState<'3m' | '6m' | '12m'>('6m')

  const q = useQuery({
    queryKey: ['metrics', { activeShopId: activeShopId ?? null, from, to }],
    queryFn: () => getMetrics({ shop_id: activeShopId ?? undefined, from, to }),
    enabled: true,   
    staleTime: 30_000,
    retry: 0,
    })

  const m = q.data
  const currency = m?.currency ?? 'CLP'
  const fmtMoney = (n: number) =>
    new Intl.NumberFormat('es-CL', { style: 'currency', currency }).format(n)

  // Linea ventas por mes
  const lineData = useMemo(() => {
    const labels = m?.monthly_sales?.map((r) => r.month) ?? []
    const data = m?.monthly_sales?.map((r) => r.total) ?? []
    return {
      labels,
      datasets: [
        {
          label: 'Ventas',
          data,
          borderWidth: 2,
          pointRadius: 0,
          tension: 0.25,
        },
      ],
    }
  }, [m])

  const lineOptions = useMemo(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (ctx: TooltipItem<'line'>) => ` ${fmtMoney(Number(ctx.parsed.y))}`,
        },
      },
    },
    scales: {
      x: { grid: { display: false } },
      y: {
        ticks: {
          callback: (value: number | string) => fmtMoney(Number(value)),
        },
      },
    },
  }), [currency])

  //  barras horizontales (top productos)
  const barData = useMemo(() => {
    const rows = m?.top_products ?? []
    const labels = rows.map((r) => r.title || r.sku || '—')
    const dataQty = rows.map((r) => r.qty || 0)
    return {
      labels,
      datasets: [
        {
          label: 'Unidades',
          data: dataQty,
          borderWidth: 1,
        },
      ],
    }
  }, [m])

  const barOptions = useMemo(() => ({
    indexAxis: 'y' as const,
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { enabled: true },
    },
    scales: {
      x: { grid: { display: true } },
      y: { grid: { display: false } },
    },
  }), [])

  return (
    <Space direction="vertical" size={16} style={{ width: '100%' }}>
      <Title level={4} style={{ margin: 0 }}>Métricas</Title>

      <Card>
        <Row gutter={[16, 16]} align="middle">
          <Col>
            <Space direction="vertical" size={4}>
              <Text type="secondary">Desde</Text>
              <DatePicker
                value={dayjs(from)}
                onChange={(_, dateString) => {
                  if (typeof dateString === 'string') {
                    setFrom(dateString)
                  } else {
                    setFrom(from)
                  }
                }}
                allowClear={false}
                inputReadOnly
              />
            </Space>
          </Col>
          <Col>
            <Space direction="vertical" size={4}>
              <Text type="secondary">Hasta</Text>
              <DatePicker
                value={dayjs(to)}
                onChange={(_, dateString) => {
                  if (typeof dateString === 'string') {
                    setTo(dateString)
                  } else {
                    setTo(to)
                  }
                }}
                allowClear={false}
                inputReadOnly
              />
            </Space>
          </Col>
          <Col flex="auto" />
          <Col>
            <Space direction="vertical" size={4}>
              <Text type="secondary">Rango rápido</Text>
              <Select
                value={quick}
                style={{ width: 180 }}
                options={[
                  { value: '3m', label: 'Últimos 3 meses' },
                  { value: '6m', label: 'Últimos 6 meses' },
                  { value: '12m', label: 'Últimos 12 meses' },
                ]}
                onChange={(v) => {
                  setQuick(v)
                  if (v === '3m') setFrom(monthsAgoISO(3))
                  if (v === '6m') setFrom(monthsAgoISO(6))
                  if (v === '12m') setFrom(monthsAgoISO(12))
                }}
              />
            </Space>
          </Col>
        </Row>
      </Card>

      <Row gutter={[16, 16]}>
        {/* KPIs */}
        <Col xs={24} md={8}>
          <Card>
            <Text type="secondary">Ventas</Text>
            <Title level={5} style={{ marginTop: 4 }}>
              {m ? fmtMoney(m.kpis.revenue) : '—'}
            </Title>
          </Card>
        </Col>
        <Col xs={24} md={8}>
          <Card>
            <Text type="secondary">Órdenes</Text>
            <Title level={5} style={{ marginTop: 4 }}>
              {m ? m.kpis.orders.toLocaleString('es-CL') : '—'}
            </Title>
          </Card>
        </Col>
        <Col xs={24} md={8}>
          <Card>
            <Text type="secondary">Ticket promedio</Text>
            <Title level={5} style={{ marginTop: 4 }}>
              {m ? fmtMoney(m.kpis.aov) : '—'}
            </Title>
          </Card>
        </Col>

        {/* Ventas por mes */}
        <Col xs={24} md={16}>
          <Card>
            <Title level={5} style={{ marginBottom: 8 }}>Ventas por mes</Title>
            <div style={{ height: 260 }}>
              <Line data={lineData} options={lineOptions} />
            </div>
          </Card>
        </Col>

        {/* Top productos */}
        <Col xs={24} md={8}>
          <Card>
            <Title level={5} style={{ marginBottom: 8 }}>Top productos (unidades)</Title>
            <div style={{ height: 260 }}>
              <Bar data={barData} options={barOptions} />
            </div>
          </Card>
        </Col>
      </Row>
    </Space>
  )
}
