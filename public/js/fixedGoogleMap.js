import { getGoogleMap } from './googleMap.js';

export function initializeMap() {
  const mapElementId = 'map';

  const mapOptions = {
    disableDefaultUI: true,
    gestureHandling: "none",
    zoomControl: true
  };

  const markerOptions = {
    title: "Your place"
  };

  getGoogleMap(mapElementId, mapOptions, markerOptions);
}
