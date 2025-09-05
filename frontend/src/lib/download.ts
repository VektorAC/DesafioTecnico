import api from './axios'

export async function downloadFile(path: string, params?: Record<string, unknown>) {
  const res = await api.get(path, { params, responseType: 'blob' })
  const cd = res.headers['content-disposition'] || ''
  const filenameMatch = cd.match(/filename="?([^"]+)"?/)
  const filename = filenameMatch?.[1] || 'download'
  const url = URL.createObjectURL(res.data)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  document.body.appendChild(a)
  a.click()
  a.remove()
  URL.revokeObjectURL(url)
}
