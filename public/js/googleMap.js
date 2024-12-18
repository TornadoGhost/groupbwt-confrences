export function getGoogleMap(mapElementId, options = {}, markerOptions = {}, callback) {
    function getCountryName(code, locale = 'en') {
        return new Intl.DisplayNames([locale], {type: 'region'}).of(code);
    }

    document.addEventListener("DOMContentLoaded", function () {
        const countryElement = document.getElementById('country-name');
        if (countryElement) {
            const countryCode = countryElement.dataset.country;
            if (countryCode) {
                const countryName = getCountryName(countryCode);
                countryElement.textContent = countryName;
            }
        }

        const mapElement = document.getElementById(mapElementId);
        const latitude = parseFloat(mapElement.dataset.lat);
        const longitude = parseFloat(mapElement.dataset.lng);

        const map = new google.maps.Map(mapElement, {
            center: {lat: latitude, lng: longitude},
            zoom: 3,
            disableDefaultUI: true,
            gestureHandling: "none",
            zoomControl: false,
            ...options
        });

        const marker = new google.maps.Marker({
            position: {lat: latitude, lng: longitude},
            map: map,
            title: "Your place",
            ...markerOptions
        });

        if (callback) {
            callback(marker, map);
        }
    });
}
