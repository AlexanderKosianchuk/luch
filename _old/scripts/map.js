$(document).ready(function(){
	var flightId = $("input#flightId").attr('value'),
		startFrame = $("input#startFrame").attr('value'),
		endFrame = $("input#endFrame").attr('value'),
		Coord = new Coordinate(flightId, startFrame, endFrame);
	
	var coordPoints = Coord.ReceiveCoordinates();

	var flightPlanCoordinates = [];
	for(var i = 0; i < coordPoints.length; i++)
	{
		flightPlanCoordinates.push(new google.maps.LatLng(coordPoints[i][0], coordPoints[i][1]));	  
	}
	  
	function initialize() {
		  var mapOptions = {
		    zoom: 15,
		    center: flightPlanCoordinates[flightPlanCoordinates.length - 1],
		    mapTypeId: google.maps.MapTypeId.SATELLITE
		  };
		  
	  var map = new google.maps.Map(document.getElementById('map_canvas'),
		      mapOptions);
	  
	  var flightPath = new google.maps.Polyline({
	    path: flightPlanCoordinates,
	    geodesic: true,
	    strokeColor: '#FF0000',
	    strokeOpacity: 1.0,
	    strokeWeight: 2
	  });

	  flightPath.setMap(map);
	}

	google.maps.event.addDomListener(window, 'load', initialize);

});







