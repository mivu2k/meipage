import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'

export interface InquiryItem {
  id: number
  title: string
  image: string | null
  quantity: number
}

const STORAGE_KEY = 'dtc_inquiry_cart'

/** The "inquiry cart": products the visitor collects to submit as one quote request. */
export const useInquiryStore = defineStore('inquiry', () => {
  const items = ref<InquiryItem[]>(JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]'))

  watch(items, (v) => localStorage.setItem(STORAGE_KEY, JSON.stringify(v)), { deep: true })

  const count = computed(() => items.value.length)
  const has = (id: number) => items.value.some((i) => i.id === id)

  function add(item: Omit<InquiryItem, 'quantity'>) {
    if (!has(item.id)) items.value.push({ ...item, quantity: 1 })
  }
  function remove(id: number) {
    items.value = items.value.filter((i) => i.id !== id)
  }
  function setQuantity(id: number, quantity: number) {
    const item = items.value.find((i) => i.id === id)
    if (item) item.quantity = Math.max(1, quantity)
  }
  function clear() {
    items.value = []
  }

  return { items, count, has, add, remove, setQuantity, clear }
})
