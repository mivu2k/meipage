import { api } from './client'
import type {
  AuthUser,
  Branch,
  Brand,
  Career,
  DownloadFile,
  Paged,
  Post,
  Product,
  RepairCase,
  SiteSettings,
  Solution,
  Term,
  Ticket,
} from './types'

const V1 = '/wp-json/dtc/v1'

// ---- Public content ----
export const getSettings = () => api<SiteSettings>(`${V1}/settings`)

export const getProducts = (params: {
  page?: number
  per_page?: number
  brand?: string
  category?: string
  search?: string
}) => api<Paged<Product>>(`${V1}/products`, { params })

export const getProduct = (slug: string) => api<Product>(`${V1}/products/${slug}`)
export const getBrands = () => api<Brand[]>(`${V1}/brands`)
export const getBrand = (slug: string) => api<Brand>(`${V1}/brands/${slug}`)
export const getProductCategories = () => api<Term[]>(`${V1}/product-categories`)
export const getSolutions = () => api<Solution[]>(`${V1}/solutions`)
export const getSolution = (slug: string) => api<Solution>(`${V1}/solutions/${slug}`)
export const getPosts = (params: { page?: number; per_page?: number; category?: string }) =>
  api<Paged<Post>>(`${V1}/posts`, { params })
export const getPost = (slug: string) => api<Post>(`${V1}/posts/${slug}`)
export const getBranches = () => api<Branch[]>(`${V1}/branches`)
export const getCareers = () => api<Career[]>(`${V1}/careers`)
export const getPage = (slug: string) => api<Post>(`${V1}/pages/${slug}`)

// ---- Auth ----
export const login = (username: string, password: string) =>
  api<{ token: string; user: AuthUser }>(`${V1}/auth/login`, {
    method: 'POST',
    body: { username, password },
    auth: false,
  })
export const getMe = () => api<AuthUser>(`${V1}/auth/me`)
export const register = (payload: {
  name: string
  email: string
  password: string
  organization: string
  country: string
}) => api<{ message: string }>(`${V1}/auth/register`, { method: 'POST', body: payload, auth: false })

// ---- Forms ----
export const submitInquiry = (payload: {
  name: string
  email: string
  organization: string
  country: string
  message: string
  products: { id: number; title: string; quantity: number }[]
}) => api<{ id: number }>(`${V1}/inquiries`, { method: 'POST', body: payload })

export const submitContact = (payload: {
  name: string
  email: string
  subject: string
  message: string
  department?: string
}) => api<{ id: number }>(`${V1}/contact`, { method: 'POST', body: payload })

export const submitConsultation = (payload: {
  name: string
  email: string
  organization: string
  solution: string
  message: string
}) => api<{ id: number }>(`${V1}/consultations`, { method: 'POST', body: payload })

export const submitApplication = (payload: {
  name: string
  email: string
  phone: string
  position: number
  cover_letter: string
  resume_media_id?: number
}) => api<{ id: number }>(`${V1}/applications`, { method: 'POST', body: payload })

// ---- Customer portal (authenticated) ----
export const getMyDownloads = () => api<DownloadFile[]>(`${V1}/portal/downloads`)
export const getMyProducts = () => api<Product[]>(`${V1}/portal/products`)
export const getMyTickets = () => api<Ticket[]>(`${V1}/portal/tickets`)
export const getTicket = (id: number) => api<Ticket>(`${V1}/portal/tickets/${id}`)
export const createTicket = (payload: { subject: string; type: Ticket['type']; message: string }) =>
  api<Ticket>(`${V1}/portal/tickets`, { method: 'POST', body: payload })
export const replyTicket = (id: number, message: string) =>
  api<Ticket>(`${V1}/portal/tickets/${id}/reply`, { method: 'POST', body: { message } })
export const getMyRepairs = () => api<RepairCase[]>(`${V1}/portal/repairs`)
export const getMyInquiries = () =>
  api<{ id: number; date: string; status: string; products: string[] }[]>(`${V1}/portal/inquiries`)
