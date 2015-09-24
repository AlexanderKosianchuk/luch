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
	FL = null;
	
	LA.GetLanguage().done(function(data){
		var langStr = data;
		LA.GetServiceStrs().done(function(data){
			var srvcStrObj = data,
				showcase = W.NewShowcase();
			FL = new FlightList(langStr, srvcStrObj, eventHandler);
			FU = new FlightUploader($window, $document, langStr, srvcStrObj, eventHandler);
			FP = new FlightProccessingStatus(langStr);
			FO = new FlightViewOptions($window, $document, langStr, srvcStrObj, eventHandler);
			B = new BruType($window, $document, langStr, srvcStrObj, eventHandler);
			C = new Chart($window, $document, langStr, srvcStrObj, eventHandler);	
			U = new User($window, $document, langStr, srvcStrObj, eventHandler);
				
			FL.FillFactoryContaider(showcase);
			FP.SupportUploadingStatus();
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
	
	var watchdog = true;
	$document.mousewheel(function(event, delta) {
		var this$ = $(this);
		event.stopPropagation();
		event.preventDefault();
		
		if(watchdog){
			watchdog = false;
			if(delta == 1){
				$("html, body").animate({ scrollTop: this$.scrollTop() - $window.height() }, 250,
					function(e){
						watchdog = true;
					});
			}
			else if(delta == -1){
				$("html, body").animate({ scrollTop: this$.scrollTop() + $window.height() }, 250,
					function(e){
						watchdog = true;
					});
			}
		}
	});
	
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
	eventHandler.on("viewFlightOptions", function(e, flightId, task, showcase){
		if(showcase == null){
			W.RemoveShowcases(1);
			showcase = W.NewShowcase();
		} else {
			W.ClearShowcase(showcase);
		}
		
		if(flightId != null){
			FO.flightId = flightId;
		}
		
		if(task != null){
			FO.task = task;
		}
		
		if(FO.flightId != null){
			FO.FillFactoryContaider(showcase);
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

























//$(document).ready(function() {
//	var mm = $('div.MainMenu'),
//		fsm = $('div.FlightSubMenu'),
//		ssm = $('div.SliceSubMenu'),
//		esm = $('div.EngineSubMenu'),
//		bsm = $('div.BruTypesSubMenu'),
//		dsm = $('div.DocsSubMenu'),
//		usm = $('div.UserSubMenu'),
//		
//		fileUpload = $('div#fileUpload').dialog({
//			resizable:false,
//			autoOpen: false,
//			hide: { 
//				effect: "fadeOut",  
//				duration: 150 
//			},
//			show: { 
//				effect: "fadeIn", 
//				duration: 150
//			} 
//		}),
//		
//		fileImport = $('div#fileImport').dialog({
//			resizable:false,
//			autoOpen: false,
//			resize: "auto",
//			hide: { 
//				effect: "fadeOut",  
//				duration: 150 
//			},
//			show: { 
//				effect: "fadeIn", 
//				duration: 150
//			} 
//		}),
//		
//		sliceCreation = $('div#sliceCreation').dialog({
//			resizable:false,
//			autoOpen: false,
//			hide: { 
//				effect: "fadeOut",  
//				duration: 150 
//			},
//			show: { 
//				effect: "fadeIn", 
//				duration: 150
//			} 
//		}),
//		messageBox = $('div#dialog').dialog({
//			resizable:false,
//			autoOpen: false,
//			hide: { 
//				effect: "fadeOut",  
//				duration: 150 
//			},
//			show: { 
//				effect: "fadeIn", 
//				duration: 150
//			} 
//		}),
//
//		flightListForm = $('form#flightList'),
//		
//		sliceListForm = $('form#sliceList'),
//		sliceListflightId = $('form#sliceList #flightId'),
//		sliceListAction = $('form#sliceList #sliceUploaderAction'),
//		
//		engineListAction = $('form#enginesList #engineAction'),
//		engineListSerial = $('form#enginesList #engineSerial'),
//		enginesListForm = $('form#enginesList'),
//		
//		bruTypeListForm = $('form#bruTypeList'),
//		bruTypeListAction = $('form#bruTypeList #bruTypeAction'),
//		
//		userListForm = $('form#usersList'),
//		userListAction = $('form#usersList #userAction'),
//		lang = Object();
//		
//		$.ajax({
//			url: LANG_FILE,
//			dataType: 'json',
//			async: false,
//			success: function(data) {
//				lang = data;
//			}
//		}).fail(function() {
//			$.ajax({
//				url: LANG_FILE_DEFAULT,
//				dataType: 'json',
//				async: false,
//				success: function(data) {
//					lang = data;
//				}
//			});
//		});
//		
//		if(navigator.appName == 'Microsoft Internet Explorer'){
//			mm.css({
//				'position': 'absolute',
//				'height' : '99%'
//			});
//			fsm.css({position: 'absolute'});
//			ssm.css({position: 'absolute'});
//			esm.css({position: 'absolute'});
//			usm.css({position: 'absolute'});
//		}
//	
////======================================================	
////Main menu	
////======================================================	
//	mm.on('mouseover', function(e){
//		var el = $(e.target);
//		fileUpload.dialog("close");
//		sliceCreation.dialog("close");
//		if(el.attr('id') == BUT_FLIGHT){
//			ssm.fadeOut(10);
//			esm.fadeOut(10);
//			usm.fadeOut(10);
//			bsm.fadeOut(10);
//			dsm.fadeOut(10);
//			fsm.css({
//				left: el.position().left + el.width() + 11,
//				top: el.position().top + 1
//			}).fadeIn(150);
//		} else if(el.attr('id') == BUT_SLICE){
//			fsm.fadeOut(10);
//			esm.fadeOut(10);
//			usm.fadeOut(10);
//			bsm.fadeOut(10);
//			dsm.fadeOut(10);
//			ssm.css({
//				left: el.position().left + el.width() + 11,
//				top: el.position().top + 1
//			}).fadeIn(150);
//		} else if(el.attr('id') == BUT_ENGINE){
//			fsm.fadeOut(10);
//			ssm.fadeOut(10);
//			usm.fadeOut(10);
//			bsm.fadeOut(10);
//			dsm.fadeOut(10);
//			esm.css({
//				left: el.position().left + el.width() + 11,
//				top: el.position().top + 1
//			}).fadeIn(150);
//		} else if(el.attr('id') == BUT_BRU_TYPE){
//			fsm.fadeOut(10);
//			ssm.fadeOut(10);
//			esm.fadeOut(10);
//			usm.fadeOut(10);
//			dsm.fadeOut(10);
//			bsm.css({
//				left: el.position().left + el.width() + 11,
//				top: el.position().top + 1
//			}).fadeIn(150);
//		} else if(el.attr('id') == BUT_DOCS){
//			fsm.fadeOut(10);
//			ssm.fadeOut(10);
//			esm.fadeOut(10);
//			usm.fadeOut(10);
//			bsm.fadeOut(10);
//			dsm.css({
//				left: el.position().left + el.width() + 11,
//				top: el.position().top + 1
//			}).fadeIn(150);
//		} else if(el.attr('id') == BUT_USER){
//			fsm.fadeOut(10);
//			ssm.fadeOut(10);
//			esm.fadeOut(10);
//			bsm.fadeOut(10);
//			dsm.fadeOut(10);
//			usm.css({
//				left: el.position().left + el.width() + 11,
//				top: el.position().top + 1
//			}).fadeIn(150);
//		} else {
//			fsm.fadeOut(10);
//			ssm.fadeOut(10);
//			esm.fadeOut(10);
//			bsm.fadeOut(10);
//			dsm.fadeOut(10);
//			usm.fadeOut(10);
//		}
//	});
////======================================================	
////Flight menu	
////======================================================
//	fsm.on('click', function(e){
//		var el = $(e.target);
//		if(el.attr('id') == BUT_ADD_FLIGHT){
//			fsm.fadeOut();
//			fileUpload.dialog("option", {
//				position: { 
//					my: "left top", 
//					at: "right top", 
//					of: el 
//				}
//			});
//			fileUpload.dialog("open");
//		} else if(el.attr('id') == BUT_DEL_FLIGHT){
//			fsm.fadeOut();
//			flightListForm.attr('action', FORM_ACTIOM_TO_DEL_IN_FILEUPLOADER);
//			flightListForm.submit();
//		} else if(el.attr('id') == BUT_VIEW_FLIGHT){
//			fsm.fadeOut();
//			flightListForm.attr('action', FORM_ACTIOM_TO_VIEW_IN_TUNER);
//			flightListForm.submit();
//		} else if(el.attr('id') == BUT_FOLLOW_FLIGHT){
//			fsm.fadeOut();
//			flightListForm.attr('action', FORM_ACTIOM_TO_FOLLOW_ON_CHART);
//			flightListForm.submit();
//		} else if(el.attr('id') == BUT_EXPORT_FLIGHT){
//			fsm.fadeOut();
//			
//			var radio = $('input#flightIdRadioBut').filter(':checked').val();
//			if(typeof (radio) === "undefined") {
//				messageBox.find(':first-child').text(lang.errorFlightNotSelect);
//				messageBox.dialog("option", {
//					position: { 
//						my: "left top", 
//						at: "right top", 
//						of: el 
//					}
//				});
//				messageBox.dialog("open");
//			} else {
//				var pV = {
//						action: FLIGHT_EXPORT,
//						flightId: radio,
//				};
//				
//				$.ajax({
//					type: "POST",
//					data: pV,
//					dataType: 'json',
//					url: SCRIPT_ADDR_FILE_PROCESSOR,
//					async: true
//				}).fail(function(msg){
//					console.log(msg);
//				}).done(function(url){
//					$("div#exportLink").
//					append('<iframe type="application/zip" src="'+
//						url +'"></iframe>');
//				});
//			}
//		} else if(el.attr('id') == BUT_IMPORT_FLIGHT){
//			fsm.fadeOut();
//			var url = "fileUploader/";
//			
//		    $('#fileImportBut').fileupload({
//		        url: url,
//		        dataType: 'json',
//		        done: function (e, data) {
//		            $.each(data.result.files, function (index, file) {
//		                $('<p/>').text(file.name).appendTo('#files');
//		                
//		                //when file uploaded call fileProcessor to import it
//			            var pV = {
//								action: FLIGHT_IMPORT,
//								importedFileUrl: file.url,
//						};
//						
//						$.ajax({
//							type: "POST",
//							data: pV,
//							dataType: 'json',
//							url: SCRIPT_ADDR_FILE_PROCESSOR,
//							async: true
//						}).fail(function(msg){
//							console.log(msg);
//						}).done(function(answ){
//							
//							if(answ == "ok"){
//								fileImport.dialog("close");
//								messageBox.find(':first-child').text(lang.flightImportSuccess);
//								messageBox.dialog("option", {
//									position: {
//										my: "center",
//										at: "center",
//										of: window
//									}
//								});
//								messageBox.dialog("open");
//							} else {
//								fileImport.dialog("close");
//								messageBox.find(':first-child').text(lang.flightImportFailed);
//								messageBox.dialog("option", {
//									pposition: {
//										my: "center",
//										at: "center",
//										of: window
//									}
//								});
//								messageBox.dialog("open");
//							}
//						});
//		            });
//		        },
//		        progressall: function (e, data) {
//		            var progress = parseInt(data.loaded / data.total * 100, 10);
//		            $('#progress .progress-bar').css(
//		                'width',
//		                progress + '%'
//		            );
//		        }
//		    }).prop('disabled', !$.support.fileInput)
//		        .parent().addClass($.support.fileInput ? undefined : 'disabled');
//	    
//			fileImport.dialog("option", {
//				position: { 
//					my: "left top", 
//					at: "right top", 
//					of: el 
//				}
//			});
//			fileImport.dialog("open");
//		}
//	});
//	
//	$("tr#flightRow").on('dblclick', function(e){
//		var children = $(e.currentTarget).children();
//		children.find("input").prop('checked',true);
//		flightListForm.attr('action', FORM_ACTIOM_TO_FAST_SHOW_ON_CHART);
//		flightListForm.submit();
//	});
////======================================================	
////Slice menu	
////======================================================
//	ssm.on('click', function(e){
//		var el = $(e.target);
//		if(el.attr('id') == BUT_CALC_SLICE){
//			ssm.fadeOut();
//			sliceListForm.attr('action', FORM_ACTIOM_SLICEUPLOADER);
//			sliceListflightId.attr('name', '');
//			sliceListAction.attr('value', ACTION_SLICE_SHOW);
//			sliceListForm.submit();
//		} else if(el.attr('id') == BUT_CREATE_SLICE){
//			ssm.fadeOut();
//			sliceCreation.dialog("option", {
//				position: { 
//					my: "left top", 
//					at: "right top", 
//					of: el 
//				}
//			});
//			sliceListAction.attr('value', ACTION_SLICE_CREATE);
//			sliceCreation.dialog("open");
//		} else if(el.attr('id') == BUT_DEL_SLICE){
//			ssm.fadeOut();
//			sliceListForm.attr('action', FORM_ACTIOM_SLICEUPLOADER);
//			sliceListflightId.attr('name', '');
//			sliceListAction.attr('value', ACTION_SLICE_DEL);
//			sliceListForm.submit();
//		//if CHOOSE_SLICE set to input flightId on form value of radio
//		} else if(el.attr('id') == BUT_CHOOSE_SLICE){
//			ssm.fadeOut();
//			sliceListForm.attr('action', FORM_ACTIOM_SLICEUPLOADER);
//			var radio = $('input#flightIdRadioBut').filter(':checked').val();
//			if(typeof (radio) === "undefined") {
//				messageBox.find(':first-child').text(lang.errorFlightNotSelect);
//				messageBox.dialog("option", {
//					position: { 
//						my: "left top", 
//						at: "right top", 
//						of: el 
//					}
//				});
//				messageBox.dialog("open");
//			} else {
//				//enable both BUT_APPEND_SLICE and BUT_COMPARE_SLICE
//				el.attr('hidden', true).next().attr('hidden', false)
//					.next().attr('hidden', false);
//				sliceListflightId.attr("value", radio);
//				messageBox.find(':first-child').text(lang.flightSelectedChooseSlice);
//				messageBox.dialog("option", {
//					position: { 
//						my: "left top", 
//						at: "right top", 
//						of: el 
//					}
//				});		
//				messageBox.dialog("open");
//			}
//			/*flListBut.attr('name', BUT_NAME_TO_VIEW);
//			flightListForm.submit();*/
//		} else if(el.attr('id') == BUT_APPEND_SLICE){
//			ssm.fadeOut();
//			sliceListAction.attr('value', ACTION_SLICE_APPEND);
//			sliceListForm.submit();
//		} else if(el.attr('id') == BUT_COMPARE_SLICE){
//			ssm.fadeOut();
//			sliceListAction.attr('value', ACTION_SLICE_COMPARE);
//			sliceListForm.submit();
//		} else if(el.attr('id') == BUT_ETALON_SLICE){
//			ssm.fadeOut();
//			sliceListAction.attr('value', ACTION_SLICE_ETALON);
//			sliceListForm.submit();
//		}
//	});
////======================================================	
////Engine menu	
////======================================================
//	esm.on('click', function(e){
//		var el = $(e.target);
//		if(el.attr('id') == BUT_ENGINE_DIAGNOSTIC){
//			esm.fadeOut();
//			engineListAction.attr('value', ACTION_ENGINE_DIAGNOSTIC);
//			engineListSerial.attr('value', $('input[name=etalonId]:checked', '#enginesList').data('engineserial'));
//			enginesListForm.submit();
//		} else if(el.attr('id') == BUT_ENGINE_DEL){
//			esm.fadeOut();
//			engineListAction.attr('value', ACTION_ENGINE_DEL);
//			engineListSerial.attr('value', $('input[name=etalonId]:checked', '#enginesList').data('engineserial'));
//			enginesListForm.submit();
//		}
//	});
////======================================================	
////BruType menu	
////======================================================
//	bsm.on('click', function(e){
//		var el = $(e.target);
//		if(el.attr('id') == BUT_BRUTYPE_VIEW){
//			bsm.fadeOut();
//			bruTypeListAction.attr('value', ACTION_BRUTYPE_VIEW);
//			bruTypeListForm.submit();
//		} else if(el.attr('id') == BUT_BRUTYPE_ADD){
//			bsm.fadeOut();
//			bruTypeListAction.attr('value', ACTION_BRUTYPE_ADD);
//			bruTypeListForm.submit();
//		} else if(el.attr('id') == BUT_BRUTYPE_EDIT){
//			bsm.fadeOut();
//			bruTypeListAction.attr('value', ACTION_BRUTYPE_EDIT);
//			bruTypeListForm.submit();
//		} else if(el.attr('id') == BUT_BRUTYPE_DEL){
//			bsm.fadeOut();
//			bruTypeListAction.attr('value', ACTION_BRUTYPE_DELETE);
//			bruTypeListForm.submit();
//		}
//	});
//		
////======================================================	
////User menu	
////======================================================
//	usm.on('click', function(e){
//		var el = $(e.target);
//		if(el.attr('id') == BUT_USER_EXIT){
//			usm.fadeOut();
//			
//			var pV = {
//					action: ACTION_USER_LOGOUT,
//			};
//			
//			$.ajax({
//				type: "POST",
//				data: pV,
//				dataType: 'json',
//				url: SCRIPT_ADDR_USER_OPERATION,
//				async: true
//			}).fail(function(data){
//				console.log(data);
//			}).done(function(data){
//				location.reload();
//			});
//		} else if(el.attr('id') == BUT_USER_VIEW){
//			usm.fadeOut();
//			userListAction.attr('value', ACTION_USER_VIEW);
//			userListForm.submit();
//		} else if(el.attr('id') == BUT_USER_ADD){
//			usm.fadeOut();
//			userListAction.attr('value', ACTION_USER_CREATE);
//			userListForm.submit();
//		} else if(el.attr('id') == BUT_USER_EDIT){
//			usm.fadeOut();
//			userListAction.attr('value', ACTION_USER_EDIT);
//			userListForm.submit();
//		} else if(el.attr('id') == BUT_USER_DEL){
//			usm.fadeOut();
//			userListAction.attr('value', ACTION_USER_DELETE);
//			userListForm.submit();
//		}
//	});
//});
