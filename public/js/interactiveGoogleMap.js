import { getGoogleMap } from './googleMap.js';

export function initializeMap() {
  const mapElementId = 'map';

  const mapOptions = {
    disableDefaultUI: false,
    gestureHandling: "cooperative",
    zoomControl: true,
    streetViewControl: false,
    mapTypeControl: false,
  };

  const markerOptions = {
    title: "Your place",
    draggable: true,
  };

  const callback = (marker, map) => {
    const latitudeInput = document.getElementById('conference_latitude');
    const longitudeInput = document.getElementById('conference_longitude');

    marker.addListener('dragend', function (event) {
      const newLat = event.latLng.lat();
      const newLng = event.latLng.lng();

      latitudeInput.value = newLat.toFixed(6);
      longitudeInput.value = newLng.toFixed(6);
    });

    map.addListener('click', function (event) {
      const newLat = event.latLng.lat();
      const newLng = event.latLng.lng();

      marker.setPosition({ lat: newLat, lng: newLng });

      latitudeInput.value = newLat.toFixed(6);
      longitudeInput.value = newLng.toFixed(6);
    });
  };

  getGoogleMap(mapElementId, mapOptions, markerOptions, callback);
}
