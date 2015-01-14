/**
 * Created by Kamil on 2015-01-04.
 */
function getPoint() {
    /*xml = xmls.pop();*/
    var points = [];
    var max_speed = 0;
    var max_altitude = 0;
    var count_altitude = 0;
    var dif_altitude = 0;
    var last_altitude = 0;
    var alt = [];
    var distance = 0;
    var lat2 = 0;
    var lon2 = 0;
    var help = 0;
    var speed = 0;
    var distance2 = 0;
    var counter = 0;
    var how_many_points = $(xml).find("Position").length;
    var co_ile_przesiac = Math.round(how_many_points/500);
    if(co_ile_przesiac==0)
        co_ile_przesiac=1;
    console.log(co_ile_przesiac);
    $(xml).find("Trackpoint").each(function () {
        var lat = $(this).find("LatitudeDegrees").text();
        var lon = $(this).find("LongitudeDegrees").text();
        var altitude = parseInt($(this).find("AltitudeMeters").text());
        dif_altitude=altitude-last_altitude;
        console.log(dif_altitude);
        if(altitude !=0 && last_altitude!=0 && dif_altitude>0)
            count_altitude = count_altitude + dif_altitude;
        last_altitude = altitude;
        if(altitude>max_altitude)
            max_altitude = altitude;
        if(help == 0){
            lat2 = lat;
            lon2 = lon;
        }

        distance2 = getDistanceFromLatLonInKm(lat2, lon2, lat, lon);
        if(help == 0)
            distance2 = 0.00001;
        help = 1;
        if(distance2>0.03)
            console.log("nieporzadany punkt");
        else
            speed = distance2/0.000277;
            if(speed>max_speed)
                max_speed = speed;
        distance = distance + getDistanceFromLatLonInKm(lat2, lon2, lat, lon);
        if(counter%co_ile_przesiac == 0)
            points.push([distance, speed]);
        alt.push([distance, altitude]);
        counter = counter + 1;
        lat2 = lat;
        lon2 = lon;
    });
    $("#max_speed").html(max_speed.toFixed(1)+" km/h");
    $("#altitude").html(count_altitude.toFixed(0)+" m");
    $("#max_altitude").html(max_altitude.toFixed(0)+" m");
    draw(points,alt);
}
function speedFormatter(v, axis) {
    return v.toFixed(axis.tickDecimals) + " km/h";
}
function distanceFormatter(v, axis) {
    return v.toFixed(axis.tickDecimals) + " km";
}
function altitudeFormatter(v, axis) {
    return v.toFixed(axis.tickDecimals) + " m";
}
function draw(d1,d2) {

    plot = $.plot("#placeholder", [
        { data: d1, label: "Prędkość = -0.00",color: "rgba(255, 0, 0, 0.8)" },
        { data: d2, label: "Wysokość = -0.00", yaxis: 2, color: "#e6e6e6",
            lines: {fill:true,zero:false/*, fillColor:"rgba(255, 0, 0, 0.8)"*/} }
    ], {
        series: {
            lines: { show: true }
        },
        crosshair: {
            mode: "x"
        },
        grid: {
            /*backgroundColor: { colors: [ "#fff", "#eee" ] },*/
            hoverable: true,
            autoHighlight: false,
            borderWidth: {
                top: 0,
                right: 1,
                bottom: 1,
                left: 1
            }
        },
        xaxis: {
            tickLength: 0,
            tickFormatter: distanceFormatter
        },
        yaxes: [{
            // align if we are to the right

            position: "right",
            tickFormatter: speedFormatter
        },
            {
                position: "left",
                tickFormatter: altitudeFormatter,
                tickColor: "#e6e6e6"
            }
        ]
    });
    var ticks = $(".tickLabel").slice(9,14);
    var ticks2 = $(".tickLabel").slice(14,21);
    ticks.each(function(i) { $(this).css("color", "rgba(255, 0, 0, 0.8)"); });
    ticks2.each(function(i) { $(this).css("color", "#adadad"); });
    var legends = $("#placeholder .legendLabel");

    legends.each(function () {
        // fix the widths so they don't jump around
        $(this).css('width', $(this).width());
    });

    var updateLegendTimeout = null;
    var latestPosition = null;

    function updateLegend() {

        updateLegendTimeout = null;

        var pos = latestPosition;

        var axes = plot.getAxes();
        if (pos.x < axes.xaxis.min || pos.x > axes.xaxis.max ||
            pos.y < axes.yaxis.min || pos.y > axes.yaxis.max) {
            return;
        }

        var i, j, dataset = plot.getData();
        for (i = 0; i < dataset.length; ++i) {

            var series = dataset[i];

            // Find the nearest points, x-wise

            for (j = 0; j < series.data.length; ++j) {
                if (series.data[j][0] > pos.x) {
                    break;
                }
            }

            // Now Interpolate

            var y,
                p1 = series.data[j - 1],
                p2 = series.data[j];

            if (p1 == null) {
                y = p2[1];
            } else if (p2 == null) {
                y = p1[1];
            } else {
                y = p1[1] + (p2[1] - p1[1]) * (pos.x - p1[0]) / (p2[0] - p1[0]);
            }
            console.log(y);
            legends.eq(i).text(series.label.replace(/=.*/, "= " + y.toFixed(1)));
        }
    }

    $("#placeholder").bind("plothover",  function (event, pos, item) {
        latestPosition = pos;
        if (!updateLegendTimeout) {
            updateLegendTimeout = setTimeout(updateLegend, 50);
        }
    });

    $("#footer").prepend("Flot " + $.plot.version + " &ndash; ");
};
function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
    var R = 6371; // Radius of the earth in km
    var dLat = deg2rad(lat2-lat1);  // deg2rad below
    var dLon = deg2rad(lon2-lon1);
    var a =
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
            Math.sin(dLon/2) * Math.sin(dLon/2)
        ;
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    var d = R * c; // Distance in km
    return d;
}

function deg2rad(deg) {
    return deg * (Math.PI/180)
}