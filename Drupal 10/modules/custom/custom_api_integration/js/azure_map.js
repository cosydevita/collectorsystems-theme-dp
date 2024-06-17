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
          let longitude = dmsToDecimal(location.longitude);
          let latitude = dmsToDecimal(location.latitude);

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


function dmsToDecimal(dms) {
  const parts = dms.match(/(\d+)[°]\s(\d+)[′]\s([\d.]+)[″]\s([NSEW])/);
  if (!parts) {
      throw new Error("Invalid DMS format");
  }

  const degrees = parseFloat(parts[1]);
  const minutes = parseFloat(parts[2]);
  const seconds = parseFloat(parts[3]);
  const direction = parts[4];

  let decimal = degrees + minutes / 60 + seconds / 3600;

  // Negate for south and west coordinates
  if (direction === "S" || direction === "W") {
      decimal *= -1;
  }

  return decimal;
}
