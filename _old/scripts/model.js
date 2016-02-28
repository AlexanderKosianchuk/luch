$(document).ready(function(){
	
	var flightId = $("input#flightId").attr('value'),
		startFrame = $("input#startFrame").attr('value'),
		endFrame = $("input#endFrame").attr('value'),
		stepLenght = $("input#stepLenght").attr('value'),
	
		container = $('div#model');
	
	var animationNeed = false;
	
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
			animationNeed = true;
			startRunning();
		} else {
			options = {
				label : "play",
				icons : {
					primary : "ui-icon-play"
				}
			};
			animationNeed = false;
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
		animationNeed = false;
		$slider.slider("value", 0);
		startRunning();
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
		animationNeed = false;
		startRunning();
	});
	
	var coord = new Coordinate(flightId, 0, 1);
	var paramValues = [];
	
	function startRunning() {
		var curFrameNum = $slider.slider("value");
		coord.startFrame = curFrameNum;
		coord.endFrame = curFrameNum + 1;
		var rParmas = coord.ReceiveParams();
		
		model.rotation.z = rParmas['KM'][1] * Math.PI / 180;
		model.rotation.y = rParmas['TG'][1] * Math.PI / 180;
		model.rotation.x = rParmas['KR'][1] * Math.PI / 180;
		
		start();
		Running();
	}
	
	function Running() {
		if(animationNeed) {
			
			var curFrameNum = $slider.slider("value");
			
			coord.startFrame = curFrameNum;
			coord.endFrame = curFrameNum + 1;
			var rParmas = coord.ReceiveParams();
			
			model.rotation.z += rParmas['KM'][1] * 0.01 * Math.PI / 180;
			model.rotation.y += rParmas['TG'][1] * 0.01 * Math.PI / 180;
			model.rotation.x += rParmas['KR'][1] * 0.01 * Math.PI / 180;
			
			$slider.slider("value", $slider.slider("value") + 1);
			start();
			setTimeout(function(){ Running() }, stepLenght * 1000);
		}
	}
	
	if (! Detector.webgl ) Detector.addGetWebGLMessage();

	var scene;
	var camera;
	var renderer;
	var model;
	var animations;
	var kfAnimations = [ ];
	var kfAnimationsLength = 0;
	var loader = new THREE.ColladaLoader();
	var lastTimestamp;
	var progress = 0;
	
	var initModelRotationX, initModelRotationY, initModelRotationZ;

	loader.load( './models/mi8.dae', function ( collada ) {

		model = collada.scene;
		animations = collada.animations;
		kfAnimationsLength = animations.length;
		
		model.scale.x = model.scale.y = model.scale.z = 4.125; // 1/8 scale, modeled in cm
		/*model.rotation.y = 0 * Math.PI / 180;
		model.rotation.x = -90 * Math.PI / 180;
		model.rotation.z = 180 * Math.PI / 180;*/
		
		initModelRotationX = model.rotation.x;
		initModelRotationY = model.rotation.y;
		initModelRotationZ = model.rotation.z;

		init();
		start();
		animate( lastTimestamp );

	});

	function init() {

		// Camera

		camera = new THREE.PerspectiveCamera( 40, window.innerWidth / window.innerHeight, 0.01, 1000 );
		camera.up = new THREE.Vector3(0,0,1);
		camera.position.set(10, 0, 20);

		// Scene

		scene = new THREE.Scene();
		scene.fog = new THREE.FogExp2( 0xfff4e5, 0.03 );

		// KeyFrame Animations

		var animHandler = THREE.AnimationHandler;

		for ( var i = 0; i < kfAnimationsLength; ++i ) {

			var animation = animations[i];
			animHandler.add( animation );

			var kfAnimation = new THREE.KeyFrameAnimation( animation.node, animation.name );
			kfAnimation.timeScale = 1;
			kfAnimations.push(kfAnimation);

		}

		// Grid

		var size = 14, step = 1;

		var geometry = new THREE.Geometry();
		var material = new THREE.LineBasicMaterial( { color: 0x303030 } );

		for ( var i = - size; i <= size; i += step ) {

			geometry.vertices.push( new THREE.Vector3( - size, - 0.04, i ) );
			geometry.vertices.push( new THREE.Vector3(   size, - 0.04, i ) );

			geometry.vertices.push( new THREE.Vector3( i, - 0.04, - size ) );
			geometry.vertices.push( new THREE.Vector3( i, - 0.04,   size ) );

		}

		var line = new THREE.Line( geometry, material, THREE.LinePieces );
		
		line.rotation.y = 0 * Math.PI / 180;
		line.rotation.x = -90 * Math.PI / 180;
		line.rotation.z = 180 * Math.PI / 180;
		
		scene.add( line );

		// Add the COLLADA

		//model.getObjectByName( 'camEye_camera', true ).visible = false;
		//model.getObjectByName( 'camTarget_camera', true ).visible = false;

		scene.add(model);

		// Lights

		pointLight = new THREE.PointLight( 0xffffff, 1.75 );
		pointLight.position = camera.position;

		scene.add( pointLight );
		camera.lookAt(scene.position);

		// Renderer

		renderer = new THREE.WebGLRenderer( { antialias: true } );
		renderer.setSize( window.innerWidth, window.innerHeight );
		renderer.setClearColor(0x659CEF, 1);

		container.append(renderer.domElement );

		window.addEventListener( 'resize', onWindowResize, false );

	}

	function onWindowResize() {

		camera.aspect = window.innerWidth / window.innerHeight;
		camera.updateProjectionMatrix();

		renderer.setSize( window.innerWidth, window.innerHeight );

	}

	function start() {

		for ( var i = 0; i < kfAnimationsLength; ++i ) {

			var animation = kfAnimations[i];

			for ( var h = 0, hl = animation.hierarchy.length; h < hl; h++ ) {

				var keys = animation.data.hierarchy[ h ].keys;
				var sids = animation.data.hierarchy[ h ].sids;
				var obj = animation.hierarchy[ h ];

				if ( keys.length && sids ) {

					for ( var s = 0; s < sids.length; s++ ) {

						var sid = sids[ s ];
						var next = animation.getNextKeyWith( sid, h, 0 );

						if ( next ) next.apply( sid );

					}

					obj.matrixAutoUpdate = false;
					animation.data.hierarchy[ h ].node.updateMatrix();
					obj.matrixWorldNeedsUpdate = true;

				}

			}
			animation.loop = false;
			animation.play();
			lastTimestamp = Date.now();

		}

	}

	function animate() {			

		var timestamp = Date.now();
		var frameTime = ( timestamp - lastTimestamp ) * 0.009; // seconds

		if(animationNeed) {
			if ( progress >= 0 && progress < 1 ) {
	
				for ( var i = 0; i < kfAnimationsLength; ++i ) {
					kfAnimations[i].update( frameTime );
				}
	
			} else if ( progress >= 1 ) {
	
				for ( var i = 0; i < kfAnimationsLength; ++i ) {
					kfAnimations[i].stop();
				}
	
				progress = 0;
				start();
	
			}
		}

		progress += frameTime;
		lastTimestamp = timestamp;
		renderer.render( scene, camera );
		requestAnimationFrame( animate );		
	}
	
	
	/*if ( ! Detector.webgl ) Detector.addGetWebGLMessage();

	var stats;
	var scene;
	var camera;
	var renderer;
	var model;
	var animations;
	var kfAnimations = [ ];
	var kfAnimationsLength = 0;
	var loader = new THREE.ColladaLoader();
	var lastTimestamp;
	var progress = 0;

	loader.load( './models/pump.dae', function ( collada ) {

		model = collada.scene;
		animations = collada.animations;
		kfAnimationsLength = animations.length;
		model.scale.x = model.scale.y = model.scale.z = 8.125; // 1/8 scale, modeled in cm

		init();
		start();
		animate( lastTimestamp );

	} );

	function init() {

		// Camera

		camera = new THREE.PerspectiveCamera( 40, window.innerWidth / window.innerHeight, 0.01, 1000 );
		camera.position.set(20, 20, 30);

		// Scene
		scene = new THREE.Scene();

		// KeyFrame Animations
		var animHandler = THREE.AnimationHandler;

		for ( var i = 0; i < kfAnimationsLength; ++i ) {

			var animation = animations[ i ];
			animHandler.add( animation );

			var kfAnimation = new THREE.KeyFrameAnimation( animation.node, animation.name );
			kfAnimation.timeScale = 1;
			kfAnimations.push( kfAnimation );

		}

		// Grid
		var material = new THREE.LineBasicMaterial( { color: 0x303030 } );
		var geometry = new THREE.Geometry();
		var floor = -0.04, step = 1, size = 14;

		for ( var i = 0; i <= size / step * 2; i ++ ) {

			geometry.vertices.push( new THREE.Vector3( - size, floor, i * step - size ) );
			geometry.vertices.push( new THREE.Vector3(   size, floor, i * step - size ) );
			geometry.vertices.push( new THREE.Vector3( i * step - size, floor, -size ) );
			geometry.vertices.push( new THREE.Vector3( i * step - size, floor,  size ) );

		}

		var line = new THREE.Line( geometry, material, THREE.LinePieces );
		scene.add( line );
		camera.lookAt( scene.position );


		// Lights
		pointLight = new THREE.PointLight( 0xffffff, 1.75 );
		pointLight.position = camera.position;

		scene.add( pointLight );

		// Renderer
		renderer = new THREE.WebGLRenderer( { antialias: true } );
		renderer.setSize( window.innerWidth, window.innerHeight );

		container.append( renderer.domElement );

		//
		window.addEventListener( 'resize', onWindowResize, false );

	}

	function onWindowResize() {

		camera.aspect = window.innerWidth / window.innerHeight;
		camera.updateProjectionMatrix();

		renderer.setSize( window.innerWidth, window.innerHeight );
	}

	function start() {

		for ( var i = 0; i < kfAnimationsLength; ++i ) {

			var animation = kfAnimations[i];

			for ( var h = 0, hl = animation.hierarchy.length; h < hl; h++ ) {

				var keys = animation.data.hierarchy[ h ].keys;
				var sids = animation.data.hierarchy[ h ].sids;
				var obj = animation.hierarchy[ h ];

				if ( keys.length && sids ) {

					for ( var s = 0; s < sids.length; s++ ) {

						var sid = sids[ s ];
						var next = animation.getNextKeyWith( sid, h, 0 );

						if ( next ) next.apply( sid );

					}

					obj.matrixAutoUpdate = false;
					animation.data.hierarchy[ h ].node.updateMatrix();
					obj.matrixWorldNeedsUpdate = true;
				}

			}
			animation.loop = false;
			animation.play();
			lastTimestamp = Date.now();

		}

	}

	function animate() {

		var timestamp = Date.now();
		var frameTime = ( timestamp - lastTimestamp ) * 0.001; // seconds

		if ( progress >= 0 && progress < 48 ) {

			for ( var i = 0; i < kfAnimationsLength; ++i ) {

				kfAnimations[ i ].update( frameTime );
			}

		} else if ( progress >= 48 ) {

			for ( var i = 0; i < kfAnimationsLength; ++i ) {

				kfAnimations[ i ].stop();
			}

			progress = 0;
			start();

		}

		progress += frameTime;
		lastTimestamp = timestamp;
		renderer.render( scene, camera );
		requestAnimationFrame( animate );
	}
		
	/*if (!Detector.webgl) Detector.addGetWebGLMessage();

	var container;

	var camera, scene, renderer, objects;
	var particleLight, pointLight;
	var dae, skin, animationKeyFrames;

	var loader = new THREE.ColladaLoader();
	loader.options.convertUpAxis = true;
	loader.load( './models/mi8.dae', function ( collada ) {

		dae = collada.scene;
		console.log(collada);
		//skin = collada.skins[ 0 ];
		animationKeyFrames = collada.animations[0];
		console.log(animationKeyFrames);

		dae.scale.x = dae.scale.y = dae.scale.z = 5.007;
		dae.updateMatrix();

		init();
		animate();

	} );

	function init() {

		container = $('div#model');

		camera = new THREE.PerspectiveCamera( 45, window.innerWidth / window.innerHeight, 1, 2000 );
		camera.position.set( 2, 2, 3 );

		scene = new THREE.Scene();

		// Grid

		var size = 14, step = 1;

		var geometry = new THREE.Geometry();
		var material = new THREE.LineBasicMaterial( { color: 0x303030 } );

		for ( var i = - size; i <= size; i += step ) {

			geometry.vertices.push( new THREE.Vector3( - size, - 0.04, i ) );
			geometry.vertices.push( new THREE.Vector3(   size, - 0.04, i ) );

			geometry.vertices.push( new THREE.Vector3( i, - 0.04, - size ) );
			geometry.vertices.push( new THREE.Vector3( i, - 0.04,   size ) );

		}

		var line = new THREE.Line( geometry, material, THREE.LinePieces );
		scene.add( line );

		// Add the COLLADA

		scene.add(dae);

		particleLight = new THREE.Mesh( new THREE.SphereGeometry( 4, 8, 8 ), new THREE.MeshBasicMaterial( { color: 0xffffff } ) );
		scene.add(particleLight);

		// Lights

		scene.add( new THREE.AmbientLight( 0xcccccc ) );

		var directionalLight = new THREE.DirectionalLight(0xeeeeee );
		directionalLight.position.x = Math.random() - 0.5;
		directionalLight.position.y = Math.random() - 0.5;
		directionalLight.position.z = Math.random() - 0.5;
		directionalLight.position.normalize();
		scene.add( directionalLight );
		
		

		particleLight.position.x = 3009;
		particleLight.position.y = 4000;
		particleLight.position.z = 3009;

		pointLight = new THREE.PointLight( 0xffffff, 4 );
		pointLight.position = particleLight.position;
		scene.add( pointLight );
		
		camera.position.x = -15;
		camera.position.y = 5;
		camera.position.z = 0;

		camera.lookAt(scene.position);

		renderer = new THREE.WebGLRenderer();
		renderer.setSize( window.innerWidth, window.innerHeight );
		renderer.setClearColorHex(0x659CEF, 1 );

		container.append(renderer.domElement);

		window.addEventListener( 'resize', onWindowResize, false );

	}

	function onWindowResize() {

		camera.aspect = window.innerWidth / window.innerHeight;
		camera.updateProjectionMatrix();

		renderer.setSize( window.innerWidth, window.innerHeight );

	}

	//

	var t = 0;
	var clock = new THREE.Clock();

	function animate() {

		var delta = clock.getDelta();

		requestAnimationFrame( animate );

		if ( t > 1 ) t = 0;

		if (animationKeyFrames) {

			// guess this can be done smarter...

			// (Indeed, there are way more frames than needed and interpolation is not used at all
			//  could be something like - one morph per each skinning pose keyframe, or even less,
			//  animation could be resampled, morphing interpolation handles sparse keyframes quite well.
			//  Simple animation cycles like this look ok with 10-15 frames instead of 100 ;)

			for ( var i = 0; i < animationKeyFrames.morphTargetInfluences.length; i++ ) {

				skin.morphTargetInfluences[ i ] = 0;

			}

			skin.morphTargetInfluences[ Math.floor( t * 30 ) ] = 1;

			t += delta;

		}

		render();

	}

	function render() {

		var timer = Date.now() * 0.0005;

		renderer.render( scene, camera );

	}*/

});







