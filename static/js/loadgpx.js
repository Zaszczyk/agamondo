
function GPXParser(xmlDoc, map)
{
    this.xmlDoc = xmlDoc;
    this.map = map;
    this.trackcolour = "#ff00ff"; // red
    this.trackwidth = 5;
    this.mintrackpointdelta = 0.0001
}

// Set the colour of the track line segements.
GPXParser.prototype.SetTrackColour = function(colour)
{
    this.trackcolour = colour;
}

// Set the width of the track line segements
GPXParser.prototype.SetTrackWidth = function(width)
{
    this.trackwidth = width;
}

// Set the minimum distance between trackpoints.
// Used to cull unneeded trackpoints from map.
GPXParser.prototype.SetMinTrackPointDelta = function(delta)
{
    this.mintrackpointdelta = delta;
}

GPXParser.prototype.TranslateName = function(name)
{
    if (name == "wpt")
    {
        return "Waypoint";
    }
    else if (name == "trkpt")
    {
        return "Track Point";
    }
}


GPXParser.prototype.CreateMarker = function(point)
{
    var lon = parseFloat(point.getAttribute("lon"));
    var lat = parseFloat(point.getAttribute("lat"));
    var html = "";

    if (point.getElementsByTagName("html").length > 0)
    {
        for (i=0; i<point.getElementsByTagName("html").item(0).childNodes.length; i++)
        {
            html += point.getElementsByTagName("html").item(0).childNodes[i].nodeValue;
        }
    }
    else
    {
        // Create the html if it does not exist in the point.
        html = "<b>" + this.TranslateName(point.nodeName) + "</b><br>";
        var attributes = point.attributes;
        var attrlen = attributes.length;
        for (i=0; i<attrlen; i++)
        {
            html += attributes.item(i).name + " = " + attributes.item(i).nodeValue + "<br>";
        }

        if (point.hasChildNodes)
        {
            var children = point.childNodes;
            var childrenlen = children.length;
            for (i=0; i<childrenlen; i++)
            {
                // Ignore empty nodes
                if (children[i].nodeType != 1) continue;
                if (children[i].firstChild == null) continue;
                html += children[i].nodeName + " = " + children[i].firstChild.nodeValue + "<br>";
            }
        }
    }

    var marker = new GMarker(new GLatLng(lat,lon));
    GEvent.addListener(marker, "click",
        function()
        {
            marker.openInfoWindowHtml(html);
        }
    );

    this.map.addOverlay(marker);


    // All methods that add items to the map return the bounding box of what they added.
    //var latlng = new GLatLng(lat,lon);
    //return new GLatLngBounds(latlng,latlng);
}


GPXParser.prototype.AddTrackSegmentToMap = function(trackSegment, colour, width)
{
    //var latlngbounds = new GLatLngBounds();

    var trackpoints = trackSegment.getElementsByTagName("trkpt");
    if (trackpoints.length == 0)
    {
        return; //latlngbounds;
    }

    var pointarray = [];

    // process first point
    var lastlon = parseFloat(trackpoints[0].getAttribute("lon"));
    var lastlat = parseFloat(trackpoints[0].getAttribute("lat"));
    var latlng = new GLatLng(lastlat,lastlon);
    pointarray.push(latlng);
    //latlngbounds.extend(latlng);

    // Create a marker at the begining of each track segment
    //this.CreateMarker(trackpoints[0]);

    for (var i=1; i < trackpoints.length; i++)
    {
        var lon = parseFloat(trackpoints[i].getAttribute("lon"));
        var lat = parseFloat(trackpoints[i].getAttribute("lat"));

        // Verify that this is far enough away from the last point to be used.
        var latdiff = lat - lastlat;
        var londiff = lon - lastlon;
        if ( Math.sqrt(latdiff*latdiff + londiff*londiff) > this.mintrackpointdelta )
        {
            lastlon = lon;
            lastlat = lat;
            latlng = new GLatLng(lat,lon);
            pointarray.push(latlng);
            //latlngbounds.extend(latlng);
        }

    }

    var polyline = new GPolyline(pointarray, colour, width);

    this.map.addOverlay(polyline);

    // All methods that add items to the map return the bounding box of what they added.
    //return latlngbounds;
}

GPXParser.prototype.AddTrackToMap = function(track, colour, width)
{
    var segments = track.getElementsByTagName("trkseg");
    //var latlngbounds = new GLatLngBounds();
    for (var i=0; i < segments.length; i++)
    {
        var segmentlatlngbounds = this.AddTrackSegmentToMap(segments[i], colour, width);
        //this.AddTrackSegmentToMap(segments[i], colour, width);
        //latlngbounds.extend(segmentlatlngbounds.getSouthWest());
        //latlngbounds.extend(segmentlatlngbounds.getNorthEast());
    }

    // All methods that add items to the map return the bounding box of what they added.
    //return latlngbounds;
}

GPXParser.prototype.CenterAndZoom = function (trackSegment, maptype)
{

    var pointlist = new Array("trkpt", "wpt");
    var minlat = 0;
    var maxlat = 0;
    var minlon = 0;
    var maxlon = 0;

    for (var pointtype=0; pointtype < pointlist.length; pointtype++)
    {

        // Center the map and zoom on the given segment.
        var trackpoints = trackSegment.getElementsByTagName(pointlist[pointtype]);

        // If the min and max are uninitialized then initialize them.
        if ( (trackpoints.length > 0) && (minlat == maxlat) && (minlat == 0) )
        {
            minlat = parseFloat(trackpoints[0].getAttribute("lat"));
            maxlat = parseFloat(trackpoints[0].getAttribute("lat"));
            minlon = parseFloat(trackpoints[0].getAttribute("lon"));
            maxlon = parseFloat(trackpoints[0].getAttribute("lon"));
        }

        for (var i=0; i < trackpoints.length; i++)
        {
            var lon = parseFloat(trackpoints[i].getAttribute("lon"));
            var lat = parseFloat(trackpoints[i].getAttribute("lat"));

            if (lon < minlon) minlon = lon;
            if (lon > maxlon) maxlon = lon;
            if (lat < minlat) minlat = lat;
            if (lat > maxlat) maxlat = lat;
        }
    }

    if ( (minlat == maxlat) && (minlat == 0) )
    {
        this.map.setCenter(new GLatLng(49.327667, -122.942333), 14);
        return;
    }

    // Center around the middle of the points
    var centerlon = (maxlon + minlon) / 2;
    var centerlat = (maxlat + minlat) / 2;

    var bounds = new GLatLngBounds(new GLatLng(minlat, minlon), new GLatLng(maxlat, maxlon));

    this.map.setCenter(new GLatLng(centerlat, centerlon), this.map.getBoundsZoomLevel(bounds), maptype);
}

GPXParser.prototype.CenterAndZoomToLatLngBounds = function (latlngboundsarray)
{
    var boundingbox = new GLatLngBounds();
    for (var i=0; i<latlngboundsarray.length; i++)
    {
        if (!latlngboundsarray[i].isEmpty())
        {
            boundingbox.extend(latlngboundsarray[i].getSouthWest());
            boundingbox.extend(latlngboundsarray[i].getNorthEast());
        }
    }

    var centerlat = (boundingbox.getNorthEast().lat() + boundingbox.getSouthWest().lat()) / 2;
    var centerlng = (boundingbox.getNorthEast().lng() + boundingbox.getSouthWest().lng()) / 2;
    this.map.setCenter(new GLatLng(centerlat, centerlng), this.map.getBoundsZoomLevel(boundingbox));
}


GPXParser.prototype.AddTrackpointsToMap = function ()
{
    var tracks = this.xmlDoc.documentElement.getElementsByTagName("trk");
    //var latlngbounds = new GLatLngBounds();

    for (var i=0; i < tracks.length; i++)
    {
        this.AddTrackToMap(tracks[i], this.trackcolour, this.trackwidth);
        //var tracklatlngbounds = this.AddTrackToMap(tracks[i], this.trackcolour, this.trackwidth);
        //latlngbounds.extend(tracklatlngbounds.getSouthWest());
        //latlngbounds.extend(tracklatlngbounds.getNorthEast());
    }

    // All methods that add items to the map return the bounding box of what they added.
    //return latlngbounds;
}

GPXParser.prototype.AddWaypointsToMap = function ()
{
    var waypoints = this.xmlDoc.documentElement.getElementsByTagName("wpt");
    //var latlngbounds = new GLatLngBounds();

    for (var i=0; i < waypoints.length; i++)
    {
        this.CreateMarker(waypoints[i]);
        //var waypointlatlngbounds = this.CreateMarker(waypoints[i]);
        //latlngbounds.extend(waypointlatlngbounds.getSouthWest());
        //latlngbounds.extend(waypointlatlngbounds.getNorthEast());
    }

    // All methods that add items to the map return the bounding box of what they added.
    //return latlngbounds;
}

/**
 * Created by Kamil on 2014-11-04.
 */
