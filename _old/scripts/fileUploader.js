var FLIGHT_CONVERT = 'conv',
	FLIGHT_PROC = 'proc',
	FLIGHT_DEL_TEMP = 'deltmp',
	FLIGHT_GET_CUR_ID = 'getId',
	FLIGHT_COMPARE_TO_ETALON = 'compare',
	ETALON_DO_NOT_COMPARE = 'donotcompare',
	
	UPLOADER_PREVIEW = 'preview',
	UPLOADER_SLICE = 'slice',
	
	UPLOADER_TO_MAIN = 'toMain',
	UPLOADER_TO_TUNER = 'toTuner',
	UPLOADER_TO_CHART = 'toChart',
	UPLOADER_TO_DIAGNOSTIC = 'toDiagnostic',
	
	ENGINE_DIAGNOSTIC = 'engineDiagnostic',
	
	INDEX_PAGE = 'index.php',
	TUNER_PAGE = 'tuner.php',
	CHART_PAGE = 'chart.php',
	DIAGNOSTIC_PAGE = 'diagnostic.php',
	
	scriptAddrCopyPreview = location.protocol + '//' + location.host + "/asyncCopyPreview.php",
		
	LANG_FILE =  location.protocol + '//' + location.host + "/lang/" + "RU.lang",
	LANG_FILE_DEFAULT =  location.protocol + '//' + location.host + "/lang/" + "Default.lang";


$(document).ready(function(){
	
	var lang = Object();
	
	$.ajax({
		url: LANG_FILE,
		dataType: 'json',
		async: false,
		success: function(data) {
			lang = data;
		}
	}).fail(function() {
		$.ajax({
			url: LANG_FILE_DEFAULT,
			dataType: 'json',
			async: false,
			success: function(data) {
				lang = data;
			}
		});
	});

	var messBox = $("div#dialog").dialog({
		resizable: false,
		modal: true,
		autoOpen: false,
		draggable: true,
		buttons: [{
            text: lang.continueLabel,
            click: function() {
              $( this ).dialog( "close" );
        }}],
		width: 400
	});

	var flightUploadingButt = $("input#submitInputFlightInfoButt"),
		sliceFlightButt = $("input#sliceFlightButt"),
		totalFileSize = $("input#fileSize").attr("value"),
		filePath = $("input#filePath").attr("value"),
		enginesArr = [],
		processedFileNum = 0,
		filesArr = [],
		flightId = 0,
		sliceId = 0,
		
		dialogText = $("div#dialog p"),
		progressbar = $("div#progressbar"),
		
		copyCreationTime = $("input#copyCreationTime"),
		copyCreationDate = $("input#copyCreationDate"),
		aditionalInfo = $("input.FlightUploadingInputsAditionalInfo"),

		scriptHeaderReader = location.protocol + '//' + location.host + "/asyncHeaderReader.php",
		scriptAddrUploadFile = location.protocol + '//' + location.host + "/asyncFileProcessor.php",
		tempFileName = guid() + "tempStatus.json",
		scriptAddrProgressFile = location.protocol + '//' + location.host + "/uploadedFiles/" + tempFileName;
	
	if(totalFileSize.indexOf(',') !== -1) {
		totalFileSize = totalFileSize.split(",");
		filePath = filePath.split(",");	
		for(var i = 0; i < totalFileSize.length; i++) {
			filesArr[i] = {'size': totalFileSize[i], 'path': filePath[i].replace(/\//g, "\\\\") };
		}
	}
	else {
		filesArr[processedFileNum] = {'size': totalFileSize, 'path': filePath}
	}

	//==============================================	
	//init ProgressBar
	var pb = new ProgressBar(progressbar, filesArr[processedFileNum]["size"], scriptAddrProgressFile, processedFileNum, filesArr.length, lang.waitAnswerText);
	//==============================================
	
	//==============================================	
	//initial header read
	callHeader(filesArr[processedFileNum]);
	//==============================================
				
	flightUploadingButt.on('click', function() {
		var emptyInput = $('input:text[value=""]'),
			engines = '';
		
		if($("input#engines").lenght > 0){
			engines = $("input#engines").val().split(",");
		}
	
		if(emptyInput.length > 0){
			dialogText.text(lang.notAllFieldsMatched);
			messBox.dialog("open");
		} else {
			//disable all fields
			$("input, select").prop('disabled', true)

			//save engine serails
		    $.each(engines, function (idx, val) {
		    	var trimed = $.trim(val);
		    	if($.inArray(trimed, enginesArr) < 0){
		    		enginesArr.push(trimed);
		    	}
		    });
			uploadFile(filesArr[processedFileNum]);
		}
	});
	
	// Listen for the event completeUploading.
	progressbar.on('completeUploading', function (e) {
		//getUploadedFligthId();
		flightId = $("div#progressLabel").data("receivedinfo");
		
		console.log(flightId);	

		cleanUpTempFiles(tempFileName);
		
		var curExecProc = false,
			execProc = $("input#execProc");
		if((execProc.length > 0) && (execProc.attr("checked") == 'checked')) {
			curExecProc = true;
		} else {
			curExecProc = false;
		}
		
		//if user want flight events seach execute, else go next
		if(curExecProc){
			processFlight(flightId);
		}
		else
		{
			progressbar.trigger("completeProcess");			
		}
	});
	
	progressbar.on('completeProcess', function (e) {
		cleanUpTempFiles(tempFileName);
		
		var compareToEtalonTag = $("select#compareToEtalon"),
			selectCompareToEtalon = compareToEtalonTag.children(":selected").data("sliceid"),
			curEngineCompare = false;
		
		//tag exist
		if(compareToEtalonTag.length > 0){
			if(selectCompareToEtalon != ETALON_DO_NOT_COMPARE) {
				curEngineCompare = true;
				sliceId = selectCompareToEtalon;
			} /*else {
				curEngineCompare = false;
			}*/
		} /*else {
			curEngineCompare = false;
		}*/
		
		if(curEngineCompare){
			compateToEtalon(flightId, sliceId);
		} else {
			progressbar.trigger("completeEngineCompare");		
		}
	});
	
	progressbar.on('completeEngineCompare', function (e) {
		cleanUpTempFiles(tempFileName);

		processedFileNum++;
		if(processedFileNum < filesArr.length){
			callHeader(filesArr[processedFileNum]);
			uploadFile(filesArr[processedFileNum]);
		} else {
			cleanUpTempFiles(tempFileName);
			pb.waitAnswerText = lang.completeText;
			pb.CompltProc();

			//action after complete
			var selectRedirVariant = $("select#actionAfterUpload").children(":selected").data("action");
			
			console.log("selectRedirVariant " + selectRedirVariant);

			setTimeout(function(){
				redirBySelectedVariant(selectRedirVariant, flightId, sliceId)
			}, 1500);	
		}
	});
	//=============================================================
	
	//=============================================================
	function callHeader(files) {
		pV = {
			bruType : $('input#bruType').val(),	
			filePath : files['path']
		};

		$.ajax({
			dataType : "json",
			type: "POST",
			url : scriptHeaderReader,
			data : pV,
			async: false,
		}).done(function(flightInfo){
			if(!$.isEmptyObject(flightInfo)) {
				if(flightInfo.startCopyTime != '') {
					var date = flightInfo.startCopyTime.toString();
					copyCreationDate.val(date.slice(9,19));//.attr('readonly', true);
					copyCreationTime.val(date.slice(0,8));//.attr('readonly', true);
				}
				
				for (var key in flightInfo) {
					$("input#" + key).val(flightInfo[key]);
				}
			}
		});
	}
	//=============================================================
	
	//=============================================================
	function uploadFile(files){
		var aditionalInfo = $("input.FlightUploadingInputsAditionalInfo"),
			aditionalInfoVars = String();
			
		for(var i = 0; i < aditionalInfo.length; i++) {
			var curElem = $(aditionalInfo[i]);
			aditionalInfoVars += curElem.attr('id') + ":" + curElem.val() + ";";
		}
		
		var selectedEtalonId = $('select#compareToEtalon').find(":selected").data("sliceId");
		
		tempFileName = guid() + "tempStatus.json",
		scriptAddrProgressFile = location.protocol + '//' + location.host + "/uploadedFiles/" + tempFileName;
		
		pb.framesProc = 0;
		pb.totalFileSize = files["size"];
		pb.processedFileNum = processedFileNum;
		pb.scriptAddrProgressFile = scriptAddrProgressFile;
		pb.trigger = "completeUploading";
	
		//postValues
		var pV = {
			bruType : $('input#bruType').val(),
			bort : $('input#bort').val(),
			voyage : $('input#voyage').val(),
			departureAirport : $('input#departureAirport').val(),
			arrivalAirport : $('input#arrivalAirport').val(),
			copyCreationTime : $('input#copyCreationTime').val(),
			copyCreationDate : $('input#copyCreationDate').val(),
			performer : $('input#performer').val(),
			aditionalInfo : aditionalInfoVars,
			uploadedFile: files["path"],
			tempFileName: tempFileName,
			action: FLIGHT_CONVERT			
		};
		
		$.post(scriptAddrUploadFile, pV).fail(function(mess){
			console.log("Post fail " + mess);
		});
		
		pb.ShowProgressFromServer();
	}
	//=============================================================
	
	//=============================================================
	//not use flight id receives by 
	/*function getUploadedFligthId(){	
		//postValues
		var pV = {
			bruType : $('input#bruType').val(),
			bort : $('input#bort').val(),
			voyage : $('input#voyage').val(),
			copyCreationTime : $('input#copyCreationTime').val(),
			copyCreationDate : $('input#copyCreationDate').val(),
			performer : $('input#performer').val(),
			action: FLIGHT_GET_CUR_ID			
		};
		
		$.ajax({
			dataType : "json",
			type: "POST",
			url : scriptAddrUploadFile,
			data : pV,
			async: false,
		}).done(function(receivedFlightId){
			flightId = receivedFlightId;
		});
	}*/
	//=============================================================
	
	//=============================================================
	function processFlight(flightId) {
		tempFileName = guid() + "tempStatus.json";
		scriptAddrProgressFile = location.protocol + '//' + location.host + "/uploadedFiles/" + tempFileName;
		
		pb.framesProc = 0;
		pb.processedFileNum = processedFileNum;
		pb.scriptAddrProgressFile = scriptAddrProgressFile;
		pb.trigger = "completeProcess";
	
		//postValues
		var pV = {
			flightId : flightId,
			tempFileName: tempFileName,
			action: FLIGHT_PROC			
		};
		
		$.post(scriptAddrUploadFile, pV).fail(function(mess){
			console.log("Post fail " + mess);
		});
		
		pb.ShowProgressLabelsReceiving();
	}
	//=============================================================
	
	//=============================================================
	function compateToEtalon(flightId, sliceId) {	
		tempFileName = guid() + "tempStatus.json";
		scriptAddrProgressFile = location.protocol + '//' + location.host + "/uploadedFiles/" + tempFileName;
		
		pb.framesProc = 0;
		pb.processedFileNum = processedFileNum;
		pb.scriptAddrProgressFile = scriptAddrProgressFile;
		pb.trigger = "completeEngineCompare";
	
		//postValues
		var pV = {
			flightId : flightId,
			sliceId : sliceId,
			tempFileName: tempFileName,
			action: FLIGHT_COMPARE_TO_ETALON			
		};
		
		$.post(scriptAddrUploadFile, pV).fail(function(mess){
			console.log("Post fail " + mess);
		});
		
		pb.ShowProgressLabelsReceiving();
	}
	
	function cleanUpTempFiles(tempFileName){
		var pV = {
				tempFileName : tempFileName,
				action: FLIGHT_DEL_TEMP			
			};
			
		$.post(scriptAddrUploadFile, pV).fail(function(mess){
			console.log("Post fail " + mess);
		});
	}
	
	function redirBySelectedVariant(redirVariant, flightId, sliceId){
		
		var redirectForm = $("form#redirectForm"),
			redirOption1 = $("form#redirectForm input#option1"),
			redirOption2 = $("form#redirectForm input#option2"),
			redirOption3 = $("form#redirectForm input#option3");
		
		//printf("<form id='redirectForm' action='fileUploader.php' method='post' enctype='multipart/form-data' style='visibility: hidden'>
		//		<input id='option1' name='radioBut'/>
		//		<input id='option2' name='radioBut'/>
		//		</form>");
		console.log("redirVariant " + redirVariant);
		
		switch(redirVariant){
			case UPLOADER_TO_MAIN:
				location.href = location.protocol + '//' + location.host + '/' + INDEX_PAGE;
				break;
				
			case UPLOADER_TO_TUNER:
				redirectForm.attr("action", TUNER_PAGE);
				redirOption1
					.attr("name", "radioBut")
					.prop('disabled', false)
					.attr("value", flightId);
				redirectForm.submit();
				break;
				
			case UPLOADER_TO_CHART:
				redirectForm.attr("action", CHART_PAGE);
				redirOption1
					.attr("name", "radioBut")
					.prop('disabled', false)
					.attr("value", flightId);
				console.log("flightId " + flightId);
				
				redirectForm.submit();
				break;
				
			case UPLOADER_TO_DIAGNOSTIC:
				redirectForm.attr("action", DIAGNOSTIC_PAGE);
				redirOption1
					.attr("name", "etalonId")
					.prop('disabled', false)
					.attr("value", sliceId)
				
				redirOption2
				.attr("name", "engines")
				.prop('disabled', false)
				.attr("value", enginesArr.join(","));

				redirOption3
					.attr("name", "engineAction")
					.prop('disabled', false)
					.attr("value", ENGINE_DIAGNOSTIC);
				redirectForm.submit();
				break;
		}	
	}
	
	//PREVIEW CHART
	var previewParams = $("input#previewParams").val();
	
	if(previewParams != '') {
		var LEGEND_CONTAINER_WIDTH = 170,
			LEGEND_CONTAINER_OUTER = 215;
		
		var $window = $(document),
			gCont = $("div#previewChartContainer"),
			placeholder = $("div#previewChartPlaceholder"),
			legend = $("div#previewChartLegend"),
			
			loadingBox = $("div#loadingBox"),
			
			bruType = $("input#bruType").val(),
			filePathes = $("input#filePath").val();
	
		var previewParams = previewParams.split(";"),
			filePathes = filePathes.split(",");
		
		//=============================================================
	
		//=============================================================
		//prepare placeholder and plot
		gCont.css({
			"width": $window.width() - 25 + 'px',
			"height": 300 + 'px',
			});
		placeholder.css({
			"height": gCont.height() - 35 + 'px'
			});
		legend.css({
			//"margin-top": '35px',
			"width": LEGEND_CONTAINER_WIDTH + 'px',
			"height": placeholder.height() - 25 + 'px'
		});
	
		placeholder.css("width",  ($window.width() - LEGEND_CONTAINER_OUTER + 
			(previewParams.length) * 18) + "px");
		placeholder.css("margin-left",  "-" + 
			((previewParams.length - 1) * 18) + "px");	
		
		loadingBox.css({
			'position': 'absolute',
			'top': gCont.offset().top + gCont.height() / 2 - 40+ 'px',
			'left':  ($window.width() / 2 - 30) + 'px'
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
				position : "left",
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
				container: legend,            
				noColumns: 1,
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
			plotYaxArr = Object(),
			plotAxes = Object(),
			plotDataset = Object(),
		
			pV = {
				fileName: filePath,
				bruType: bruType,
				action: UPLOADER_PREVIEW
			};
		
		$.ajax({
			type: "POST",
			data: pV,
			dataType: 'json',
			url: scriptAddrCopyPreview,
			async: true
		}).done(function(apDataArray){
			loadingBox.fadeOut();
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
			plot = $.plot(placeholder, prmData, options);
			
			plotYaxArr = plot.getYAxes();
			plotAxes = plot.getAxes();
			plotDataset = plot.getData();
			
		}).fail(function(mess){
			console.log(mess);
		});
		
		var updateLegendTimeout = false,
			curPos = Array();
		
		placeholder.on('plothover', function (event, pos, item) { 
			//label
			if (!updateLegendTimeout) {
				updateLegendTimeout = true;
				setTimeout(function() {
					var values = GetValue(plotDataset, pos.x);
					UpdateLegend(pos, values);
				}, 200);
			}	
			
			curPos = pos;
		});
		
		function UpdateLegend(pos, valuesArr) 
		{
			updateLegendTimeout = false;
			//update each time legends because it can be lost after zoom or pan
			var legndLabls = legend.find(".legendLabel");
			
			if (pos.x < plotAxes.xaxis.min || pos.x > plotAxes.xaxis.max ||
				pos.y < plotAxes.yaxis.min || pos.y > plotAxes.yaxis.max) {
				return;
			}
			//update legend only for ap
			for (var i = 0; i < previewParams.length; ++i) {
				var series = plotDataset[i],
					y = valuesArr[i],				
					s = series.label.substring(0, series.label.indexOf('='));
				legndLabls.eq(i).text(s + " = " + Number(y).toFixed(2));
			}
			updateLegendTimeout = false;
		};
		
		//Get value by x coord by interpolating
		function GetValue(dataset, x) {	
			var yArr = Array();
			for (var i = 0; i < previewParams.length; ++i) {
				var series = dataset[i];
				// Find the nearest points, x-wise
				for (var j = 0; j < series.data.length; ++j) {
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
		
		//====================================================
		//flot selection
		//====================================================
		var slicedFlightsForm = $("form#slicedFlightsForm"),
			slicedUploadingFile = $("input#slicedUploadingFile"),
			slicedBruType = $("input#slicedBruType"),
			fromRange = 0,
			toRange = 0;
		
		placeholder.on("plotselected", function (event, ranges) {
			fromRange = ranges.xaxis.from.toFixed(0);
			toRange = ranges.xaxis.to.toFixed(0);
		});
		
		sliceFlightButt.on('click', function() {
			var pV = {
					fileName: filePath,
					action: UPLOADER_SLICE,
					bruType: bruType,
					startCopyTime: plotAxes.xaxis.min, 
					endCopyTime: plotAxes.xaxis.max, 
					startSliceTime: fromRange,
					endSliceTime:  toRange
				};
			
			console.log(pV);
			
			$.ajax({
				type: "POST",
				data: pV,
				dataType: 'json',
				url: scriptAddrCopyPreview,
				async: true
			}).done(function(newFileName){
				if(newFileName != 'err') {
					slicedUploadingFile.val(newFileName);
					slicedBruType.val(bruType);
					slicedFlightsForm.submit();
				} else {
					console.log(newFileName);
				}
			}).fail(function(mess){
				console.log(mess);
			});
		});
	}
	
});

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
