function initialize(nazwaMapy,nazwaPliku) {
    var map = new google.maps.Map(document.getElementById(nazwaMapy), {
        mapTypeId: google.maps.MapTypeId.TERRAIN
    });

    $.ajax({
        type: "GET",
        url: nazwaPliku, // change to your file
        dataType: "xml",
        success: function(xml) {
            var points = [];
            var bounds = new google.maps.LatLngBounds ();
            $(xml).find("Position").each(function() {
                var lat=$(this).find("LatitudeDegrees").text();
                var lon = $(this).find("LongitudeDegrees").text();
                var p = new google.maps.LatLng(lat, lon);
                points.push(p);
                bounds.extend(p);
            });

            var poly = new google.maps.Polyline({
                path: points,
                strokeColor: "#5D66DS",
                strokeOpacity: .7,
                strokeWeight: 4
            });

            poly.setMap(map);
            map.fitBounds(bounds);
        }
    });
}
