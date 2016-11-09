var MODEL_URL = 'http://www.barnabu.co.uk/geapi/flightsim/hawk.kmz';

$(document).ready(function(){
	
	/*var flightId = $("input#flightId").attr('value'),
		startFrame = $("input#startFrame").attr('value'),
		endFrame = $("input#endFrame").attr('value'),
		stepLenght = $("input#stepLenght").attr('value');*/
	
	var $play = $("#play").button({
		text : false,
		icons : {
			primary : "ui-icon-play"
		}
	}).click(function() {
		var options;
		if ($(this).text() === "play") {
			options = {
				label : "pause",
				icons : {
					primary : "ui-icon-pause"
				}
			};
		} else {
			options = {
				label : "play",
				icons : {
					primary : "ui-icon-play"
				}
			};
		}
		$(this).button("option", options);
		
		
	});
	
	var $stop = $("#stop").button({
		text : false,
		icons : {
			primary : "ui-icon-stop"
		}
	}).click(function() {
		$play.button("option", {
			label : "play",
			icons : {
				primary : "ui-icon-play"
			}
		});
		$slider.slider("value", 0);
	});
	
	var $slider = $("#slider").slider({ 
		min: 0,
		max: endFrame,
		step: 1,
		value: 0
	});
	
	$slider.on( "slide", function( event, ui ) {
		$play.button("option", {
			label : "play",
			icons : {
				primary : "ui-icon-play"
			}
		});
	});
	
	$("div#map3d").css({
		height: $(window).height() - 47,
		width: $(window).width()
	});	
	
});

var flightId = null,
	ge = null,
	aircraft = null,
	Coord = null,
	coordPoints = null;

google.load("earth", "1", {'other_params': 'sensor=false' });
google.load('maps', '2.x', {'other_params': 'sensor=false' });
google.setOnLoadCallback(InitGoogleEarthContainer);

function InitGoogleEarthContainer() {
	ge = new google.maps.Geocoder();
    google.earth.createInstance("map3d", InitGoogleEarthCallback, FailureGoogleEarthCallback);
}

function InitGoogleEarthCallback(object) {
	ge = object;
	ge.getWindow().setVisibility(true);
	ge.getOptions().setMouseNavigationEnabled(true);
	ge.getLayerRoot().enableLayerById(ge.LAYER_BUILDINGS, true);
	ge.getOptions().setFlyToSpeed(ge.SPEED_TELEPORT);
	  
	$(document).ready(function(){
		flightId = $("input#flightId").val();
		Coord = new Coordinate(flightId, -1, -1);
		coordPoints = Coord.ReceiveCoordinates();
	});
	  
	var lookAt = ge.getView().copyAsLookAt(ge.ALTITUDE_RELATIVE_TO_GROUND);
	lookAt.setLatitude(coordPoints[coordPoints.length-1][0]);
	lookAt.setLongitude(coordPoints[coordPoints.length-1][1]);
	// Height as i understand
	lookAt.setRange(1000.0);
	ge.getView().setAbstractView(lookAt);
	  
	//Получим текущий вид.
	var camera = ge.getView().copyAsCamera(ge.ALTITUDE_RELATIVE_TO_GROUND);
	
	// Добавим 30 градусов к текущему значению наклона
	// и 45 градусов к значению поворота.
	camera.setTilt(camera.getTilt() + 0.0);
	camera.setRoll(camera.getRoll() + 0.0);
	  
	//camera.setLatitude(48.423123);
	//camera.setLongitude(10.922494);
	//camera.setAltitude(469.715686);
	camera.setHeading(90.000000);
	//camera.setTilt(90.000000);
	//camera.setRoll(0.000000);
	
	// Обновим вид в Google Планете Земля.
	ge.getView().setAbstractView(camera);
	  
	//Create a 3D model, initialize it from a Collada file, and place it
	//in the world.

	var placemark = ge.createPlacemark('');
		placemark.setName('model');
	var model = ge.createModel('');
		ge.getFeatures().appendChild(placemark);
	var loc = ge.createLocation('');
		loc.setLatitude(coordPoints[coordPoints.length-1][0]);
		loc.setLongitude(coordPoints[coordPoints.length-1][1]);
		model.setLocation(loc);
	var link = ge.createLink('');
	
	//A textured model created in Sketchup and exported as Collada.
	//link.setHref('https://91.218.212.137/models/splotchy_box.dae');
	link.setHref('http://www.luch.sys/models/splotchy_2.dae');
	//link.setHref('http://earth-api-samples.googlecode.com/svn/trunk/examples/' +
    //   'static/splotchy_box.dae');
	model.setLink(link);
	
	placemark.setGeometry(model);
	
}


function FailureGoogleEarthCallback(err) {
}

//milktruck.js  -- Copyright Google 2007

//Code for Monster Milktruck demo, using Earth Plugin.

//window.truck = null;
//
////Pull the Milktruck model from 3D Warehouse.
//var MODEL_URL = 'http://www.barnabu.co.uk/geapi/flightsim/hawk.kmz';
//
//var TICK_MS = 66;
//
//
//var STEER_ROLL = -1.0;
//var ROLL_SPRING = 0.25;
//var ROLL_DAMP = -0.16;
//
//function Truck() {
//var me = this;
//
//me.doTick = true;

//// We do all our motion relative to a local coordinate frame that is
//// anchored not too far from us.  In this frame, the x axis points
//// east, the y axis points north, and the z axis points straight up
//// towards the sky.
////
//// We periodically change the anchor point of this frame and
//// recompute the local coordinates.
//me.localAnchorLla = [0, 0, 0];
//me.localAnchorCartesian = V3.latLonAltToCartesian(me.localAnchorLla);
//me.localFrame = M33.identity();
//
//// Position, in local cartesian coords.
//me.pos = [0, 0, 0];
//
//// Velocity, in local cartesian coords.
//me.vel = [0, 0, 0];
//
//// Orientation matrix, transforming model-relative coords into local
//// coords.
//me.modelFrame = M33.identity();
//
//me.roll = 0;
//me.rollSpeed = 0;
//
//me.idleTimer = 0;
//me.fastTimer = 0;
//me.popupTimer = 0;
//
//ge.getOptions().setFlyToSpeed(100);  // don't filter camera motion
//
//window.google.earth.fetchKml(ge, MODEL_URL,
//                            function(obj) { me.finishInit(obj); });
//}

//Truck.prototype.finishInit = function(kml) {
//var me = this;
//
//// The model zip file is actually a kmz, containing a KmlFolder with
//// a camera KmlPlacemark (we don't care) and a model KmlPlacemark
//// (our milktruck).
//me.placemark = kml.getFeatures().getChildNodes().item(1);
//me.model = me.placemark.getGeometry();
//me.orientation = me.model.getOrientation();
//me.location = me.model.getLocation();
//
//me.model.setAltitudeMode(ge.ALTITUDE_ABSOLUTE);
//me.orientation.setHeading(90);
//me.model.setOrientation(me.orientation);
//
//ge.getFeatures().appendChild(me.placemark);
//
//me.balloon = ge.createHtmlStringBalloon('');
//me.balloon.setFeature(me.placemark);
//me.balloon.setMaxWidth(200);
//
//me.teleportTo(37.423501, -122.086744, 90);  // Looking at the 'Plex
//
//me.lastMillis = (new Date()).getTime();
//
//var href = window.location.href;
//var pagePath = href.substring(0, href.lastIndexOf('/')) + '/';
//
//google.earth.addEventListener(ge, "frameend", function() { me.tick(); });
//
//me.cameraCut();
//
//ge.getWindow().blur();
//
//// If the user clicks on the Earth window, try to restore keyboard
//// focus back to the page.
//google.earth.addEventListener(ge.getWindow(), "mouseup", function(event) {
//   ge.getWindow().blur();
// });
//}
//
//leftButtonDown = false;
//rightButtonDown = false;
//gasButtonDown = false;
//reverseButtonDown = false;
//
//function keyDown(event) {
//if (!event) {
// event = window.event;
//}
//if (event.keyCode == 37) {  // Left.
// leftButtonDown = true;
// event.returnValue = false;
//} else if (event.keyCode == 39) {  // Right.
// rightButtonDown = true;
// event.returnValue = false;
//} else if (event.keyCode == 38) {  // Up.
// gasButtonDown = true;
// event.returnValue = false;
//} else if (event.keyCode == 40) {  // Down.
// reverseButtonDown = true;
// event.returnValue = false;
//} else {
// return true;
//}
//return false;
//}
//
//function keyUp(event) {
//if (!event) {
// event = window.event;
//}
//if (event.keyCode == 37) {  // Left.
// leftButtonDown = false;
// event.returnValue = false;
//} else if (event.keyCode == 39) {  // Right.
// rightButtonDown = false;
// event.returnValue = false;
//} else if (event.keyCode == 38) {  // Up.
// gasButtonDown = false;
// event.returnValue = false;
//} else if (event.keyCode == 40) {  // Down.
// reverseButtonDown = false;
// event.returnValue = false;
//}
//return false;
//}
//
//function clamp(val, min, max) {
//if (val < min) {
// return min;
//} else if (val > max) {
// return max;
//}
//return val;
//}
//
//Truck.prototype.tick = function() {
//var me = this;
//
//var now = (new Date()).getTime();
//// dt is the delta-time since last tick, in seconds
//var dt = (now - me.lastMillis) / 1000.0;
//if (dt > 0.25) {
// dt = 0.25;
//}
//me.lastMillis = now;
//
//var c0 = 1;
//var c1 = 0;
//
//var gpos = V3.add(me.localAnchorCartesian,
//                 M33.transform(me.localFrame, me.pos));
//var lla = V3.cartesianToLatLonAlt(gpos);
//
//if (V3.length([me.pos[0], me.pos[1], 0]) > 100) {
// // Re-anchor our local coordinate frame whenever we've strayed a
// // bit away from it.  This is necessary because the earth is not
// // flat!
// me.adjustAnchor();
//}
//
//var dir = me.modelFrame[1];
//var up = me.modelFrame[2];
//
//var absSpeed = V3.length(me.vel);
//
//var groundAlt = ge.getGlobe().getGroundAltitude(lla[0], lla[1]);
//var airborne = false;
//var steerAngle = 0;
//
//// Steering.
//if (leftButtonDown || rightButtonDown) {
// var TURN_SPEED_MIN = 40.0;  // radians/sec
// var TURN_SPEED_MAX = 60.0;  // radians/sec
//
// var turnSpeed;
//
// // Degrade turning at higher speeds.
// //
// //           angular turn speed vs. vehicle speed
// //    |     -------
// //    |    /       \-------
// //    |   /                 \-------
// //    |--/                           \---------------
// //    |
// //    +-----+-------------------------+-------------- speed
// //    0    SPEED_MAX_TURN           SPEED_MIN_TURN
// var SPEED_MAX_TURN = 200.0;
// var SPEED_MIN_TURN = 450.0;
// if (absSpeed < SPEED_MAX_TURN) {
//   turnSpeed = TURN_SPEED_MIN + (TURN_SPEED_MAX - TURN_SPEED_MIN)
//                * (SPEED_MAX_TURN - absSpeed) / SPEED_MAX_TURN;
//   turnSpeed *= (absSpeed / SPEED_MAX_TURN);  // Less turn as truck slows
// } else {
//   turnSpeed = TURN_SPEED_MAX;
// }
// if (leftButtonDown) {
//   steerAngle = turnSpeed/2 * dt * Math.PI / 180.0;
// }
// if (rightButtonDown) {
//   steerAngle = -turnSpeed/2 * dt * Math.PI / 180.0;
// }
//}
//
//// Turn.
//var newdir = airborne ? dir : V3.rotate(dir, up, steerAngle);
//me.modelFrame = M33.makeOrthonormalFrame(newdir, up);
//dir = me.modelFrame[1];
//up = me.modelFrame[2];
//
//var forwardSpeed = 0;
//
//if (!airborne) {
// // TODO: if we're slipping, transfer some of the slip
// // velocity into forward velocity.
//
// // Damp sideways slip.  Ad-hoc frictiony hack.
// //
// // I'm using a damped exponential filter here, like:
// // val = val * c0 + val_new * (1 - c0)
// //
// // For a variable time step:
// //  c0 = exp(-dt / TIME_CONSTANT)
// var right = me.modelFrame[0];
// var slip = V3.dot(me.vel, right);
// c0 = Math.exp(-dt / 0.5);
// me.vel = V3.sub(me.vel, V3.scale(right, slip * (1 - c0)));
//
// // Apply engine/reverse accelerations.
// var ACCEL = 80.0;
// var DECEL = 80.0;
// var MAX_REVERSE_SPEED = 100.0;
// forwardSpeed = V3.dot(dir, me.vel);
// gasButtonDown = true;
// if (gasButtonDown) {
//   // Accelerate forwards.
//   me.vel = V3.add(me.vel, V3.scale(dir, ACCEL * dt));
// } else if (reverseButtonDown) {
//   if (forwardSpeed > -MAX_REVERSE_SPEED)
//     me.vel = V3.add(me.vel, V3.scale(dir, -DECEL * dt));
// }
//}
//
//// Air drag.
////
//// Fd = 1/2 * rho * v^2 * Cd * A.
//// rho ~= 1.2 (typical conditions)
//// Cd * A = 3 m^2 ("drag area")
////
//// I'm simplifying to:
////
//// accel due to drag = 1/Mass * Fd
//// with Milktruck mass ~= 2000 kg
//// so:
//// accel = 0.6 / 2000 * 3 * v^2
//// accel = 0.0009 * v^2
//absSpeed = V3.length(me.vel);
//if (absSpeed > 0.01) {
// var veldir = V3.normalize(me.vel);
// var DRAG_FACTOR = 0.00050;
// var drag = absSpeed * absSpeed * DRAG_FACTOR;
//
// // Some extra constant drag (rolling resistance etc) to make sure
// // we eventually come to a stop.
// var CONSTANT_DRAG = 2.0;
// drag += CONSTANT_DRAG;
//
// if (drag > absSpeed) {
//   drag = absSpeed;
// }
//
// me.vel = V3.sub(me.vel, V3.scale(veldir, drag * dt));
//}
//
//// Gravity
//me.vel[2] -= 9.8 * dt;
//
//// Move.
//var deltaPos = V3.scale(me.vel, dt);
//me.pos = V3.add(me.pos, deltaPos);
//
//gpos = V3.add(me.localAnchorCartesian,
//             M33.transform(me.localFrame, me.pos));
//lla = V3.cartesianToLatLonAlt(gpos);
//
//// Don't go underground.
//groundAlt = ge.getGlobe().getGroundAltitude(lla[0], lla[1]);
//if (me.pos[2] < groundAlt) {
// me.pos[2] = groundAlt;
//}
//
//var normal = estimateGroundNormal(gpos, me.localFrame);
//
//if (airborne) {
// // Cancel velocity into the ground.
// //
// // TODO: would be fun to add a springy suspension here so
// // the truck bobs & bounces a little.
// var speedOutOfGround = V3.dot(normal, me.vel);
// if (speedOutOfGround < 0) {
//   me.vel = V3.add(me.vel, V3.scale(normal, -speedOutOfGround));
// }
//
// // Make our orientation follow the ground.
// c0 = Math.exp(-dt / 0.25);
// c1 = 1 - c0;
// var blendedUp = V3.normalize(V3.add(V3.scale(up, c0),
//                                     V3.scale(normal, c1)));
// me.modelFrame = M33.makeOrthonormalFrame(dir, blendedUp);
//}
//
//// Propagate our state into Earth.
//gpos = V3.add(me.localAnchorCartesian,
//             M33.transform(me.localFrame, me.pos));
//lla = V3.cartesianToLatLonAlt(gpos);
//me.model.getLocation().setLatLngAlt(lla[0], lla[1], lla[2]+25);
//
//var newhtr = M33.localOrientationMatrixToHeadingTiltRoll(me.modelFrame);
//
//// Compute roll according to steering.
//// TODO: this would be even more cool in 3d.
//var absRoll = newhtr[2];
//me.rollSpeed += steerAngle * forwardSpeed * 4* STEER_ROLL;
//// Spring back to center, with damping.
//me.rollSpeed += (ROLL_SPRING * -me.roll + ROLL_DAMP * me.rollSpeed);
//me.roll += me.rollSpeed * dt;
//me.roll = clamp(me.roll, -85, 85);
//absRoll -= me.roll;
//
//me.orientation.set(newhtr[0], newhtr[1], absRoll);
//
//
////me.tickPopups(dt);
//
//me.cameraFollow(dt, gpos, me.localFrame);
//
//// Hack to work around focus bug
//// TODO: fix that bug and remove this hack.
////ge.getWindow().blur();
//};
//
////TODO: would be nice to have globe.getGroundNormal() in the API.
//function estimateGroundNormal(pos, frame) {
//// Take four height samples around the given position, and use it to
//// estimate the ground normal at that position.
////  (North)
////     0
////     *
////  2* + *3
////     *
////     1
//var pos0 = V3.add(pos, frame[0]);
//var pos1 = V3.sub(pos, frame[0]);
//var pos2 = V3.add(pos, frame[1]);
//var pos3 = V3.sub(pos, frame[1]);
//var globe = ge.getGlobe();
//function getAlt(p) {
// var lla = V3.cartesianToLatLonAlt(p);
// return globe.getGroundAltitude(lla[0], lla[1]);
//}
//var dx = getAlt(pos1) - getAlt(pos0);
//var dy = getAlt(pos3) - getAlt(pos2);
//var normal = V3.normalize([dx, dy, 2]);
//return normal;
//}
//
//
//Truck.prototype.scheduleTick = function() {
//var me = this;
//if (me.doTick) {
// setTimeout(function() { me.tick(); }, TICK_MS);
//}
//};
//
////Cut the camera to look at me.
//Truck.prototype.cameraCut = function() {
//var me = this;
//var lo = me.model.getLocation();
//var la = ge.createLookAt('');
//la.set(lo.getLatitude(), lo.getLongitude(),
//      10 /* altitude */,
//      ge.ALTITUDE_RELATIVE_TO_GROUND,
//      fixAngle(180 + me.model.getOrientation().getHeading() + 45),
//      80, /* tilt */
//      25 /* range */         
//      );
//ge.getView().setAbstractView(la);
//};
//
//Truck.prototype.cameraFollow = function(dt, truckPos, localToGlobalFrame) {
//var me = this;
//
//var c0 = Math.exp(-dt / 0.5);
//var c1 = 1 - c0;
//
//var la = ge.getView().copyAsLookAt(ge.ALTITUDE_RELATIVE_TO_GROUND);
//
//var truckHeading = me.model.getOrientation().getHeading();
//var camHeading = la.getHeading();
//
//var deltaHeading = fixAngle(truckHeading - camHeading);
//var heading = camHeading + c1 * deltaHeading;
//heading = fixAngle(heading);
//
//var TRAILING_DISTANCE = 25;
//var headingRadians = heading / 180 * Math.PI;
//
//var CAM_HEIGHT = 10;
//
//var headingDir = V3.rotate(localToGlobalFrame[1], localToGlobalFrame[2],
//                          -headingRadians);
//var camPos = V3.add(truckPos, V3.scale(localToGlobalFrame[2], CAM_HEIGHT));
//camPos = V3.add(camPos, V3.scale(headingDir, -TRAILING_DISTANCE));
//var camLla = V3.cartesianToLatLonAlt(camPos);
//var camLat = camLla[0];
//var camLon = camLla[1];
//var camAlt = camLla[2] - ge.getGlobe().getGroundAltitude(camLat, camLon);
//
//la.set(camLat, camLon, camAlt+22, ge.ALTITUDE_RELATIVE_TO_GROUND, 
//     heading, 80 /*tilt*/, 0 /*range*/);
//ge.getView().setAbstractView(la);
//};
//
////heading is optional.
//Truck.prototype.teleportTo = function(lat, lon, heading) {
//var me = this;
//me.model.getLocation().setLatitude(lat);
//me.model.getLocation().setLongitude(lon);
//me.model.getLocation().setAltitude(ge.getGlobe().getGroundAltitude(lat, lon));
//if (heading == null) {
// heading = 0;
//}
//me.vel = [0, 0, 0];
//
//me.localAnchorLla = [lat, lon, 0];
//me.localAnchorCartesian = V3.latLonAltToCartesian(me.localAnchorLla);
//me.localFrame = M33.makeLocalToGlobalFrame(me.localAnchorLla);
//me.modelFrame = M33.identity();
//me.modelFrame[0] = V3.rotate(me.modelFrame[0], me.modelFrame[2], -heading);
//me.modelFrame[1] = V3.rotate(me.modelFrame[1], me.modelFrame[2], -heading);
//me.pos = [0, 0, ge.getGlobe().getGroundAltitude(lat, lon)];
//
//me.cameraCut();
//};
//
////Move our anchor closer to our current position.  Retain our global
////motion state (position, orientation, velocity).
//Truck.prototype.adjustAnchor = function() {
//var me = this;
//var oldLocalFrame = me.localFrame;
//
//var globalPos = V3.add(me.localAnchorCartesian,
//                      M33.transform(oldLocalFrame, me.pos));
//var newAnchorLla = V3.cartesianToLatLonAlt(globalPos);
//newAnchorLla[2] = 0;  // For convenience, anchor always has 0 altitude.
//
//var newAnchorCartesian = V3.latLonAltToCartesian(newAnchorLla);
//var newLocalFrame = M33.makeLocalToGlobalFrame(newAnchorLla);
//
//var oldFrameToNewFrame = M33.transpose(newLocalFrame);
//oldFrameToNewFrame = M33.multiply(oldFrameToNewFrame, oldLocalFrame);
//
//var newVelocity = M33.transform(oldFrameToNewFrame, me.vel);
//var newModelFrame = M33.multiply(oldFrameToNewFrame, me.modelFrame);
//var newPosition = M33.transformByTranspose(
//   newLocalFrame,
//   V3.sub(globalPos, newAnchorCartesian));
//
//me.localAnchorLla = newAnchorLla;
//me.localAnchorCartesian = newAnchorCartesian;
//me.localFrame = newLocalFrame;
//me.modelFrame = newModelFrame;
//me.pos = newPosition;
//me.vel = newVelocity;
//}
//
////Keep an angle in [-180,180]
//function fixAngle(a) {
//while (a < -180) {
// a += 360;
//}
//while (a > 180) {
// a -= 360;
//}
//return a;
//}
//
////math3d.js -- Copyright 2007 Google
//
////Some Javascript math utilities.
////
////Exports V3 (3-vector utilities), M33 (3x3 matrix utilities)
//
////NOTE: This will be refactored in a more Object
////Oriented style, so don't get attached to this syntax!
//
////3D vector functions.
//V3 = {
//EARTH_RADIUS: 6378100,
//
//dup: function(a) {
// return [a[0], a[1], a[2]];
//},
//
//toString: function(a) {
// return "[" + a[0] + ", " + a[1] + ", " + a[2] + "]";
//},
//
//nearlyEqual: function(a, b, tolerance) {
// if (!tolerance) {
//   tolerance = 1e-6;
// }
// return Math.abs(a[0] - b[0]) <= tolerance
//   && Math.abs(a[1] - b[1]) <= tolerance
//   && Math.abs(a[2] - b[2]) <= tolerance;
//},
//
//cross: function(a, b) {
// return [
//     a[1] * b[2] - a[2] * b[1],
//     a[2] * b[0] - a[0] * b[2],
//     a[0] * b[1] - a[1] * b[0] ];
//},
//
//dot: function(a, b) {
// return a[0] * b[0] + a[1] * b[1] + a[2] * b[2];
//},
//
//add: function(a, b) {
// return [
//     a[0] + b[0],
//     a[1] + b[1],
//     a[2] + b[2]];
//},
//
//sub: function(a, b) {
// return [
//     a[0] - b[0],
//     a[1] - b[1],
//     a[2] - b[2]];
//},
//
//scale: function(a, scale) {
// return [a[0] * scale, a[1] * scale, a[2] * scale];
//},
//
//length: function(a) {
// return Math.sqrt(a[0] * a[0] + a[1] * a[1] + a[2] * a[2]);
//},
//
//normalize: function(a) {
// var len = V3.length(a);
// if (len <= 0) {
//   return [NaN, NaN, NaN];
// }
// return V3.scale(a, 1.0 / len);
//},
//
//bisect: function(a, b) {
// return [(a[0] + b[0]) / 2,
//         (a[1] + b[1]) / 2,
//         (a[2] + b[2]) / 2];
//},
//
//// Returns v rotated counterclockwise about axis by radians.
//// axis should be a unit vector; otherwise you'll get weird results.
//rotate: function(v, axis, radians) {
// var vDotAxis = V3.dot(v, axis);
// var vPerpAxis = V3.sub(v, V3.scale(axis, vDotAxis));
// var vPerpPerpAxis = V3.cross(axis, vPerpAxis);
// var result = V3.add(V3.scale(axis, vDotAxis),
//                     V3.add(V3.scale(vPerpAxis, Math.cos(radians)),
//                            V3.scale(vPerpPerpAxis, Math.sin(radians))));
// return result;
//},
//
//// Takes a set of Euler angles and converts from degrees to radians.
//toRadians: function(v) {
// return [v[0] * Math.PI / 180,
//         v[1] * Math.PI / 180,
//         v[2] * Math.PI / 180];
//},
//
//// Takes a set of Euler angles and converts from radians to degrees.
//toDegrees: function(v) {
// return [v[0] * 180 / Math.PI,
//         v[1] * 180 / Math.PI,
//         v[2] * 180 / Math.PI];
//},
//
//// Input is [lat, lon, alt].  Lat & lon are in degrees, positive up
//// and east.  Alt in meters, relative to Earth's radius.
////
//// Output is meters x,y,z.  x points out of (0,0) (just off West
//// Africa), y points out the North Pole, and z points out of (0,-90)
//// (near Ecuador).
//latLonAltToCartesian: function(vert) {
// var sinTheta = Math.sin(vert[1] * Math.PI / 180);
// var cosTheta = Math.cos(vert[1] * Math.PI / 180);
// var sinPhi = Math.sin(vert[0] * Math.PI / 180);
// var cosPhi = Math.cos(vert[0] * Math.PI / 180);
//
// var r = V3.EARTH_RADIUS + vert[2];
// var result = [
//     r * cosTheta * cosPhi,
//     r * sinPhi,
//     r * -sinTheta * cosPhi ];
// return result;
//},
//
//// Input is meters [x, y, z].  Output is [lat, lon, alt].  Lat & lon
//// in degrees, alt in meters.
//// 
//// V3.cartesianToLatLonAlt([R, 0, 0]) ~= [0, 0, 0]
//// V3.cartesianToLatLonAlt([R/sqrt(2), R/sqrt(2), 0]) ~= [45, 0, 0]
//// V3.cartesianToLatLonAlt([R/sqrt(2), 0, R/sqrt(2)]) ~= [0, -45, 0]
//cartesianToLatLonAlt: function(a) {
// var r = V3.length(a);
// if (r <= 0) {
//   return [NaN, NaN, NaN];
// }
// var alt = r - V3.EARTH_RADIUS;
// // Compute projection onto unit sphere.
// var n = V3.scale(a, 1 / r);
// var lat = Math.asin(n[1]) * 180 / Math.PI;
// if (lat > 90) {
//   lat -= 180;
// }
// var lon = 0;
// if (Math.abs(lat) < 90) {
//   lon = Math.atan2(n[2], n[0]) * -180 / Math.PI;
// }
// return [lat, lon, alt];
//},
//
//// Return the signed perpendicular distance from the point c to the line
//// defined by [a, b].
////
//// We get the sign by determining if point is to the left of the line,
//// from the point of view of looking towards the origin through vert0.
//// I.e. is it to the left, looking at the surface of the Earth from
//// above.
//leftDistance: function(a, b, c) {
// var ab = V3.sub(b, a);
// var ac = V3.sub(c, a);
// var cross = V3.cross(ab, ac);
//
// var dot = V3.dot(a, cross);
// var lineLength = V3.length(ab);
// if (lineLength < 1e-6) {
//   return NaN;
// }
// var perpendicularDistance = V3.length(cross) / lineLength;
//
// if (dot > 0) {
//   return perpendicularDistance;
// } else {
//   return -perpendicularDistance;
// }
//},
//
//// Return the distance between two cartesian 3d points, along the
//// surface of the Earth, assuming they are on the surface of the
//// Earth.  (If the inputs are not on the surface of the earth, they
//// are projected to the surface first.)
//earthDistance: function(a, b) {
// var dot = V3.dot(V3.normalize(a), V3.normalize(b));
// var angle = Math.acos(dot);
// var dist = V3.EARTH_RADIUS * angle;
// return dist;
//}
//};
//
//M33 = {
//// Conventions:
////
//// * V3 is a 3-element array representing a column vector
////
//// * M33 is an array of 3 column vectors, representing a 3x3 matrix
////
////   [ [00] [10] [20] ]
////   [ [01] [11] [21] ]
////   [ [02] [12] [22] ]
//
//toString: function(a) {
// return "[" + V3.toString(a[0]) + ", " + 
//   V3.toString(a[1]) + ", " + V3.toString(a[2]) + "]";
//},
//
//nearlyEqual: function(a, b) {
// return V3.nearlyEqual(a[0], b[0])
//   && V3.nearlyEqual(a[1], b[1])
//   && V3.nearlyEqual(a[2], b[2]);
//},
//
//transpose: function(a) {
// return [
//     [a[0][0], a[1][0], a[2][0]],
//     [a[0][1], a[1][1], a[2][1]],
//     [a[0][2], a[1][2], a[2][2]]];
//},
//
//multiply: function(a, b) {
// var result = [[0, 0, 0], [0, 0, 0], [0, 0, 0]];
// for (var i = 0; i < 3; i++) {
//   for (var j = 0; j < 3; j++) {
//     result[i][j] = a[0][j] * b[i][0]
//                    + a[1][j] * b[i][1]
//                    + a[2][j] * b[i][2];
//   }
// }
// return result;
//},
//
//// Applies matrix a to column vector b.  (I.e. returns a * b)
//transform: function(a, b) {
// return [
//     a[0][0] * b[0] + a[1][0] * b[1] + a[2][0] * b[2],
//     a[0][1] * b[0] + a[1][1] * b[1] + a[2][1] * b[2],
//     a[0][2] * b[0] + a[1][2] * b[1] + a[2][2] * b[2]];
//},
//
//// Applies the transpose of matrix a to column vector b.
//// (I.e. returns a.transpose() * b)
//transformByTranspose: function(a, b) {
// return [
//     a[0][0] * b[0] + a[0][1] * b[1] + a[0][2] * b[2],
//     a[1][0] * b[0] + a[1][1] * b[1] + a[1][2] * b[2],
//     a[2][0] * b[0] + a[2][1] * b[1] + a[2][2] * b[2]];
//},
//
//identity: function() {
// return [[1, 0, 0], [0, 1, 0], [0, 0, 1]];
//},
//
//makeOrthonormalFrame: function(dir, up) {
// var newright = V3.normalize(V3.cross(dir, up));
// var newdir = V3.normalize(V3.cross(up, newright));
// var newup = V3.cross(newright, newdir);
// return [newright, newdir, newup];
//},
//
//// [heading, tilt, roll] (degrees) to [[right],[dir],[up]] (local
//// coords).  The return value transforms global direction vectors
//// into local direction vectors.  The transpose of the return value
//// transforms local direction vectors into global direction vectors.
////
//// heading, tilt, roll are in degrees, clockwise about z,x,y axes (!?).
//// heading of 0 means pointing North
//// heading of 90 means pointing West
////
//// [1, 0, 0] in local coords points right (East, for 0, 0, 0 htr)
//// [0, 1, 0] in local coords points ahead (North, for 0, 0, 0 htr)
//// [0, 0, 1] in local coords points up (away from center of earth)
//headingTiltRollToLocalOrientationMatrix: function(htr) {
// return M33.eulToMat(V3.toRadians(htr));
//},
//
//// [[right], [dir], [up]] (in local cartesian coords) to [heading, tilt, roll]
//// (in degrees)
//localOrientationMatrixToHeadingTiltRoll: function(mat) {
// var htr = M33.matToEul(mat);
// return V3.toDegrees(htr);
//},
//
//// Builds a local orientation matrix, to transform from local coords
//// into global coords.  "Local" means that the local x basis vector
//// points East, the y basis vector points North and the z basis
//// vector points Up (towards the sky).
//makeLocalToGlobalFrame: function(latLonAlt) {
// var vertical = V3.normalize(V3.latLonAltToCartesian(latLonAlt));
// var east = V3.normalize(V3.cross([0, 1, 0], vertical));
// var north = V3.normalize(V3.cross(vertical, east));
//
// return [east, north, vertical];
//},
//
//// See Graphics Gems IV, Chapter III.5, "Euler Angle Conversion" by
//// Ken Shoemake.
////
//// http://vered.rose.utoronto.ca/people/spike/GEMS/GEMS.html
//eulerConfig: {
// i: 2, j: 0, k: 1,     // NOTE: KML convention is Z, X, Y!
// counterClockwise: true,
// sameAxis: false,      // third axis same as first (i == k)
// frameRelative: false  // frame-relative (vs. static)
//},
//
//// Construct matrix from Euler angles (in radians).
////
//// Thanks to Ken Shoemake / Graphics Gems
//eulToMat: function(eulerAnglesIn) {
// var ti, tj, th, ci, cj, ch, si, sj, sh, cc, cs, sc, ss;
// var config = M33.eulerConfig;
// var i = config.i;
// var j = config.j;
// var k = config.k;
//
// var ea = V3.dup(eulerAnglesIn);
// var m = [[0, 0, 0], [0, 0, 0], [0, 0, 0]];
// if (config.frameRelative) { var t = ea[0]; ea[0] = ea[2]; ea[2] = t; }
// if (!config.counterClockwise) {
//   ea[0] = -ea[0]; ea[1] = -ea[1]; ea[2] = -ea[2];
// }
// ti = ea[0];	  tj = ea[1];	th = ea[2];
// ci = Math.cos(ti); cj = Math.cos(tj); ch = Math.cos(th);
// si = Math.sin(ti); sj = Math.sin(tj); sh = Math.sin(th);
// cc = ci*ch; cs = ci*sh; sc = si*ch; ss = si*sh;
// if (config.sameAxis) {
//   m[i][i] = cj;     m[i][j] =  sj*si;    m[i][k] =  sj*ci;
//   m[j][i] = sj*sh;  m[j][j] = -cj*ss+cc; m[j][k] = -cj*cs-sc;
//   m[k][i] = -sj*ch; m[k][j] =  cj*sc+cs; m[k][k] =  cj*cc-ss;
// } else {
//   m[i][i] = cj*ch; m[i][j] = sj*sc-cs; m[i][k] = sj*cc+ss;
//   m[j][i] = cj*sh; m[j][j] = sj*ss+cc; m[j][k] = sj*cs-sc;
//   m[k][i] = -sj;   m[k][j] = cj*si;    m[k][k] = cj*ci;
// }
// return m;
//},
//
//// Convert matrix to Euler angles (in radians).
////
//// Thanks to Ken Shoemake / Graphics Gems
//matToEul: function(m, config) {
// var config = M33.eulerConfig;
// var i = config.i;
// var j = config.j;
// var k = config.k;
//
// var FLT_EPSILON = 1e-6;
// var ea = [0, 0, 0];
// if (config.sameAxis) {
//   var sy = Math.sqrt(m[i][j] * m[i][j] + m[i][k] * m[i][k]);
//   if (sy > 16 * FLT_EPSILON) {
//     ea[0]= Math.atan2(m[i][j], m[i][k]);
//     ea[1]= Math.atan2(sy, m[i][i]);
//     ea[2]= Math.atan2(m[j][i], -m[k][i]);
//   } else {
//     ea[0]= Math.atan2(-m[j][k], m[j][j]);
//     ea[1]= Math.atan2(sy, m[i][i]);
//     ea[2]= 0;
//   }
// } else {
//   var cy = Math.sqrt(m[i][i] * m[i][i] + m[j][i] * m[j][i]);
//   if (cy > 16 * FLT_EPSILON) {
//     ea[0]= Math.atan2(m[k][j], m[k][k]);
//     ea[1]= Math.atan2(-m[k][i], cy);
//     ea[2]= Math.atan2(m[j][i], m[i][i]);
//   } else {
//     ea[0]= Math.atan2(-m[j][k], m[j][j]);
//     ea[1]= Math.atan2(-m[k][i], cy);
//     ea[2]= 0;
//   }
// }
// if (!config.counterClockwise) {
//   ea[0] = -ea[0]; ea[1] = -ea[1]; ea[2] = -ea[2];
// }
// if (config.frameRelative) { var t = ea[0]; ea[0] = ea[2]; ea[2] = t; }
// return ea;
//}
//};







