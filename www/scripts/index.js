jQuery(function($) { $(document).ready(function() {
	
	var $document = $(document),
		$window = $(window),
		userLang = $('html').attr("lang"),
		eventHandler = $('#eventHandler');
	
	var LA = new Language(userLang),
	W = new WindowFactory($window, $document),
	FP = null,
	FU = null,
	FO = null, 
	B = null,
	C = null, 
	U = null, 
	FL = null,
	SF = null;
	
	LA.GetLanguage().done(function(data){
		var langStr = data;
		LA.GetServiceStrs().done(function(data){
			var srvcStrObj = data,
				wsp = W.NewShowcase();
			FL = new FlightList(langStr, srvcStrObj, eventHandler);
			FU = new FlightUploader($window, $document, langStr, srvcStrObj, eventHandler);
			FP = new FlightProccessingStatus(langStr);
			FO = new FlightViewOptions($window, $document, langStr, srvcStrObj, eventHandler);
			B = new BruType($window, $document, langStr, srvcStrObj, eventHandler);
			C = new Chart($window, $document, langStr, srvcStrObj, eventHandler);	
			U = new User($window, $document, langStr, srvcStrObj, eventHandler);
			SF = new SearchFlight($window, $document, langStr, srvcStrObj, eventHandler);
			
			FL.FillFactoryContaider(wsp);
		});
	});
	
	$window.resize(function(e) {	
		if(W != null){
			W.ResizeShowcase(e);
		}
		
		if(FL != null){
			FL.ResizeFlightList(e);
		}
		if(FO != null){
			FO.ResizeFlightViewOptionsContainer(e);
		}
		if(C != null){
			C.ResizeChartContainer(e);
		}
		
		if(B != null){
			B.ResizeBruTypeContainer(e);
		}
	});
	
	$document.resize(function(e) {	
		if(W != null){
			W.ResizeShowcase(e);
		}
	});
	
//	var watchdog = true;
//	$document.mousewheel(function(event, delta) {
//		var this$ = $(this);
//		event.stopPropagation();
//		event.preventDefault();
		
//		if(watchdog){
//			watchdog = false;
//			if(delta == 1){
//				$("html, body").animate({ scrollTop: this$.scrollTop() - $window.height() }, 250,
//					function(e){
//						watchdog = true;
//					});
//			}
//			else if(delta == -1){
//				$("html, body").animate({ scrollTop: this$.scrollTop() + $window.height() }, 250,
//					function(e){
//						watchdog = true;
//					});
//			}
//		}
//	});
	
	eventHandler.on("resizeShowcase", function(e, data){
		W.ResizeShowcase(e);
	});
	
	eventHandler.on("uploading", function(e, data){
		FU.CaptureUploadingItems();
		FP.SupportUploadingStatus();
	});
	
	eventHandler.on("uploadWithPreview", function(e, data){
		var showcase = W.NewShowcase();
		FU.FillFactoryContaider(showcase);
	});
	
	eventHandler.on("removeShowcase", function(e, data){
		var flightUploaderFactoryContainer = data;
		W.RemoveShowcase(flightUploaderFactoryContainer);
	});

	///=======================================================
	//FlightList
	///
	
	eventHandler.on("flightSearchFormShow", function(e, showcase){
		if(showcase == null){
			W.RemoveShowcases(1);
			showcase = W.NewShowcase();
		} else {
			W.ClearShowcase(showcase);
		}
		
		if((FP != null) && (FL != null)) {		
			FL.ShowFlightsListInitial(showcase);
			FP.SupportUploadingStatus();
		}
	});
		
	///=======================================================
	//FlightProccessingStatus
	///
	eventHandler.on("startProccessing", function(e, data){
		var bruType = data['bruType'],
			fileName = data['fileName'],
			tempFileName = data['tempFileName'];

		if(FP != null) {
			FP.SetUpload(fileName, bruType, tempFileName);
		}
	});

	eventHandler.on("endProccessing", function(e, data){
		var fileName = data;
		if(FP != null) {
			FP.RemoveUpload(fileName);
		}
	});
	
	eventHandler.on("convertSelectedClicked", function(e){
		W.RemoveShowcases(1);
		
		if(FL != null) {
			FL.ShowFlightsByPath();
		}
	});	
		
	///=======================================================
	
	///=======================================================
	//FlightViewOptions
	///	
	eventHandler.on("viewFlightOptions", function(e, flightId, task, someshowcase){	
		if(someshowcase == null){
			W.RemoveShowcases(1);
			someshowcase = W.NewShowcase();
		} else {
			W.ClearShowcase(someshowcase);
		}
		
		if(flightId != null){
			FO.flightId = flightId;
		}
		
		if(task != null){
			FO.task = task;
		}
		
		if(FO.flightId != null){
			FO.FillFactoryContaider(someshowcase);
		}
	});
	///=======================================================
	
	///=======================================================
	//FlightViewOptions
	///	
	eventHandler.on("showBruTypeEditingForm", function(e, bruTypeId, task, showcase){		
		if(showcase == null){
			W.RemoveShowcases(1);
			showcase = W.NewShowcase();
		} else {
			W.ClearShowcase(showcase);
		}
		
		if(bruTypeId != null){
			B.bruTypeId = bruTypeId;
		}
		
		if(task != null){
			B.task = task;
		}
		
		B.FillFactoryContaider(showcase);
	});
	
	///=======================================================
	
	///=======================================================
	//User
	///	
	eventHandler.on("userLogout", function(e){		
		U.logout();
	});
	
	eventHandler.on("userChangeLanguage", function(e, lang){		
		U.changeLanguage(lang);
	});
		
	eventHandler.on("userShowList", function(e, showcase){		
		if(showcase == null){
			W.RemoveShowcases(1);
			showcase = W.NewShowcase();
		} else {
			W.ClearShowcase(showcase);
		}
		
		U.FillFactoryContaider(showcase);
	});
	
	eventHandler.on("flightSearchFormShow", function(e, showcase){		
		if(showcase == null){
			W.RemoveShowcases(1);
			showcase = W.NewShowcase();
		} else {
			W.ClearShowcase(showcase);
		}
		
		SF.FillFactoryContaider(showcase);
	});
	
	///=======================================================
	
	///=======================================================
	//Chart
	///	
	eventHandler.on("showChart", function(e, 
			flightId, tplName, 
			stepLength, startCopyTime, startFrame, endFrame,
			apParams, bpParams){
		
		W.RemoveShowcases(2);
		var showcase = W.NewShowcase();
		
		if(C != null) {
			C.SetChartData(flightId, tplName,  
					stepLength, startCopyTime, startFrame, endFrame,
					apParams, bpParams);
			
			C.FillFactoryContaider(showcase);
		}
	});
	///=======================================================
});});


