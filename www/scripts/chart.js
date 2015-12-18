jQuery(function($) { $(document).ready(function() {
	
	var $document = $(document),
		$window = $(window),
		userLang = $('html').attr("lang"),
		eventHandler = $('#eventHandler');
	
	var LA = new Language(userLang),
	C = null;
	
	LA.GetLanguage().done(function(data){
		var langStr = data;
		LA.GetServiceStrs().done(function(data){
			var srvcStrObj = data;

			C = new Chart($window, $document, langStr, srvcStrObj, eventHandler);	
			
			var flightId = $("#flightId").text(), 
				tplName = $("#tplName").text(), 
				stepLength = $("#stepLength").text(), 
				startCopyTime = $("#startCopyTime").text(), 
				startFrame = $("#startFrame").text(), 
				endFrame = $("#endFrame").text(),
				apParams = $("#apParams").text().split(","), 
				bpParams = $("#bpParams").text().split(",");
			
			var showcase = $window;
			
			if(C != null) {
				C.SetChartData(flightId, tplName,  
						stepLength, startCopyTime, startFrame, endFrame,
						apParams, bpParams);
				
				C.chartFactoryContainer = showcase;
				
				C.chartWorkspace = $('div#chartWorkspace');
				C.chartContent = $('div#graphContainer');
				
				C.loadingBox = $("div#loadingBox").css("top", $window.height() / 2 - 40);
				C.legend = $('div#legend');
				C.placeholder = $('div#placeholder');
				
				setInitialChartSize.apply(C);
				C.LoadFlotChart();
				
				C.chartWorkspace.resizable().resize(function(){
					var interval = setInterval(function(){
						ResizeChart.apply(C);
						C.plot.pan(0);
						clearInterval(interval);
					}, 1000);
				});
			}
		});
	});

	function setInitialChartSize(){
		this.chartWorkspace.css({
			"top": 0,
			"left": 0,
			"height": this.window.height() - 25,
			"width": this.window.width() - 25
		});
		ResizeChart.apply(this);
	}
	
	function ResizeChart(){
		this.chartContent.css({
			"top": 0,
			"left": 0,
			"width" : this.chartWorkspace.width(),
			"height": this.chartWorkspace.height()
		})
		
		if((this.chartContent != null) && 
				(this.placeholder != null) && 
				(this.legend != null) &&
				(this.apParams != null) && 
				(this.bpParams != null)){
			
			this.placeholder.css({
				"margin-top": '30px',
				"width": this.chartContent.width() - LEGEND_CONTAINER_OUTER + 'px',
				"height": this.chartContent.height() - 35 + 'px'
				});
			this.legend.css({
				"margin-top": '35px',
				"width": LEGEND_CONTAINER_OUTER + "px",
				"height": this.placeholder.height() - 25 + 'px'
			});
		
			this.placeholder.css("width",  (this.chartContent.width() - (this.legend.width() + 30) + 
				(this.apParams.length + this.bpParams.length) * 18) + "px");
			
			if((this.apParams.length == 1) && (this.bpParams.length == 0)){
				this.placeholder.css("margin-left",  "-7px");	
			} else {
				this.placeholder.css("margin-left",  "-" + 
					((this.apParams.length + this.bpParams.length - 1) * 18) + "px");	
			}
		}
	}

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
