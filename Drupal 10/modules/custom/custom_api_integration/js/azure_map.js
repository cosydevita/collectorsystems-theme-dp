let map;
let locations = drupalSettings.azure_map.locations;
let subscription_key = drupalSettings.azure_map.subscription_key;
let module_path = drupalSettings.azure_map.module_path;

function initializeMap() {
  map = new atlas.Map("myMap", {
    center: [0, 0],
    zoom: 3,
    authOptions: {
      authType: "subscriptionKey",
      subscriptionKey: subscription_key,
    },
  });

  map.events.add("ready", function () {
    let minLongitude = Number.POSITIVE_INFINITY;
    let maxLongitude = Number.NEGATIVE_INFINITY;
    let minLatitude = Number.POSITIVE_INFINITY;
    let maxLatitude = Number.NEGATIVE_INFINITY;

    let groupedLocations = {};

    // Group locations by AddressName
    locations.forEach(function (location) {
      let addressName = location.AddressName || "Unknown";
      if (!groupedLocations[addressName]) {
        groupedLocations[addressName] = [];
      }
      groupedLocations[addressName].push(location);
    });

    // Iterate over grouped locations
    Object.keys(groupedLocations).forEach(function (addressName) {
      let group = groupedLocations[addressName];
      group_size = group.length;

      // Calculate average position for the marker
      let avgLongitude =
        group.reduce((sum, loc) => sum + dmsToDecimal(loc.longitude), 0) /
        group.length;
      let avgLatitude =
        group.reduce((sum, loc) => sum + dmsToDecimal(loc.latitude), 0) /
        group.length;

      minLongitude = Math.min(minLongitude, avgLongitude);
      maxLongitude = Math.max(maxLongitude, avgLongitude);
      minLatitude = Math.min(minLatitude, avgLatitude);
      maxLatitude = Math.max(maxLatitude, avgLatitude);

      let marker = new atlas.HtmlMarker({
        position: [avgLongitude, avgLatitude],
      });
      map.markers.add(marker);

      let popup_html = '<div class="location-popup-content">';
      if (group_size > 2) {
        popup_html +=
          '<div class="location-list" style="max-height: 400px; overflow-y: auto;">';
      } else {
        popup_html += '<div class="location-list" >';
      }

      group.forEach(function (location) {
        let main_image_path = location.main_image_path;
        let main_image_attachment = location.main_image_attachment;
        let html_object_main_image = "";

        if (main_image_path) {
          html_object_main_image =
            '<img width="200" src="' + main_image_path + '">';
        } else if (main_image_attachment) {
          let object_img = "data:image/jpeg;base64," + main_image_attachment;
          html_object_main_image = '<img width="200" src="' + object_img + '">';
        } else {
          html_object_main_image =
            '<img width="200" src="' +
            module_path +
            "/images/noimage300.png" +
            '">';
        }

        popup_html += '<div class="location-item">';
        popup_html +=
          '<div class="col-left">' + html_object_main_image + "</div>";

        let data_selected_fields = location.data_selected_fields;
        let html_data_selected_fields = "";
        if (data_selected_fields) {
          Object.keys(data_selected_fields).forEach(function (key) {
            let value = data_selected_fields[key];
            if (value) {
              html_data_selected_fields += "<div>" + value + "</div>";
            }
          });
        }

        popup_html += '<div class="col-right">';
        popup_html += html_data_selected_fields;
        popup_html +=
        '<div class="location-name"><img class="location-marker-icon" width="20" src="' +
        module_path +
        '/images/map-marker.svg">' +
        location.AddressName +
        "</div>";
        popup_html += '<a href="'+location.object_detail_url+'" class="btn-learn-more">Learn More <img class="arrow-icon" src="' + module_path+'/images/right-arrow.svg'+'" /> </a>';
        popup_html += "</div></div>";
      });

      popup_html += "</div></div>";

      let popup = new atlas.Popup({
        content: popup_html,
        position: [avgLongitude, avgLatitude],
        anchor: "top",
      });

      map.events.add("click", marker, function () {
        map.popups.clear();
        popup.open(map);
      });
    });

    // Calculate the center of the bounding box
    let center = [
      (minLongitude + maxLongitude) / 2,
      (minLatitude + maxLatitude) / 2,
    ];

    // Set the map's view to fit the bounding box
    const total_objects = Object.keys(groupedLocations).length;
    if(total_objects == 1){
      map.setCamera({
        center: center,
        zoom: 15,
      });

    }else{
      map.setCamera({
        center: center,
        bounds: [minLongitude, minLatitude, maxLongitude, maxLatitude],
        padding: 50, // Optional: padding around the bounding box
      });
    }

  });
}

function destroyMap() {
  return new Promise(function (resolve, reject) {
    if (map) {
      map.dispose();
      map = null;
      resolve(); // Resolve the promise to indicate completion
    }
  });
}

document.addEventListener("DOMContentLoaded", initializeMap);

function dmsToDecimal(dms) {
  const parts = dms.match(/(\d+)[°]\s(\d+)[′]\s([\d.]+)[″]\s([NSEW])/);

  if (!parts) {
    console.error("Invalid DMS format: " + dms);
    return null;
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

jQuery(document).ready(function ($) {
  $(".custom-tabs-wrapper button.map").click(function () {
    $(".custom-tabs-wrapper button").removeClass("active");
    $(this).addClass("active");
    $("#gallery-block").hide();
    $("#azure-map-block").show();
    destroyMap().then(function () {
      initializeMap();
    });
  });

  $(".custom-tabs-wrapper button.gallery").click(function () {
    $(".custom-tabs-wrapper button").removeClass("active");
    $(this).addClass("active");

    $("#gallery-block").show();
    $("#azure-map-block").hide();
  });
});

//For Group level objects searching page, after the search re-initialize the map with the new searched data
jQuery(document).ajaxComplete(function (event, xhr, options) {
  var url = options.url;
  var responseJSON = xhr.responseJSON;
  if (url === "/v1/group-level-objects-searching-page") {
    groupLevelSearchHtml = responseJSON.groupLevelSearchHtml;
    locations = responseJSON.locations;
    destroyMap().then(function () {
      initializeMap();
    });
  }
});

document
  .querySelector("#azure-map-block #locateMeButton")
  .addEventListener("click", locateMe);

function locateMe() {
  if (navigator.geolocation) {
    // Only secure origins are allowed, it won't work in localhost
    navigator.geolocation.getCurrentPosition(
      function (position) {
        let userLongitude = position.coords.longitude;
        let userLatitude = position.coords.latitude;
        let nearbyLocations = [];

        locations.forEach(function (location) {
          let longitude = dmsToDecimal(location.longitude);
          let latitude = dmsToDecimal(location.latitude);

          // Calculate distance between user location and each marker (simple approximation)
          let distance = Math.sqrt(
            Math.pow(userLongitude - longitude, 2) +
              Math.pow(userLatitude - latitude, 2)
          );

          if (distance < 0.1) {
            // Assuming a threshold of 0.1 degrees for "nearby"
            nearbyLocations.push(location);
          }
        });

        let marker = new atlas.HtmlMarker({
          position: [userLongitude, userLatitude],
        });
        map.markers.add(marker);

        // Center map on user's location
        map.setCamera({
          center: [userLongitude, userLatitude],
          zoom: 10, // Adjust zoom level as needed, min is 0 max is 22
        });

        let popup_html = "<div>Your Location </div>";
        popup_html =
          '<div class="location-popup-content">' + popup_html + "</div>";

        let popup = new atlas.Popup({
          content: popup_html,
          position: [userLongitude, userLatitude],
          anchor: "top", // Adjust the anchor to 'top's
        });

        map.events.add("click", marker, function () {
          map.popups.clear();
          popup.open(map);
        });
      },
      function (error) {
        alert("Sorry! we are unable to get your location.");
        console.error("Error getting geolocation: " + error.message);
      }
    );
  } else {
    alert("Geolocation is not supported by this browser.");
  }
}
