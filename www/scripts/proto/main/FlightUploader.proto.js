var FILE_PROCCESSOR_SRC = location.protocol + '//' + location.host + "/view/fileUploader.php";
	
function FlightUploader(window, document, langStr, srvcStrObj, eventHandler) 
{ 
	this.langStr = langStr;
	this.firstUploadedComplt = false;
	this.updateLegendTimeout = false;
	
	this.plotStack = Object();
	this.plotAxesStack = Object();
	this.plotDatasetStack = Object();
	this.plotSelectedFromRangeStack = Object();
	this.plotSelectedToRangeStack = Object();
	
	this.plotRequests = 0;
	this.plotRequestsClosed = 0;
	
	this.eventHandler = eventHandler;
	this.window = window;
	this.document = document;
	
	this.flightUploaderFactoryContainer = null;
	this.flightUploaderTopMenu = null;
	this.flightUploaderOptions = null;
	this.flightUploaderContent = null;
	
	this.flightUploaderActions = srvcStrObj['uploaderPage'];
	this.sliceActions = srvcStrObj['slicesPage'];
	this.flightFileActions = srvcStrObj['flightsPage'];
}

FlightUploader.prototype.FillFactoryContaider = function(factoryContainer) {
	this.flightUploaderFactoryContainer = factoryContainer;
	
	this.flightUploaderFactoryContainer.append("<div id='flightUploaderTopMenu' class='TopMenu'>" +
				"<label id='convertSelected' class='Up'>" +
					"<span style='position:absolute; margin-top:8px;'>&nbsp;" +
					this.langStr.flightUploaderUpload +
					"</span>" +
				"</label>" +
			"</div>");
	this.flightUploaderFactoryContainer.append("<div id='flightUploaderOptions' class='OptionsMenuFullWidth' style='margin-top:5px;'></div>");
	this.flightUploaderFactoryContainer.append("<div id='flightUploaderContent' class='ContentFullWidth' style='margin-top:5px;'></div>");
	
	this.flightUploaderTopMenu = $("#flightUploaderTopMenu");
	this.flightUploaderOptions = $("#flightUploaderOptions");
	this.flightUploaderContent = $("#flightUploaderContent");
	
	this.ShowFlightUploadingOptions();
	
	this.ResizeFlightUploader();
	this.document.scrollTop(factoryContainer.data("index") * this.window.height());
}

FlightUploader.prototype.ResizeFlightUploader = function(e) {
	var self = this;
	
	if((self.flightUploaderOptions != null) && 
			(self.flightUploaderFactoryContainer != null)){
		self.flightUploaderOptions.css({
			'width': self.flightUploaderFactoryContainer.width(),
			'height': '50px'
		});
	}
	
	if((self.flightUploaderTopMenu != null) && 
			(self.flightUploaderOptions != null) && 
			(self.flightUploaderFactoryContainer != null)){
		self.flightUploaderContent.css({
			'width': self.flightUploaderFactoryContainer.width() - 10,
			"height": self.window.height() - self.flightUploaderOptions.height() - self.flightUploaderTopMenu.height() - 35, //35 because padding and margin
		});
	}
}

FlightUploader.prototype.CaptureUploadingItems = function() { 	
	var self = this;
	
	var fileUploadDialog = $('div#fileUploadDialog').dialog({
		resizable:false,
		autoOpen: false,
		resize: "auto",
		hide: { 
			effect: "fadeOut",  
			duration: 150 
		},
		show: { 
			effect: "fadeIn", 
			duration: 150
		} 
	});
	
	var previewCheckBoxDiv = $("div#previewCheckBoxDiv"),
		bruTypeSelectForUploadingDiv = $("div#bruTypeSelectForUploadingDiv"),
		importInsteadConvert = false;
	//radiobuttons import/convert
	$("div#importConvertRadio").buttonset().change(function(e){
		var el = $(e.target);
		if(el.attr("id") == self.flightFileActions["flightFileConvert"]){
			previewCheckBoxDiv.slideToggle();
			bruTypeSelectForUploadingDiv.slideToggle();
			importInsteadConvert = false;
		} else if(el.attr("id") == self.flightFileActions["flightFileImport"]){
			previewCheckBoxDiv.slideToggle();
			bruTypeSelectForUploadingDiv.slideToggle();
			importInsteadConvert = true;
		}
	});

	$("#uploadTopButt").on("click", function(e){
		$('#progress .progress-bar').css(
            'width',
            0 + '%'
        );
		
		var filesCount = 0;
		
		var url = "fileUploader/";
		
	    $('input#chooseFileBut').fileupload({
	        url: url,
	        dataType: 'json',
	        done: function (e, data) {        	
	        	var selectedBruType = $('select#bruTypeSelectForUploading').find(":selected").text();
	    	        	
	        	if(importInsteadConvert) {
					$.each(data.result.files, function (index, file) {
						$('<p/>').text(file.name).appendTo('#files');
						self.Import(file.name);
						filesCount++;
					});
	        	} else {
		        	if($("input#previewCheckBox:checked").length > 0) {
		        		//show flight info and preview
		        		
		        		self.eventHandler.trigger("uploadWithPreview");
		        		
		        	    $.each(data.result.files, function (index, file) {
		        	        $('<p/>').text(file.name).appendTo('#files');
	    	        		self.GetFlightParams(filesCount, file.name, selectedBruType);       	        	        	        
		        	        filesCount++;       	        
		        	    });
		        	} else { 
		        		//else background uploading
		        		$.each(data.result.files, function (index, file) {
		        	        $('<p/>').text(file.name).appendTo('#files');
		        	        self.EasyUploading(selectedBruType, file.name);        	        
		        	        filesCount++; 
		        	    });
		        	}
	        	}
	        },
	        progressall: function (e, data) {
	            var progress = parseInt(data.loaded / data.total * 100, 10);
	            
	            if(progress >= 100) {
	            	setTimeout(function(){
		            	fileUploadDialog.dialog("close");
	            	}, 300);
	            } else {    
		            $('#progress .progress-bar').css({
		                'width': progress + '%'
		            });
	            }
	        }
	    }).prop('disabled', !$.support.fileInput)
	        .parent().addClass($.support.fileInput ? undefined : 'disabled');
    
	    fileUploadDialog.dialog("option", {
			position: { 
				my: "left top", 
				at: "left bottom", 
				of: $("#uploadTopButt")
			}
		});
	    fileUploadDialog.dialog("open");
	});
};

FlightUploader.prototype.ShowFlightUploadingOptions = function()
{
	if(this.flightUploaderOptions != null){
		var uploaderOptionsStr = "<table v-align='top'><tr><td>" +
				"<label style='line-height: 35px;'>" + this.langStr.flightFilesList + "</label></td>" +
				"</tr></table>";	
		this.flightUploaderOptions.append(uploaderOptionsStr);
	}
}

FlightUploader.prototype.GetFlightParams = function(
		extIndex,
		extFile,
		extSelectedBruType
) { 
	    
	var self = this,
		index = extIndex,
		file = extFile,
		selectedBruType = extSelectedBruType;
    
	if(self.flightUploaderContent != null){
		//when file uploaded call fileProcessor to import it
		var pV = {
					action: self.flightUploaderActions["flightShowUploadingOptions"],
					data: { 
						index: index,
						file: file,
						bruType: selectedBruType
					}
			};
	        
		if((index == 0) || (self.firstUploadedComplt == false)){
	        $.ajax({
				type: "POST",
				data: pV,
				dataType: 'json',
				url: FILE_PROCCESSOR_SRC,
				async: false
			}).fail(function(msg){
				console.log(msg);
			}).done(function(answ) {
				if(answ["status"] == "ok") {
					var flightUploadingProfile = answ["data"]
					self.flightUploaderContent.append(flightUploadingProfile);
					
					var parentContainer = $("div#fileFlightInfo" + index),
						previewParamsRaw = parentContainer.data("previewparams"),
						flightInfoColunmWidth = 450,
						chartWidth = self.window - flightInfoColunmWidth - 30,
						previewParams = Array();

					if(previewParamsRaw.indexOf(";") > -1) {
						previewParams = previewParamsRaw.split(";")
					} else {
						previewParams[0] = previewParamsRaw;
					}
					
					self.PreviewChart(parentContainer, 
							previewParams,
							index,
							file, 
							selectedBruType, 
							chartWidth);
					
					self.SliceFlightButtInitialSupport(parentContainer, previewParams);
					self.firstUploadedComplt = true;
				} else {
					console.log(answ["error"]);
				}
		    });
		} else {
	        $.ajax({
				type: "POST",
				data: pV,
				dataType: 'json',
				url: FILE_PROCCESSOR_SRC,
				async: true
			}).fail(function(msg){
				console.log(msg);
			}).done(function(answ) {
				if(answ["status"] == "ok") {
					//if first uploaded, need to hide FlightView and show FlightUpload content
					//uppending answer
					//mainContainer.append(answ["data"]);
	
					mainContainerPjs.innerHTML = mainContainerPjs.innerHTML + 
						answ["data"];
					
					var parentContainer = $("div#fileFlightInfo" + index),
						previewParamsRaw = parentContainer.data("previewparams"),
						flightInfoColunmWidth = containerWidth / 2.5,
						chartWidth = containerWidth - flightInfoColunmWidth - 30,
						previewParams = Array();
	
					if(previewParamsRaw.indexOf(";") > -1) {
						previewParams = previewParamsRaw.split(";")
					} else {
						previewParams[0] = previewParamsRaw;
					}
					
					self.PreviewChart(parentContainer, 
							previewParams,
							index,
							file, 
							selectedBruType, 
							chartWidth);
					
					self.SliceFlightButtInitialSupport(parentContainer, previewParams);
					
				} else {
					console.log(answ["error"]);
				}
		    });
		}	
	}
};

FlightUploader.prototype.GetSlicedFlightParams = function(
		extIndex,
		extFile,
		extSelectedBruType,
		extParentIndex
) { 
	    
	var self = this,
		index = extIndex,
		file = extFile,
		containerWidth = self.containerWidth,
		selectedBruType = extSelectedBruType,
		parentToAppentAfter = $("div#fileFlightInfo" + extParentIndex);
	
	//when file uploaded call fileProcessor to import it
	var pV = {
		action: self.flightUploaderActions["flightShowUploadingOptions"],
		data: { 
			index: index,
			file: file,
			containerWidth: containerWidth,
			bruType: selectedBruType
		}
	};
        
    $.ajax({
		type: "POST",
		data: pV,
		dataType: 'json',
		url: FILE_PROCCESSOR_SRC,
		async: false
	}).fail(function(msg){
		console.log(msg);
	}).done(function(answ) {
		if(answ["status"] == "ok") {
			//uppending answer
			//mainContainer.append(answ["data"]);

			parentToAppentAfter.after(answ["data"]);
			
			var parentContainer = $("div#fileFlightInfo" + index),
				previewParamsRaw = parentContainer.data("previewparams"),
				flightInfoColunmWidth = containerWidth / 2.5,
				chartWidth = containerWidth - flightInfoColunmWidth - 30,
				previewParams = Array();

			if(previewParamsRaw.indexOf(";") > -1) {
				previewParams = previewParamsRaw.split(";")
			} else {
				previewParams[0] = previewParamsRaw;
			}
			
			self.PreviewChart(parentContainer, 
					previewParams,
					index,
					file, 
					selectedBruType, 
					chartWidth);
			
			self.SliceFlightButtDynamicCreatedSupport(parentContainer, previewParams);

		} else {
			console.log(answ["error"]);
		}
    });
};

//PREVIEW CHART
FlightUploader.prototype.PreviewChart = function (parent, 
		previewParams,
		index, 
		fileName, 
		bruType, 
		chartWidth){
	
	var self = this;
	
	//$("#flightUploaderContent").css("height", self.window.height() - self.optionsMenuHeight - self.topMenuHeight - 35);
	//console.log($("#flightUploaderContent").css("height"));
	
	self.ResizeFlightUploader();
	
	if((previewParams.length > 0) && (previewParams[0] != "")) {
		
		var gCont = parent.find("div#previewChartContainer" + index),
			placeholderSelector = "div#previewChartPlaceholder" + index,
			placeholder = $(placeholderSelector),
			loadingBox = $("div#loadingBox" + index);
		
		self.plotRequests++;
		
		//=============================================================
	
		//=============================================================
		//prepare placeholder and plot
		gCont.css({
			"width": chartWidth + 'px',
			"height": 300 + 'px',
			});
		placeholder.css({
			"width": (gCont.width() - 10) + 'px',
			"height": (gCont.height() - 10) + 'px',
			});
		
		loadingBox.position({
			my: "center",
			at: "center",
			of: gCont
		}).fadeIn();
		
		//=============================================================
	
		//=============================================================
		//flot options
		var options	= {
			xaxis: {
				mode: "time",
				timezone: "utc",
			},
			yaxis:{
				ticks: 0,
				//tickLength: 1,
				position : "right",
				zoomRange: [0,0],			
			},
			crosshair: {
				mode: "x",
			},
			grid: { 
				aboveData: true,
				hoverable: true, 
				clickable: true,
				tickColor: "rgba(255, 255, 255, 0)",
				borderWidth: 1,
				backgroundColor: "#fff",
				markingsLineWidth: 1,
	
			},
			legend: {         
				position: "nw",
			},  
			lines: {
				lineWidth: 1,
			},
			selection: {
				mode: "x"
			}
		};
		
		//=============================================================
		//flot data
		//=============================================================
		var plot = Object(),
			plotAxes = Object(),
			plotDataset = Object(),
		
			pV = {
				action: self.flightUploaderActions["flightUploaderPreview"],
				data: { 
					file: fileName,
					bruType: bruType,
				}
			};
		
		$.ajax({
			type: "POST",
			data: pV,
			dataType: 'json',
			url: FILE_PROCCESSOR_SRC,
			async: true
		}).done(function(apDataArray){
			$("div#loadingBox" + index).remove();
			var prmData = Array(),
				i = 0;
			for (var key in apDataArray)  {
				i++;
				var apDataFlotSeries = {
					data: apDataArray[key],
					label: key + " = 0.00", 
					yaxis: i,
					shadowSize: 0, 
					lines: { lineWidth: 1, show: true }
				};
				prmData.push(apDataFlotSeries);
			}
			
			self.plotStack[index] = $.plot(placeholderSelector, prmData, options);
			self.plotAxesStack[index] = self.plotStack[index].getAxes();
			self.plotDatasetStack[index] = self.plotStack[index].getData();
			
			self.plotRequestsClosed++;
			
		}).fail(function(mess){
			console.log(mess);
		});
		
		var updateLegendTimeout = false,
			curPos = Array();
		
		$("div.PreviewChartPlaceholder").on('plothover', function (event, pos, item) { 
			var legendParent = $(event.target),
				legendParentId = legendParent.attr("id"),
				curIndex = legendParent.data("index");
			
			//if chart already ploted
			if(self.plotDatasetStack[curIndex] != null){	
				if (!self.updateLegendTimeout) {
					self.updateLegendTimeout = true;
					setTimeout(function() {
												
						var values = self.GetValue(previewParams, 
								self.plotDatasetStack[curIndex], 
								pos.x);
						
						self.UpdateLegend(previewParams, 
								legendParentId,
								self.plotDatasetStack[curIndex],
								self.plotAxesStack[curIndex], 
								pos, 
								values);
					}, 200);
				}	
				
				curPos = pos;
			}
		});
		
		//====================================================
		//flot selection
		//====================================================
		$("div.PreviewChartPlaceholder").on("plotselected", function (event, ranges) {
			var parent = $(event.target),
			curIndex = parent.data("index");
			
			self.plotSelectedFromRangeStack[curIndex] = ranges.xaxis.from.toFixed(0);
			self.plotSelectedToRangeStack[curIndex]  = ranges.xaxis.to.toFixed(0);
		});
	}
}

FlightUploader.prototype.UpdateLegend = function(previewParams, 
		placeholderSelector, dataset, plotAxes, pos, valuesArr) 
{	
	this.updateLegendTimeout = false;
	//update each time legends because it can be lost after zoom or pan
	var legndLabls = $("#" + placeholderSelector + " .legendLabel");
	
	if (pos.x < plotAxes.xaxis.min || pos.x > plotAxes.xaxis.max ||
		pos.y < plotAxes.yaxis.min || pos.y > plotAxes.yaxis.max) {
		return;
	}
	//update legend only for ap
	for (var i = 0; i < previewParams.length; ++i) {
		var series = dataset[i],
			y = valuesArr[i],				
			s = series.label.substring(0, series.label.indexOf('='));
		legndLabls.eq(i).text(s + " = " + Number(y).toFixed(2));
	}
	this.updateLegendTimeout = false;
};

//Get value by x coord by interpolating
FlightUploader.prototype.GetValue = function(previewParams, dataset, x) {	
	var yArr = Array();
	for (var i = 0; i < previewParams.length; i++) {
		var series = dataset[i];

		// Find the nearest points, x-wise
		for (var j = 0; j < series.data.length; j++) {
			if (series.data[j][0] > x) {
				break;
			};
		}
		
		// Now Interpolate
		var y,
			p1 = series.data[j - 1],
			p2 = series.data[j];
	
		if ((p1 == null) && (p2 != null)) {
			y = Number(p2[1]);
		} else if ((p1 != null) && (p2 == null)) {
			y = Number(p1[1]);
		} else if ((p1 != null) && (p2 != null)) {
			p1[0] = Number(p1[0]);
			p1[1] = Number(p1[1]);
			p2[0] = Number(p2[0]);
			p2[1] = Number(p2[1]);
			posX = Number(x);
			y = p1[1] + (p2[1] - p1[1]) * 
				(posX - p1[0]) / (p2[0] - p1[0]);
		} else {
			y = 0;			
		}
		
		yArr.push(Number(y).toFixed(2));			
	}
	return yArr;
};

///
//SLICE FLIGHT BUTTON
///
FlightUploader.prototype.SliceFlightButtInitialSupport = function(parent, previewParams) {	
	var self = this;
	
	///
	//UPLOAD SELECTED BUTTON
	///
	var convertSelected = $("label#convertSelected");
	convertSelected.click(function(event) {
		event.preventDefault();
		
		if(self.plotRequests == self.plotRequestsClosed){
			var flightsContainers = $("div.MainContainerContentRows");
			
			$.each(flightsContainers, function(counter, el){
				var $el = $(el),
					fileName = $el.data("filename"),
					bruType = $el.data("brutype"),
					index = $el.data("index"),
					ignoreDueUploading = $el.find("#ignoreDueUploading" + index),
					flightInfo = new Array(),
					flightAditionalInfo = new Array(),
					flightInfoCells = $el.find("input.FlightUploadingInputs"),
					flightAditionalInfoCells = $el.find("input.FlightUploadingInputsAditionalInfo");
				
				if((ignoreDueUploading.prop('checked') == false) && 
						(!ignoreDueUploading.attr('checked'))) {
					$.each(flightInfoCells, function(j, subEl){
						var $subEl = $(subEl);
						if($subEl.attr('type') == 'checkbox'){
							if($subEl.prop('checked')){
								flightInfo.push($subEl.attr('id'));	
								flightInfo.push(1);	
							} else {
								flightInfo.push($subEl.attr('id'));	
								flightInfo.push(0);	
							}
						} else {
							flightInfo.push($subEl.attr('id'));	
							flightInfo.push($subEl.val());			
						}
					});
					
					$.each(flightAditionalInfoCells, function(j, subEl){
						var $subEl = $(subEl);
						flightAditionalInfo.push($subEl.attr('id'));	
						flightAditionalInfo.push($subEl.val());				
					});
					
					//if no aditional info set it to zero
					if(flightAditionalInfo.length == 0){
						flightAditionalInfo = 0;
					}
					
					var flightConvertionAction = self.flightUploaderActions["flightProcces"],
						tempFileName = guid() + "_tempStatus.json",
						performProc = $el.find("input#execProc").prop('checked'),
						etalonIdToCompare = 
							$el.find("select.FlightUploadingInputs :selected").data("sliceid");	
					
					if(performProc == true){
						if((etalonIdToCompare == self.sliceActions['etalonDoNotCompare'])){
							flightConvertionAction = self.flightUploaderActions["flightProccesAndCheck"];
						} else {
							flightConvertionAction = self.flightUploaderActions["flightProccesCheckAndCompareToEtalon"];
						}
					}
									
					var pV = {
							'action': flightConvertionAction,
							'data': {
								'bruType': bruType,
								'fileName': fileName,
								'tempFileName': tempFileName,
								'flightInfo': flightInfo,
								'flightAditionalInfo' : flightAditionalInfo
							}					
						};
					
					self.InitiateFlightProccessing(pV);
				}
				$el.remove();
			});
			
			self.mainContainerOptions = $("div.MainContainerOptions");
			//fire to index event to show flight list
			self.mainContainerOptions.slideUp(function(e){
				self.mainContainerOptions.empty();
				self.eventHandler.trigger("convertSelectedClicked");	
			});
		}
		
		self.eventHandler.trigger("removeShowcase", self.flightUploaderFactoryContainer);
	});

	///
	//SLICE FILE BUTTONS
	///
	if((previewParams.length > 0) && (previewParams[0] != "")) {
		
		var butEl = $("button.SliceFlightButt, button.SliceCyclicFlightButt");
		
		$.each(butEl, function(counter, el){
			var $el = $(el);
			if($el.attr("role") == undefined){
				var button = $el.button().first().css({
					'padding-top': '0px !important'
				});
			}	
		});

		$("button.SliceFlightButt, button.SliceCyclicFlightButt").on("click", function(e) {
			e.preventDefault();
			
			//if all charts ploted
			if(self.plotRequests == self.plotRequestsClosed){
				
				var el = $(e.target).parent(),
					curIndex = el.data("index"),
					fileName = el.data("file"),
					bruType = el.data("brutype"),
					newIndex = $("div.PreviewChartPlaceholder").length,
					action = self.flightUploaderActions["flightCutFile"];
							
				if((self.plotSelectedFromRangeStack[curIndex] != undefined) && 
						(self.plotSelectedToRangeStack[curIndex] != undefined)){
					
					$("input#ignoreDueUploading" + curIndex).prop('checked', true);
					
					if(el.hasClass('SliceFlightButt')){
						action = self.flightUploaderActions["flightCutFile"];
					} else if(el.hasClass('SliceCyclicFlightButt')){
						action = self.flightUploaderActions["flightCyclicSliceFile"];
					}
										
					var pV = {
							action: action,
							data: {
								bruType: bruType,
								file: fileName,
								
								startCopyTime: self.plotAxesStack[curIndex].xaxis.min, 
								endCopyTime: self.plotAxesStack[curIndex].xaxis.max, 
								startSliceTime: self.plotSelectedFromRangeStack[curIndex],
								endSliceTime:  self.plotSelectedToRangeStack[curIndex]
							}
						};
					
					$.ajax({
						type: "POST",
						data: pV,
						dataType: 'json',
						url: FILE_PROCCESSOR_SRC,
						async: true
					}).done(function(answ){
						if(answ["status"] == 'ok') {
							var newFileName = answ["data"];
							
							self.GetSlicedFlightParams(newIndex,
									newFileName,
									bruType,
									curIndex);						
						} else {
							console.log(answ["error"]);
						}
					}).fail(function(mess){
						console.log(mess);
					});
					
				}
			}
		});
	}
};

FlightUploader.prototype.SliceFlightButtDynamicCreatedSupport = function(parent, previewParams) {	
	
	var self = this;

	if((previewParams.length > 0) && (previewParams[0] != "")) {
		
		var appendedButt = parent.find("button.SliceFlightButt, button.SliceCyclicFlightButt");
	
		if(appendedButt.attr("role") == undefined){
			var button = appendedButt.button().first().css({
				'padding-top': '0px !important'
			});
		}

		appendedButt.on("click", function(e) {
			event.preventDefault();
			
			//if all charts ploted
			if(self.plotRequests == self.plotRequestsClosed){
				var el = $(e.target).parent(),
					curIndex = el.data("index"),
					fileName = el.data("file"),
					bruType = el.data("brutype"),
					newIndex = $("div.PreviewChartPlaceholder").length,
					action = self.flightUploaderActions["flightCutFile"];
				
				if((self.plotSelectedFromRangeStack[curIndex] != undefined) && 
						(self.plotSelectedToRangeStack[curIndex] != undefined)){
					
					$("input#ignoreDueUploading" + curIndex).prop('checked', true);
	
					if(el.hasClass('SliceFlightButt')){
						action = self.flightUploaderActions["flightCutFile"];
					} else if(el.hasClass('SliceCyclicFlightButt')){
						action = self.flightUploaderActions["flightCyclicSliceFile"];
					}
					
					var pV = {
							action: action,
							data: {
								bruType: bruType,
								file: fileName,
								
								startCopyTime: self.plotAxesStack[curIndex].xaxis.min, 
								endCopyTime: self.plotAxesStack[curIndex].xaxis.max, 
								startSliceTime: self.plotSelectedFromRangeStack[curIndex],
								endSliceTime:  self.plotSelectedToRangeStack[curIndex]
							}
						};
					
					$.ajax({
						type: "POST",
						data: pV,
						dataType: 'json',
						url: FILE_PROCCESSOR_SRC,
						async: true
					}).done(function(answ){
						if(answ["status"] == 'ok') {
							var newFileName = answ["data"];
							
							self.GetSlicedFlightParams(newIndex,
									newFileName,
									bruType,
									curIndex);						
						} else {
							console.log(answ["error"]);
						}
					}).fail(function(mess){
						console.log(mess);
					});
				}
			}
		});
	}
};

FlightUploader.prototype.InitiateFlightProccessing = function(postValues) {	
	var self = this, 
		pV = postValues, 
		eventInfo = {
			'bruType': pV["data"]["bruType"],
			'fileName': pV["data"]["fileName"],
			'tempFileName': pV["data"]["tempFileName"]		
		};
	
	$.ajax({
		type: "POST",
		data: pV,
		dataType: 'json',
		url: FILE_PROCCESSOR_SRC,
		async: true
	}).done(function(answ){
		if(answ["status"] == 'ok') {
			var fileComplName = answ["data"];
			self.eventHandler.trigger("endProccessing", fileComplName);			
		} else {
			console.log(answ["error"]);
		}
	}).fail(function(mess){
		console.log(mess);
	});
	
	self.eventHandler.trigger("startProccessing", eventInfo);	
}

///
//EasyUploading
///
FlightUploader.prototype.EasyUploading = function(
		bruType,
		fileName
) { 
	
		flightConvertionAction = self.flightUploaderActions["flightEasyUpload"],
		tempFileName = guid() + "_tempStatus.json";
	
	var pV = {
			'action': flightConvertionAction,
			'data': {
				'bruType': bruType,
				'fileName': fileName,
				'tempFileName': tempFileName,
			}					
		};
	
	self.InitiateFlightProccessing(pV);
}

///
//Import
///
FlightUploader.prototype.Import = function(
		file
) { 
	var self = this, 
	pV = {
		'action': self.flightUploaderActions["itemImport"],
		'data': {
			'file': file
		}	
	};
	
	$.ajax({
		type: "POST",
		data: pV,
		dataType: 'json',
		url: FILE_PROCCESSOR_SRC,
		async: true
	}).done(function(answ){
		if(answ["status"] == 'ok') {
			location.reload();
		} else {
			console.log(answ["error"]);
		}
	}).fail(function(mess){
		console.log(mess);
	});
}


//=============================================================
function s4() {
  return Math.floor((1 + Math.random()) * 0x10000)
             .toString(16)
             .substring(1);
};
//=============================================================

//=============================================================
function guid() {
  return this.s4() + this.s4() + '_' + this.s4() + '_' + this.s4() + '_' +
         this.s4() + '_' + this.s4() + this.s4() + this.s4();
};


