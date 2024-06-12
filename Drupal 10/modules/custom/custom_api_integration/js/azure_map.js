let map;
let locations = drupalSettings.azure_map.locations;
let subscription_key = drupalSettings.azure_map.subscription_key;


function initializeMap() {
  map = new atlas.Map('myMap', {
      center: [0, 0],
      zoom: 3,
      authOptions: {
          authType: 'subscriptionKey',
          subscriptionKey: subscription_key
      }
  });

  map.events.add('ready', function () {
      let minLongitude = Number.POSITIVE_INFINITY;
      let maxLongitude = Number.NEGATIVE_INFINITY;
      let minLatitude = Number.POSITIVE_INFINITY;
      let maxLatitude = Number.NEGATIVE_INFINITY;

      locations.forEach(function (location) {
          let longitude = location.longitude;
          let latitude = location.latitude;

          minLongitude = Math.min(minLongitude, longitude);
          maxLongitude = Math.max(maxLongitude, longitude);
          minLatitude = Math.min(minLatitude, latitude);
          maxLatitude = Math.max(maxLatitude, latitude);

          let marker = new atlas.HtmlMarker({
              position: [longitude, latitude]
          });
          map.markers.add(marker);

          let popup_html = '<img width="200" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSXOhv5uppl7ovkfhSqDRmtjtnPREHUQiQPeQ&s"><div style="padding:10px;">' + location.AddressName + '</div>';

          let popup = new atlas.Popup({
              content: popup_html,
              position: [longitude, latitude]
          });

          map.events.add('click', marker, function () {
              map.popups.clear();
              popup.open(map);
          });
      });

      // Calculate the center of the bounding box
      let center = [(minLongitude + maxLongitude) / 2, (minLatitude + maxLatitude) / 2];

      // Set the map's view to fit the bounding box
      map.setCamera({
          center: center,
          bounds: [minLongitude, minLatitude, maxLongitude, maxLatitude],
          padding: 50 // Optional: padding around the bounding box
      });
  });
}


document.addEventListener("DOMContentLoaded", initializeMap);
