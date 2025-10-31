<template>
  <div class="map-container">
    <div id="map" ref="mapContainer" style="height: 400px; width: 100%;"></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix for default markers in Vite
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

const props = defineProps({
  initialCenter: {
    type: Array,
    default: () => [23.79, 90.41] // Dhaka, Bangladesh
  },
  initialZoom: {
    type: Number,
    default: 13
  },
  markers: {
    type: Array,
    default: () => [
      {
        id: 1,
        position: [23.79, 90.41],
        popup: 'Mirpur, Dhaka'
      }
    ]
  }
});

const mapContainer = ref(null);
let map = null;

onMounted(() => {
  // Initialize the map
  map = L.map(mapContainer.value).setView(props.initialCenter, props.initialZoom);

  // Add tile layer
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  // Add markers
  props.markers.forEach(marker => {
    L.marker(marker.position)
      .addTo(map)
      .bindPopup(marker.popup);
  });
});
</script>

<style scoped>
.map-container {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
</style>