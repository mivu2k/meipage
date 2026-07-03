/**
 * Minimal typed fetch client for the WordPress REST API.
 * Base URL comes from VITE_API_BASE; empty string means same-origin,
 * which is what the Nginx production setup and the Vite dev proxy expect.
 */
const BASE = import.meta.env.VITE_API_BASE || ''

export class ApiError extends Error {
  status: number
  body?: unknown

  constructor(status: number, message: string, body?: unknown) {
    super(message)
    this.status = status
    this.body = body
  }
}

let token: string | null = localStorage.getItem('dtc_token')

export function setToken(value: string | null) {
  token = value
  if (value) localStorage.setItem('dtc_token', value)
  else localStorage.removeItem('dtc_token')
}

export function getToken() {
  return token
}

interface RequestOptions {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'
  body?: unknown
  params?: Record<string, string | number | boolean | undefined>
  auth?: boolean
}

export async function api<T>(path: string, opts: RequestOptions = {}): Promise<T> {
  const url = new URL(BASE + path, window.location.origin)
  for (const [k, v] of Object.entries(opts.params ?? {})) {
    if (v !== undefined) url.searchParams.set(k, String(v))
  }

  const headers: Record<string, string> = { 'Content-Type': 'application/json' }
  if (opts.auth !== false && token) headers.Authorization = `Bearer ${token}`

  const res = await fetch(url.toString(), {
    method: opts.method ?? 'GET',
    headers,
    body: opts.body !== undefined ? JSON.stringify(opts.body) : undefined,
  })

  const isJson = res.headers.get('content-type')?.includes('json')
  const body = isJson ? await res.json() : await res.text()

  if (!res.ok) {
    if (res.status === 401) setToken(null)
    const message =
      (isJson && (body as { message?: string }).message) || `Request failed (${res.status})`
    throw new ApiError(res.status, message, body)
  }
  return body as T
}
