/** Shared types mirroring the WordPress REST API responses (dtc/v1 + wp/v2). */

export interface MediaItem {
  id: number
  url: string
  alt: string
  width?: number
  height?: number
}

export interface Term {
  id: number
  name: string
  slug: string
  parent: number
  count: number
}

export interface Product {
  id: number
  slug: string
  title: string
  excerpt: string
  content: string
  image: MediaItem | null
  gallery: MediaItem[]
  videos: string[]
  features: string[]
  specifications: { label: string; value: string }[]
  specifications_html: string
  applications: string[]
  brand: Term | null
  categories: Term[]
  accessories: number[]
  compatible: number[]
  related: number[]
  downloads: DownloadFile[]
  certifications: string[]
}

export interface Brand {
  id: number
  slug: string
  title: string
  content: string
  logo: MediaItem | null
  website: string
  country: string
}

export interface Solution {
  id: number
  slug: string
  title: string
  excerpt: string
  content: string
  image: MediaItem | null
  architecture: string
  components: string[]
  benefits: string[]
  related_products: number[]
  case_studies: { title: string; summary: string }[]
  downloads: DownloadFile[]
}

export interface Post {
  id: number
  slug: string
  title: string
  excerpt: string
  content: string
  date: string
  author: string
  image: MediaItem | null
  categories: Term[]
}

export interface DownloadFile {
  id: number
  title: string
  type: 'driver' | 'firmware' | 'manual' | 'software' | 'brochure' | 'config' | 'bulletin' | 'training' | 'datasheet'
  version?: string
  size?: string
  url: string | null // null when the current user lacks permission
  restricted: boolean
}

export interface Branch {
  id: number
  title: string
  address: string
  lat: number
  lng: number
  phone: string
  email: string
  working_hours: string
  departments: { name: string; phone: string; email: string }[]
}

export interface Career {
  id: number
  slug: string
  title: string
  content: string
  location: string
  type: string // full-time | internship | ...
  department: string
}

export interface Ticket {
  id: number
  subject: string
  type: 'technical' | 'warranty' | 'repair' | 'documentation' | 'training'
  status: 'open' | 'in_progress' | 'waiting' | 'resolved' | 'closed'
  created: string
  updated: string
  messages: { author: string; body: string; date: string }[]
}

export type RepairStage =
  | 'received'
  | 'inspection'
  | 'repair'
  | 'testing'
  | 'quality_check'
  | 'ready'
  | 'shipped'

export interface RepairCase {
  id: number
  rma: string
  product: string
  serial: string
  stage: RepairStage
  history: { stage: RepairStage; date: string; note: string }[]
}

export interface SiteSettings {
  company: {
    name: string
    slogan: string
    logo: string
    logo_dark: string
    favicon: string
  }
  contact: {
    phone: string
    email: string
    support_email: string
    whatsapp: string
    working_hours: string
  }
  social: Partial<Record<'linkedin' | 'facebook' | 'youtube' | 'x' | 'telegram' | 'signal', string>>
  theme: { primary: string; secondary: string; accent: string }
  homepage: {
    hero_title: string
    hero_subtitle: string
    hero_cta_label: string
    hero_cta_link: string
    hero_animation: 'network' | 'radar' | 'satellite'
    statistics: { label: string; value: string }[]
    cta: { title: string; text: string; button_label: string; button_link: string }
  }
  menus: Record<string, { label: string; url: string; children?: { label: string; url: string }[] }[]>
  footer: { about: string; copyright: string }
}

export interface AuthUser {
  id: number
  name: string
  email: string
  roles: string[]
}

export interface Paged<T> {
  items: T[]
  total: number
  pages: number
}
