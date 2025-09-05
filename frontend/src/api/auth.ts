import api, { setAuthToken } from '../lib/axios'

export async function loginDev(username: string, password: string) {
  const { data } = await api.post<{ token: string }>('/api/dev/login', { username, password })
  setAuthToken(data.token)
  return data.token
}

export async function me() {
  const { data } = await api.get('/api/auth/me')
  return data
}

export async function logoutDev() {
  await api.post('/api/dev/logout')
  setAuthToken(null)
}
