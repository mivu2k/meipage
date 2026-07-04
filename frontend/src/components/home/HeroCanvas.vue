<script setup lang="ts">
/**
 * Three.js hero background: a wireframe comms globe orbited by a network of
 * nodes and data links, with radar sweep and mouse-parallax camera — inspired
 * by modern agency sites (subtle, not game-like).
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
  scene.fog = new THREE.FogExp2(0x070c16, 0.055)
  const camera = new THREE.PerspectiveCamera(55, el.clientWidth / el.clientHeight, 0.1, 100)
  camera.position.set(0, 1.8, 9)

  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'low-power' })
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.setSize(el.clientWidth, el.clientHeight)
  el.appendChild(renderer.domElement)

  const accent = new THREE.Color(
    getComputedStyle(document.documentElement).getPropertyValue('--dtc-accent').trim() || '#2dd4bf',
  )

  const group = new THREE.Group()
  scene.add(group)

  // --- Centerpiece: wireframe comms globe, offset right of the headline ---
  const globe = new THREE.Group()
  globe.position.set(3.4, 0.6, 0)
  group.add(globe)

  const sphereGeo = new THREE.IcosahedronGeometry(2.1, 2)
  const wire = new THREE.Mesh(
    sphereGeo,
    new THREE.MeshBasicMaterial({ color: accent, wireframe: true, transparent: true, opacity: 0.14 }),
  )
  globe.add(wire)

  // Glowing vertices of the globe
  const globePoints = new THREE.Points(
    sphereGeo,
    new THREE.PointsMaterial({ color: accent, size: 0.05, transparent: true, opacity: 0.8 }),
  )
  globe.add(globePoints)

  // Orbit rings
  for (const [rx, opacity] of [[Math.PI / 2.2, 0.35], [Math.PI / 3, 0.18]] as const) {
    const ring = new THREE.Mesh(
      new THREE.TorusGeometry(2.7, 0.008, 8, 90),
      new THREE.MeshBasicMaterial({ color: accent, transparent: true, opacity }),
    )
    ring.rotation.x = rx
    globe.add(ring)
  }

  // --- Network nodes spread across the scene ---
  const NODE_COUNT = 80
  const nodes: THREE.Vector3[] = []
  for (let i = 0; i < NODE_COUNT; i++) {
    const r = 4 + Math.random() * 4
    const theta = Math.random() * Math.PI * 2
    const y = (Math.random() - 0.35) * 3.2
    nodes.push(new THREE.Vector3(Math.cos(theta) * r, y, Math.sin(theta) * r * 0.6))
  }
  const nodeGeo = new THREE.BufferGeometry().setFromPoints(nodes)
  const nodeMat = new THREE.PointsMaterial({ color: accent, size: 0.06, transparent: true, opacity: 0.9 })
  group.add(new THREE.Points(nodeGeo, nodeMat))

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
  group.add(new THREE.LineSegments(linkGeo, new THREE.LineBasicMaterial({ color: accent, transparent: true, opacity: 0.13 })))

  // --- Ground grid ---
  const grid = new THREE.GridHelper(30, 40, 0x1e3a5f, 0x101d33)
  grid.position.y = -2.4
  scene.add(grid)

  // --- Radar sweep ---
  let sweep: THREE.Mesh | null = null
  if (props.variant !== 'satellite') {
    sweep = new THREE.Mesh(
      new THREE.CircleGeometry(6.5, 48, 0, Math.PI / 5),
      new THREE.MeshBasicMaterial({ color: accent, transparent: true, opacity: 0.06, side: THREE.DoubleSide }),
    )
    sweep.rotation.x = -Math.PI / 2
    sweep.position.y = -2.35
    scene.add(sweep)
  }

  // --- Mouse parallax (lerped for that smooth agency feel) ---
  const pointer = { x: 0, y: 0 }
  const onPointer = (e: PointerEvent) => {
    pointer.x = (e.clientX / window.innerWidth) * 2 - 1
    pointer.y = (e.clientY / window.innerHeight) * 2 - 1
  }
  window.addEventListener('pointermove', onPointer, { passive: true })

  const clock = new THREE.Clock()
  const animate = () => {
    frame = requestAnimationFrame(animate)
    if (!visible || !renderer) return
    const t = clock.getElapsedTime()

    group.rotation.y = t * 0.04
    globe.rotation.y = t * 0.15
    globe.rotation.x = Math.sin(t * 0.2) * 0.1
    nodeMat.opacity = 0.7 + Math.sin(t * 1.4) * 0.2
    if (sweep) sweep.rotation.z = -t * 0.4

    // Ease the camera toward the pointer target
    camera.position.x += (pointer.x * 0.7 - camera.position.x) * 0.04
    camera.position.y += (1.8 - pointer.y * 0.5 - camera.position.y) * 0.04
    camera.lookAt(0.8, 0.2, 0)

    renderer.render(scene, camera)
  }

  if (reduced) {
    camera.lookAt(0.8, 0.2, 0)
    renderer.render(scene, camera)
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
    window.removeEventListener('pointermove', onPointer)
    observer?.disconnect()
    renderer?.dispose()
    nodeGeo.dispose()
    linkGeo.dispose()
    sphereGeo.dispose()
    renderer = null
  })
})
</script>

<template>
  <div ref="container" class="absolute inset-0" aria-hidden="true" />
</template>
