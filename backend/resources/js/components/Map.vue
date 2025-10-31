<template>
  <div id="map" style="height: 500px; width: 100%;"></div>
</template>

<script setup>
import { onMounted } from 'vue';
import L from 'leaflet';

// Leaflet এর ডিফল্ট মার্কার আইকন ঠিক করার জন্য (Vite/Vue 3 issue)
import 'leaflet/dist/leaflet.css';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

onMounted(() => {
  const map = L.map('map').setView([23.79, 90.41], 13); // Dhaka, Bangladesh

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  // Optional: Add a marker
  L.marker([23.79, 90.41]).addTo(map)
    .bindPopup('Mirpur, Dhaka')
    .openPopup();
});
</script>

<style scoped>
/* প্রয়োজন হলে কাস্টম স্টাইল */
</style>