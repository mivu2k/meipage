<script setup lang="ts">
/**
 * Lightweight Three.js hero background: a slowly rotating network of nodes
 * with pulsing data links and an optional radar sweep — subtle, not game-like.
 * Respects prefers-reduced-motion and pauses when off-screen.
 */
import { onMounted, onBeforeUnmount, ref } from 'vue'
import * as THREE from 'three'

const props = defineProps<{ variant?: 'network' | 'radar' | 'satellite' }>()

const container = ref<HTMLDivElement | null>(null)
let renderer: THREE.WebGLRenderer | null = null
let frame = 0
let observer: IntersectionObserver | null = null
let visible = true

onMounted(() => {
  const el = container.value
  if (!el) return
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches

  const scene = new THREE.Scene()
  scene.fog = new THREE.FogExp2(0x0a0f1a, 0.06)
  const camera = new THREE.PerspectiveCamera(55, el.clientWidth / el.clientHeight, 0.1, 100)
  camera.position.set(0, 2.2, 9)
  camera.lookAt(0, 0, 0)

  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'low-power' })
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.setSize(el.clientWidth, el.clientHeight)
  el.appendChild(renderer.domElement)

  const accent = new THREE.Color(
    getComputedStyle(document.documentElement).getPropertyValue('--dtc-accent').trim() || '#2dd4bf',
  )

  const group = new THREE.Group()
  scene.add(group)

  // --- Network nodes on a hemisphere-ish spread ---
  const NODE_COUNT = 90
  const nodes: THREE.Vector3[] = []
  for (let i = 0; i < NODE_COUNT; i++) {
    const r = 4 + Math.random() * 3.5
    const theta = Math.random() * Math.PI * 2
    const y = (Math.random() - 0.35) * 3
    nodes.push(new THREE.Vector3(Math.cos(theta) * r, y, Math.sin(theta) * r * 0.6))
  }
  const nodeGeo = new THREE.BufferGeometry().setFromPoints(nodes)
  const nodeMat = new THREE.PointsMaterial({ color: accent, size: 0.07, transparent: true, opacity: 0.9 })
  group.add(new THREE.Points(nodeGeo, nodeMat))

  // --- Links between nearby nodes ---
  const linkPositions: number[] = []
  for (let i = 0; i < NODE_COUNT; i++) {
    for (let j = i + 1; j < NODE_COUNT; j++) {
      if (nodes[i].distanceTo(nodes[j]) < 2.1) {
        linkPositions.push(...nodes[i].toArray(), ...nodes[j].toArray())
      }
    }
  }
  const linkGeo = new THREE.BufferGeometry()
  linkGeo.setAttribute('position', new THREE.Float32BufferAttribute(linkPositions, 3))
  const linkMat = new THREE.LineBasicMaterial({ color: accent, transparent: true, opacity: 0.16 })
  group.add(new THREE.LineSegments(linkGeo, linkMat))

  // --- Ground grid for the command-center feel ---
  const grid = new THREE.GridHelper(30, 40, 0x1e3a5f, 0x14283f)
  grid.position.y = -2.4
  scene.add(grid)

  // --- Radar sweep (a fading sector that rotates) ---
  let sweep: THREE.Mesh | null = null
  if (props.variant !== 'satellite') {
    const sweepGeo = new THREE.CircleGeometry(6.5, 48, 0, Math.PI / 5)
    const sweepMat = new THREE.MeshBasicMaterial({
      color: accent,
      transparent: true,
      opacity: 0.07,
      side: THREE.DoubleSide,
    })
    sweep = new THREE.Mesh(sweepGeo, sweepMat)
    sweep.rotation.x = -Math.PI / 2
    sweep.position.y = -2.35
    scene.add(sweep)
  }

  const clock = new THREE.Clock()
  const animate = () => {
    frame = requestAnimationFrame(animate)
    if (!visible || !renderer) return
    const t = clock.getElapsedTime()
    group.rotation.y = t * 0.05
    nodeMat.opacity = 0.7 + Math.sin(t * 1.4) * 0.2
    if (sweep) sweep.rotation.z = -t * 0.4
    renderer.render(scene, camera)
  }

  if (reduced) {
    renderer.render(scene, camera) // single static frame
  } else {
    animate()
  }

  const onResize = () => {
    if (!renderer || !el) return
    camera.aspect = el.clientWidth / el.clientHeight
    camera.updateProjectionMatrix()
    renderer.setSize(el.clientWidth, el.clientHeight)
  }
  window.addEventListener('resize', onResize)

  observer = new IntersectionObserver(([entry]) => {
    visible = entry.isIntersecting
  })
  observer.observe(el)

  onBeforeUnmount(() => {
    cancelAnimationFrame(frame)
    window.removeEventListener('resize', onResize)
    observer?.disconnect()
    renderer?.dispose()
    nodeGeo.dispose()
    linkGeo.dispose()
    renderer = null
  })
})
</script>

<template>
  <div ref="container" class="absolute inset-0" aria-hidden="true" />
</template>
