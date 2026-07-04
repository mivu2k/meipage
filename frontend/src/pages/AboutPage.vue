<script setup lang="ts">
import { ref, computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { getPage } from '@/api/wp'
import { useSettingsStore } from '@/stores/settings'

const store = useSettingsStore()
const company = computed(() => store.settings?.company)

// Optional extra content authored in WP (page slug "about") renders at the end.
const { data: wpPage } = useQuery({
  queryKey: ['page', 'about'],
  queryFn: () => getPage('about'),
  retry: false,
})

const tabs = [
  {
    key: 'purpose',
    label: 'Purpose',
    title: 'Purpose of Establishment',
    text: 'The basic purpose was to carry out research and development for production of high-tech telecommunication equipment for the local military requirements. Acquisition of high-tech telecommunication systems from developed countries to bridge the technological divide was also an option MICRO successfully availed and integrated into its umbrella from time to time. Research, development and manufacturing of telecommunication equipment and systems. Development of human resources and provision of job opportunities to scientists, researchers, engineers and skilled manpower. Provision of in-country technical support and after-sales services by maintaining adequate stock of spares and trained resources.',
  },
  {
    key: 'mission',
    label: 'Mission',
    title: 'Our Mission',
    text: 'To become the leading solution provider with in-house capabilities as developer, manufacturer and system integrator of high-tech electronics & telecommunications equipment and systems for military and commercial deployments in Pakistan and abroad.',
  },
  {
    key: 'objective',
    label: 'Objective',
    title: 'Our Objectives',
    text: 'Develop electronic equipment and systems with local and foreign support to meet or exceed the requirements of customers. Carry out manufacturing, assembly, testing and integration of equipment to provide quality products and systems. Enhance marketing and sales activities to continuously improve revenue generation. Provide adequate technical support and services for the satisfaction of our customers. Provide appropriate administrative and organizational support to the operational elements of MEI.',
  },
  {
    key: 'history',
    label: 'History',
    title: 'Historic Overview',
    text: 'Prior to 1981, ELMAC — now MICRO — was engaged in semiconductor manufacturing and packaging operations, the first electronics manufacturing company in Pakistan. In 1981, the Ministry of Defense acquired and leased the facility as MEI (Pvt) Limited to support the production of military radios, functioning as a public-private partnership. In 1991 the company was sold to the current owner with 100% ownership. Today it serves the military and government sector by providing radio systems, alongside ventures with multinational manufacturers for local defense and strategic requirements.',
  },
]
const activeTab = ref('purpose')
const current = computed(() => tabs.find((t) => t.key === activeTab.value)!)

const pillars = [
  {
    num: '01',
    tag: 'Communication',
    title: 'Leveraging communication to protect communities',
    text: 'MEI develops and supplies turnkey, integrated communication solutions combining TETRA, DMR, Simulcast and the latest generation of wireless broadband radio — multi-technology network solutions together with air traffic control and GSM-R radio communication systems.',
  },
  {
    num: '02',
    tag: 'Technology & Innovation',
    title: 'Great solutions, built in-house',
    text: 'Our R&D facilities have unique capabilities for developing advanced communications solutions, benefiting from over 40 years of experience. An expert team creates leading-edge technologies in professional, military and secure communications.',
  },
  {
    num: '03',
    tag: 'Surveillance',
    title: 'Security & surveillance expertise',
    text: 'Security Threat Assessment, System Design, High-Level Asset Protection, Military Base Surveillance, Land Border Protection, Maritime Boundary Surveillance, City Surveillance and Total Security Management.',
  },
]

const capabilities = [
  'Experience and capacity of handling large-scale projects with sustenance',
  'Proven ability to develop successful indigenous solutions',
  'Collaborative R&D with local and foreign OEMs/suppliers',
  'Highly competitive pricing versus multi-nationals offering similar solutions',
  'Excellent liaison with armed/paramilitary forces and civil departments',
  'Capability to undertake projects requiring Transfer of Technology (ToT)',
  'Purpose-built infrastructure suited for long-term mass production',
  'Ability to stock adequate spares',
  'Ability to deal in LC/FE and import foreign supplies using own resources',
  'Trusted bridge between other countries and Pakistan for technology acquisition',
  'Professional demonstrations, field trials and training events, inland and abroad',
  'Full electrical and environmental test equipment; SDR waveform development',
]

const clients = [
  'Pak Army', 'Pakistan Air Force', 'Pakistan Navy', 'Pak Rangers', 'PAC Kamra',
  'FWO', 'Frontier Corps', 'Police', 'Airport Security Force', 'Oil & Gas Industry',
  'Rescue 1122', 'NHA & Motorway Police', 'United Nations', 'Airline Industry', 'DHA', 'Private Enterprises',
]

const stats = [
  { value: '40+', label: 'Years of Experience' },
  { value: '1st', label: 'Electronics Manufacturer in Pakistan' },
  { value: '16+', label: 'Major Client Organizations' },
  { value: '100%', label: 'In-Country Support' },
]
</script>

<template>
  <div>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-surface py-24 text-white">
      <div class="absolute inset-0 opacity-20" aria-hidden="true">
        <div class="about-grid h-full w-full" />
      </div>
      <div class="absolute -top-32 -right-32 h-96 w-96 rounded-full bg-accent/10 blur-3xl" aria-hidden="true" />
      <div class="container-page relative z-10">
        <p class="mb-3 text-sm font-semibold tracking-[0.3em] text-accent uppercase">About {{ company?.name || 'MEI' }}</p>
        <h1 class="max-w-3xl text-4xl leading-tight font-bold sm:text-5xl">
          Four decades of mission-critical electronics & communications
        </h1>
        <p class="mt-5 max-w-2xl text-lg text-slate-300">
          {{ company?.name || 'Micro Electronics International (Pvt) Ltd' }} — developer, manufacturer and
          system integrator of high-tech electronics and telecommunication systems for military and
          commercial deployments in Pakistan and abroad.
        </p>
      </div>
    </section>

    <!-- Stats -->
    <section class="border-b border-slate-200 bg-white">
      <div class="container-page grid grid-cols-2 gap-8 py-12 lg:grid-cols-4">
        <div v-for="s in stats" :key="s.label" class="text-center">
          <div class="text-4xl font-bold text-primary">{{ s.value }}</div>
          <div class="mt-1 text-sm text-slate-500">{{ s.label }}</div>
        </div>
      </div>
    </section>

    <!-- Mission / Vision tabs -->
    <section class="py-20">
      <div class="container-page max-w-4xl">
        <div class="flex flex-wrap justify-center gap-2" role="tablist" aria-label="Company profile">
          <button
            v-for="t in tabs"
            :key="t.key"
            role="tab"
            :aria-selected="activeTab === t.key"
            class="rounded-full px-6 py-2.5 text-sm font-semibold transition"
            :class="activeTab === t.key ? 'bg-primary text-white shadow-lg' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            @click="activeTab = t.key"
          >
            {{ t.label }}
          </button>
        </div>
        <Transition name="fade" mode="out-in">
          <div :key="current.key" class="mt-10 text-center">
            <h2 class="text-2xl font-bold text-primary">{{ current.title }}</h2>
            <div class="mx-auto mt-3 h-1 w-16 rounded bg-accent" />
            <p class="mt-6 text-lg leading-relaxed text-slate-600">{{ current.text }}</p>
          </div>
        </Transition>
      </div>
    </section>

    <!-- Pillars -->
    <section class="bg-slate-50 py-20">
      <div class="container-page">
        <div class="grid gap-8 md:grid-cols-3">
          <div
            v-for="p in pillars"
            :key="p.num"
            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-8 transition duration-300 hover:-translate-y-1 hover:border-accent hover:shadow-xl"
          >
            <div class="absolute -top-4 -right-2 text-8xl font-black text-slate-100 transition group-hover:text-accent/10" aria-hidden="true">
              {{ p.num }}
            </div>
            <div class="relative">
              <p class="text-xs font-semibold tracking-widest text-accent uppercase">{{ p.tag }}</p>
              <h3 class="mt-3 text-xl font-bold text-primary">{{ p.title }}</h3>
              <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ p.text }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- What we do -->
    <section class="relative overflow-hidden bg-surface py-20 text-white">
      <div class="absolute inset-0 opacity-10" aria-hidden="true"><div class="about-grid h-full w-full" /></div>
      <div class="container-page relative z-10">
        <div class="mb-12 text-center">
          <h2 class="text-3xl font-bold">What We Do</h2>
          <div class="mx-auto mt-3 h-1 w-16 rounded bg-accent" />
        </div>
        <ul class="grid gap-x-12 gap-y-4 md:grid-cols-2">
          <li v-for="c in capabilities" :key="c" class="flex items-start gap-3 text-sm text-slate-300">
            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent/20 text-xs text-accent">✓</span>
            {{ c }}
          </li>
        </ul>
      </div>
    </section>

    <!-- Clients marquee -->
    <section class="border-b border-slate-200 bg-white py-16">
      <div class="container-page mb-8 text-center">
        <h2 class="section-title">Our Clients</h2>
        <p class="mt-2 text-sm text-slate-500">
          We've been privileged to collaborate with a long list of customers, national and international.
        </p>
      </div>
      <div class="marquee" aria-label="Client organizations">
        <div class="marquee-track">
          <span v-for="c in [...clients, ...clients]" :key="c + Math.random()" class="marquee-item">{{ c }}</span>
        </div>
      </div>
    </section>

    <!-- Optional extra content from WordPress -->
    <section v-if="wpPage?.content" class="py-16">
      <div class="container-page max-w-4xl">
        <div class="prose-wp text-slate-700" v-html="wpPage.content" />
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-primary py-16 text-white">
      <div class="container-page flex flex-col items-center text-center">
        <h2 class="text-3xl font-bold">Partner with {{ company?.name || 'MEI' }}</h2>
        <p class="mt-3 max-w-2xl text-slate-300">
          From tactical communication to surveillance and security — talk to our engineers about your requirements.
        </p>
        <div class="mt-7 flex gap-4">
          <RouterLink to="/contact" class="btn-primary bg-accent text-primary hover:bg-accent/80">Contact Us</RouterLink>
          <RouterLink to="/solutions" class="btn-outline border-white/40 text-white hover:border-accent hover:text-accent">Our Solutions</RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.about-grid {
  background-image:
    linear-gradient(rgba(45, 212, 191, 0.15) 1px, transparent 1px),
    linear-gradient(90deg, rgba(45, 212, 191, 0.15) 1px, transparent 1px);
  background-size: 48px 48px;
  mask-image: radial-gradient(ellipse at center, black 30%, transparent 75%);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

.marquee {
  overflow: hidden;
  white-space: nowrap;
}
.marquee-track {
  display: inline-block;
  animation: marquee 40s linear infinite;
}
.marquee:hover .marquee-track {
  animation-play-state: paused;
}
.marquee-item {
  display: inline-block;
  margin: 0 2.5rem;
  font-weight: 700;
  font-size: 1.05rem;
  color: var(--dtc-primary, #0b1f3a);
  opacity: 0.75;
}
@keyframes marquee {
  from { transform: translateX(0); }
  to { transform: translateX(-50%); }
}
@media (prefers-reduced-motion: reduce) {
  .marquee-track { animation: none; }
}
</style>
