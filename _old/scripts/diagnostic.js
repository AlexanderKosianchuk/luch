var ASYNC_DIAGNOSTIC_SERVER_SCR = "asyncDiagnosticServer.php",
	GET_ETALON_ENGINES = "getEtalonEngines",
	GET_ENGINE_SLICES = "getEngineSlices",
	GET_ENGINE_DISCREP = "getEngineDiscrep",
	GET_DISCREP_VALS = "getDiscrepVals",
	GET_DISCREP_LIMITS = "getDiscrepLimits",
	GET_DISCREP_REPORT = "getReport",
	DIAGNOSTIC_ABSCISSA_FLIGHTS = "flights",
	DIAGNOSTIC_IGNORE_ETALON = "ignore",
	
	LANG_FILE =  location.protocol + '//' + location.host + "/lang/" + "RU.lang",
	LANG_FILE_DEFAULT =  location.protocol + '//' + location.host + "/lang/" + "Default.lang";
	
	//DIAGNOSTIC_ACTION = "action",
	//DIAGNOSTIC_ENGINE_SERIAL = "engineSerial",
	//DIAGNOSTIC_SLICE_LIST = "sliceList",
	//DIAGNOSTIC_ABSCISSA = "abscissa",
	//DIAGNOSTIC_ORDINATE = "ordinate";
	//DIAGNOSTIC_DISCREP = "discrep";

$(document).ready(function(){	
	//==================================================================
	//Get language object
	//==================================================================
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
	
    $( "#accordion" ).accordion({
        heightStyle: "content"
      });
    
	//===============================================
	//report tab
	//===============================================
    
    var dialogText = $("div#dialog p"),
    	tableReport = $("table.ReportTable"),
    	etalonListReport = $("select#etalonListReport"),
    	engineSerialListReport = $("select#engineSerialListReport"),
    	limitsReport = $("select#limitsReport"),
    	fromDate = $("input#fromDate"),
    	toDate = $("input#toDate"),
    	reportMessage = $("div#reportTableMessage");
    
    fromDate.datepicker();
    toDate.datepicker();  
    
    UpdateTable();
    
    etalonListReport.on('change', function(e){
    	UpdateReportEngineList();
    	UpdateTable();
    });
    
	engineSerialListReport.on('change', function(e){
		UpdateTable();
    });
	
	limitsReport.on('change', function(e){
		UpdateTable();
    });
	
	fromDate.on('change', function(e){
		var curFromDate = fromDate.val();
		
		if(curFromDate != "") {
			if(IsValidDate(curFromDate)) {
				UpdateTable();
			} else {
				dialogText.text(lang.incorrectDataFormat);
				messBox.dialog("open");
				fromDate.attr("value", "");
				curFromDate = fromDate.val();
			}	
		}
		
		UpdateTable();
    });
	
	toDate.on('change', function(e){
		var curToDate = toDate.val();
		
		if(curToDate != "") {
			if(IsValidDate(curToDate)) {
				UpdateTable();
			} else {
				dialogText.text(lang.incorrectDataFormat);
				messBox.dialog("open");
				toDate.attr("value", "");
				curToDate = toDate.val();
			}	
		}
		
		UpdateTable();
    });
	
	function UpdateReportEngineList(curEtalonId){
		var curAction = GET_ETALON_ENGINES,
			etalonId = etalonListReport.find(":selected").data("etalonid");

		var postData = {
			action: curAction,
			etalonId: etalonId
		};
		
		var jqxhr = $.ajax({
			type: "POST",
			url: ASYNC_DIAGNOSTIC_SERVER_SCR,
			data: postData,
			dataType: "json",
			async: false
		}).always(function(receivedData){	
			engineSerialListReport.empty();
			for(var i = 0; i < receivedData.length; i++){
				engineSerialListReport.append("<option>" + receivedData[i] + "</option>");
			}	
			engineSerialListReport.children(":first").attr("selected", "true");
		});
	}
	
	function UpdateTable() {
		ClearTable();
		
		var curAction = GET_DISCREP_REPORT,
			etalonId = etalonListReport.find(":selected").data("etalonid"),
			curEngineSerialArr = engineSerialListReport.find(":selected"),
			enginesArr = [],
			limitsReportArr = limitsReport.find(":selected"),
			limitsArr = [],
			curFromDate = fromDate.val(),
			curToDate = toDate.val();
		
		for(var i = 0; i < curEngineSerialArr.length; i++){
			enginesArr.push($(curEngineSerialArr[i]).val());
		}
		
		for(var i = 0; i < limitsReportArr.length; i++){
			limitsArr.push($(limitsReportArr[i]).data("type"));
		}
		
		var postData = {
			action: curAction,
			etalonId: etalonId,
			engineSerial: enginesArr,
			fromDate: curFromDate,
			toDate: curToDate,
			type: limitsArr
		};
		
		var jqxhr = $.ajax({
			type: "POST",
			url: ASYNC_DIAGNOSTIC_SERVER_SCR,
			data: postData,
			dataType: "json"
		}).always(function(receivedData){	
			console.log(receivedData);
			for(var i = 0; i < receivedData.length; i++){
				var rgb = colourHexToRgb(
						colourNameToHex(
								rainbow(receivedData[i]["limitType"] * -1)
						)
				);
				var rowColor = "style='background-color: rgb(" + rgb.r + "," + rgb.g + "," + rgb.b + ")" + ";" +
					"background-color: rgba(" + rgb.r + "," + rgb.g + "," + rgb.b + "," + "0.3)" + ";'";
				var row = "<tr " + rowColor + "><td>"+ receivedData[i]["flightDate"] +"</td>" +
					"<td>"+ receivedData[i]["etalonId"] +"</td>" +
					"<td>"+ receivedData[i]["engineSerial"] +"</td>" +
					"<td>"+ receivedData[i]["sliceCode"] +"</td>" +
					"<td>"+ receivedData[i]["discrepCode"] +"</td>" +
					"<td>"+ receivedData[i]["discrepValue"] +"</td>" +
					"<td>"+ receivedData[i]["limitType"] +"</td>" +
					"<td>"+ receivedData[i]["limits"] +"</td>" +
					"<td>"+ "-" +"</td></tr>";
				
				tableReport.append($(row));
			}	
			
			if(receivedData.length == 0)
			{
				reportMessage.text(lang.noData);
				reportMessage.show();				
			} else {
				reportMessage.hide();
			}
		});
		
	}
	
	function ClearTable() {
		//select rows with class not ReportTableHeader
		$("table#ReportTable").find("tr").each(function(){
			var self = $(this);
			if(!self.is('.ReportTableHeader')){
				self.remove();
			}
        });
		
		reportMessage.text(lang.uploadingData);
		reportMessage.show();
	}
	
	// Validates that the input string is a valid date formatted as "dd.mm.yyyy"
	function IsValidDate(dateString)
	{
	    // First check for the pattern
	    if(!/^\d{1,2}\.\d{1,2}\.\d{4}$/.test(dateString))
	        return false;

	    // Parse the date parts to integers
	    var parts = dateString.split(".");
	    var day = parseInt(parts[0], 10);
	    var month = parseInt(parts[1], 10);
	    var year = parseInt(parts[2], 10);

	    // Check the ranges of month and year
	    if(year < 1000 || year > 3000 || month == 0 || month > 12)
	        return false;

	    var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];

	    // Adjust for leap years
	    if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
	        monthLength[1] = 29;

	    // Check the range of the day
	    return day > 0 && day <= monthLength[month - 1];
	};
    
    
	//===============================================
	//diagnostic tab
	//===============================================

	var etalonList = $("select#etalonList"),
		engineSerialList = $("select#engineSerialList"),
		engineSlicesList = $("select#engineSlicesList"),
		engineDiscrepAbscissa = $("select#engineDiscrepAbscissa"),
		engineDiscrepOrdinate = $("select#engineDiscrepOrdinate"),
		chartList = $("select#chartList"),
		diagnosticChartContaider = $("div.DiagnosticChartContaider"),
		diagnosticMenuContaider = $("div.DiagnosticMenuContaider"),
		
		addChartBut = $("input#addChart"),
		delChartBut = $("input#delChart"),
		
		receivedData = [],
		chartId = 0,
		
		placeholdersContentArr = new Array();

	//==================================================================
	//Get language object
	//==================================================================
	var lang = {
			flights: "Flights"
		};
	
	$.ajax({
		url: LANG_FILE,
		async: false,
		dataType: 'json',
		success: function(data) {
			$.each(data, function(key, val) {
				lang = val;
				return (key !== CURR_PAGE);
			});
		}
	});
	
	//==================================================================
	//Build initial chart
	//==================================================================

	if(chartList.find(":selected").length > 0){
		var curAction = GET_DISCREP_VALS,
			curEtalonId = etalonList.find(":selected").data("etalonid"),
			curEtalonName = etalonList.find(":selected").text(),
			curEngineSerial = engineSerialList.find(":selected").text(),
			curSlice = engineSlicesList.find(":selected").text(),
			curAbscissa = engineDiscrepAbscissa.find(":selected").data("name"),
			curAbscissaText = engineDiscrepAbscissa.find(":selected").text(),
			curOrdinate = engineDiscrepOrdinate.find(":selected").data("name"),
			chartListTitle = curAbscissaText + " - " + curOrdinate + " / " + curEngineSerial + " / " + curSlice + " / " + curEtalonName;
		
		if(curEtalonName == lang.etalonIgnore){
			chartListTitle = curAbscissaText + " - " + curOrdinate + " / " + curEngineSerial + " / " + curSlice;			
		}
		
		buildDiscrepChart(curEtalonId, curEngineSerial, curSlice, curAbscissa, curOrdinate, chartListTitle);
	}

	//==================================================================
	//etalonsList change event
	//==================================================================
	etalonList.on('change', function(e){
			UpdateEngineList();
			//UpdateSlicesList();
			//UpdateDiscrepOrdinate();
	});
	
	function UpdateEngineList(curAction, curEtalonId){
		var curAction = GET_ETALON_ENGINES,
			curEtalonId = etalonList.find(":selected").data("etalonid"),
			postData = {
				action: curAction,
				etalonId: curEtalonId
			};

		var jqxhr = $.ajax({
			  type: "POST",
			  url: ASYNC_DIAGNOSTIC_SERVER_SCR,
			  data: postData,
			  dataType: "json",
			  async: false
			});
		
		jqxhr.always(function(receivedData){
			engineSerialList.empty();
			for(var i = 0; i < receivedData.length; i++){
				engineSerialList.append("<option>" + receivedData[i] + "</option>")
			}		
		});
		
		engineSerialList.children(":first").attr("selected", "true");
		engineSerialList.trigger( "change" );
	}
	
	
	//==================================================================
	//engineSerialList change event
	//==================================================================
	engineSerialList.on('change', function(e){
		UpdateSlicesList();
		//UpdateDiscrepOrdinate();
	});
	
	function UpdateSlicesList(){
		var curAction = GET_ENGINE_SLICES,
			curEtalonId = etalonList.find(":selected").data("etalonid"),
			curEngineSerial = engineSerialList.find(":selected").text(),
			postData = {
				action: curAction,
				etalonId: curEtalonId, 
				engineSerial: curEngineSerial
			};

		var jqxhr = $.ajax({
			  type: "POST",
			  url: ASYNC_DIAGNOSTIC_SERVER_SCR,
			  data: postData,
			  dataType: "json",
			  async: false
			});
		
		jqxhr.always(function(receivedData){
			engineSlicesList.empty();
			for(var i = 0; i < receivedData.length; i++){
				engineSlicesList.append("<option>" + receivedData[i] + "</option>")
			}		
		});
		
		engineSlicesList.children(":first").attr("selected", "true");
		engineSlicesList.trigger( "change" );
	}
	
	//==================================================================
	//engineSlicesList change event
	//==================================================================
	engineSlicesList.on('change', function(e){
		UpdateDiscrepOrdinate();
	});
	
	function UpdateDiscrepOrdinate() {
		var curAction = GET_ENGINE_DISCREP,
			curEtalonId = etalonList.find(":selected").data("etalonid"),
			curEngineSerial = engineSerialList.find(":selected").text(),
			curSlice = engineSlicesList.find(":selected").text(),
			postData = {
				action: curAction,
				etalonId: curEtalonId, 
				engineSerial: curEngineSerial,
				slice: curSlice
			},
			
			jqxhr = $.ajax({
				type: "POST",
				url: ASYNC_DIAGNOSTIC_SERVER_SCR,
				data: postData,
				dataType: "json",
				async: true
			});
		
		jqxhr.always(function(receivedData){
			engineDiscrepAbscissa.empty();
			engineDiscrepAbscissa.append("<option data-name='" + DIAGNOSTIC_ABSCISSA_FLIGHTS + "'>" + lang.flights + "</option>")
			/*for(var i = 0; i < receivedData.length; i++){
				engineDiscrepAbscissa.append("<option data-name'" + receivedData[i] + "'>" + receivedData[i] + "</option>")
			}*/
			
			engineDiscrepOrdinate.empty();
			for(var i = 0; i < receivedData.length; i++){
				engineDiscrepOrdinate.append("<option data-name='" + receivedData[i] + "'>" + receivedData[i] + "</option>")
			}
			engineDiscrepOrdinate.children(":first").attr("selected", "true");
		});
	}

	//==================================================================
	//addChartBut change click
	//==================================================================
	addChartBut.on("click", function(e){
		var curAction = GET_DISCREP_VALS,
			curEtalonId = etalonList.find(":selected").data("etalonid"),
			curEtalonName = etalonList.find(":selected").text(),
			curEngineSerial = engineSerialList.find(":selected").text(),
			curSlice = engineSlicesList.find(":selected").text(),
			curAbscissa = engineDiscrepAbscissa.find(":selected").data("name"),
			curAbscissaText = engineDiscrepAbscissa.find(":selected").text(),
			curOrdinate = engineDiscrepOrdinate.find(":selected").data("name"),
			chartListTitle = curAbscissaText + " - " + curOrdinate + " / " + curEngineSerial + " / " + curSlice + " / " + curEtalonName;
		
		if(curEtalonName == lang.etalonIgnore){
			chartListTitle = curAbscissaText + " - " + curOrdinate + " / " + curEngineSerial + " / " + curSlice;			
		}
		
		chartList.append("<option title='" + chartListTitle + "' " +
				"data-placeholderid='" + chartId + "' " + 
				"data-etalonid='" + curEtalonId + "' " + 
				"data-engineserial='" + curEngineSerial + "' " + 
				"data-slice='" + curSlice + "' " + 
				"data-abscissa='" + curAbscissa + "' " + 
				"data-ordinate='" + curOrdinate + "' " + 
				">" + chartListTitle + "</option>");
		chartList.children(":first").attr("selected", "true");
		
		buildDiscrepChart(curEtalonId, curEngineSerial, curSlice, curAbscissa, curOrdinate, chartListTitle);
	});
	
	//==================================================================
	//delChartBut change click
	//==================================================================
	delChartBut.on("click", function(e){
		if(chartList.find(":selected").length > 0){
			var selectedChart = chartList.find(":selected"),
				chartSelector = "div#placeholderid" + selectedChart.data("placeholderid"),
				placeholderid = "placeholderid" + selectedChart.data("placeholderid");

			$(chartSelector).remove();
			selectedChart.remove();
			chartList.children(":first").attr("selected", "true");
			
			delete placeholdersContentArr[placeholderid];
		}
	});
	
	function buildDiscrepChart(etalonId, engineSerial, slice, abscissa, ordinate, chartListTitle){
		var curAction = GET_DISCREP_VALS,
			chartPlaceholderId = "placeholderid" + chartId,
			chartPlaceholderSelector = "div#" + chartPlaceholderId,
			chartPlaceholder = "<div id='" + chartPlaceholderId + "' class='DiagnosticChartPlaceholder'></div>";

		diagnosticChartContaider.append(chartPlaceholder);
		
		var postData = {
				action: curAction,
				etalonId: etalonId,
				engineSerial: engineSerial,
				slice: slice,
				abscissa: abscissa,
				ordinate: ordinate
			},
			
			jqxhr = $.ajax({
				type: "POST",
				url: ASYNC_DIAGNOSTIC_SERVER_SCR,
				data: postData,
				dataType: "json"
			});
		
		jqxhr.always(function(receivedData) {
			var limits = getDiscrepLimits(etalonId, engineSerial, slice, ordinate),
				marking = [];
			
			for(var j = 0; j < limits.length; j++){
				marking.push({ yaxis: { from: limits[j][0], to: limits[j][0] }, color: colourNameToHex(rainbow((j + 1) * -1)) });
				marking.push({ yaxis: { from: limits[j][1], to: limits[j][1] }, color: colourNameToHex(rainbow((j + 1) * -1)) });
			}
			
			var yMin = limits[limits.length - 1][1],
				yMax = limits[limits.length - 1][0],
				yPointMin = receivedData[0][1],
				yPointMax = receivedData[0][1];
			
			for(var k = 0; k < receivedData.length; k++){
				if(yPointMin > receivedData[k][1]){
					yPointMin = receivedData[k][1];
				}
				
				if(yPointMax < receivedData[k][1]){
					yPointMax = receivedData[k][1];
				}				
			}
			
			if(yPointMin < yMin){
				yMin = yPointMin;
			}
			
			if(yPointMax > yMax){
				yMax = yPointMax;
			}
			
			//add some boundary
			var boundary = Math.abs(yMin * yMax);
			if(boundary < 1) {
				yMin = yMin - Math.abs(yMin * yMax) * 1.25;
				yMax = yMax + Math.abs(yMin * yMax) * 1.25;
			} else if((boundary >= 1) && (boundary < 10)) {
				yMin = yMin - Math.abs(yMin * yMax) * 0.25;
				yMax = yMax + Math.abs(yMin * yMax) * 0.25;
			} else {
				yMin = yMin - 1;
				yMax = yMax + 1;
			}
			
			var data = [{ 
				data: receivedData, 
				label: chartListTitle, 
				points: { symbol: "circle" } }],
				
				options = {
					series: {
						points: {
							show: true,
							color: "red",
							radius: 3
						}
					},
					grid: {
						markings: marking,
						markingsLineWidth: 1, //number
						hoverable: true
					},
					xaxis: { 
						min: receivedData[0][0] - ((receivedData[receivedData.length - 1][0] - receivedData[0][0]) / receivedData.length),
						max: receivedData[receivedData.length - 1][0] + ((receivedData[receivedData.length - 1][0] - receivedData[0][0]) / receivedData.length)
					},
					yaxis: { 
						min: yMin,
						max: yMax
					},
					colors: [colourNameToHex(rainbow(chartId))],
					legend:{         
			            backgroundOpacity: 0.3,
			            noColumns: 0,
			            position: "nw"
			        }
				};
			
			var curPlot = $.plot(chartPlaceholderSelector, data, options);
			
			var curPlotContent = new Array();
			curPlotContent["plot"] = curPlot;
			curPlotContent["plotSelector"] = chartPlaceholderSelector;
			curPlotContent["data"] = data;
			curPlotContent["options"] = options;
			
			placeholdersContentArr[chartPlaceholderId] = curPlotContent;
		});
		
		chartId++;		
	}
	
	function getDiscrepLimits(etalonId, engineSerial, slice, discrepCode){
		var curAction = GET_DISCREP_LIMITS,
			discrepLimits = 0,
			postData = {
				action: curAction,
				etalonId: etalonId,
				engineSerial: engineSerial,
				slice: slice,
				discrep: discrepCode
			},
		
			jqxhr = $.ajax({
				type: "POST",
				url: ASYNC_DIAGNOSTIC_SERVER_SCR,
				data: postData,
				dataType: "json",
				async: false
			}).done(function(receivedData) {
				discrepLimits = receivedData;
			});
		
		return discrepLimits;
	}
	
	$(window).resize(function() {
		for (var key in placeholdersContentArr) {
			var curPlot = $.plot(placeholdersContentArr[key]["plotSelector"], placeholdersContentArr[key]["data"], placeholdersContentArr[key]["options"]);

			var curPlotContent = new Array();
			curPlotContent["plot"] = curPlot;
			curPlotContent["plotSelector"] = placeholdersContentArr[key]["plotSelector"];
			curPlotContent["data"] = placeholdersContentArr[key]["data"];
			curPlotContent["options"] = placeholdersContentArr[key]["options"];
			placeholdersContentArr[key] = curPlotContent;
		}
	});
	
	$("h3").click(function() {
		for (var key in placeholdersContentArr) {
			var curPlot = $.plot(placeholdersContentArr[key]["plotSelector"], placeholdersContentArr[key]["data"], placeholdersContentArr[key]["options"]);

			var curPlotContent = new Array();
			curPlotContent["plot"] = curPlot;
			curPlotContent["plotSelector"] = placeholdersContentArr[key]["plotSelector"];
			curPlotContent["data"] = placeholdersContentArr[key]["data"];
			curPlotContent["options"] = placeholdersContentArr[key]["options"];
			placeholdersContentArr[key] = curPlotContent;
		}
	});	
});

//=============================================================
//to improve color default color pattern
function rainbow(count) {
switch (count) {
	case -3:
		return 'Red';
	break;
	
	case -2:
		return 'Orange';
	break;
	
	case -1:
		return 'Yellow';
	break;
	
	case 0:
		return 'DeepSkyBlue';
	break;
	case 1:
		return 'Violet';
	break;
	case 2:
		return 'Tomato';
	break;
	case 3:
		return 'BlueViolet';
	break;
	case 4:
		return 'Brown';
	break;
	case 5:
		return 'CadetBlue';
	break;
	case 6:
		return 'Chartreuse';
	break;
	case 7:
		return 'Chocolate';
	break;
	case 8:
		return 'Coral';
	break;
	case 9:
		return 'CornflowerBlue';
	break;
	case 10:
		return 'Crimson ';
	break;
	case 11:
		return 'DarkCyan';
	break;
	case 12:
		return 'DarkGray';
	break;
	case 13:
		return 'DarkOliveGreen';
	break;
	case 14:
		return 'DarkOrange';
	break;
	case 15:
		return 'DarkOrchid';
	break;
	case 16:
		return 'DarkRed';
	break;
	case 17:
		return 'DarkSalmon';
	break;
	case 18:
		return 'DarkSeaGreen';
	break;
	case 19:
		return 'DarkSlateGray';
	break;
	case 20:
		return 'DarkTurquoise';
	break;
	case 21:
		return 'BurlyWood';
	break;
	case 22:
		return 'DodgerBlue';
	break;
	case 23:
		return 'FireBrick';
	break;
	case 24:
		return 'ForestGreen';
	break;
	case 25:
		return 'Fuchsia';
	break;
	case 26:
		return 'Gold';
	break;
	case 27:
		return 'GoldenRod';
	break;
	case 28:
		return 'Gray';
	break;
	case 29:
		return 'Green';
	break;
	case 30:
		return 'GreenYellow';
	break;
	case 31:
		return 'HotPink';
	break;
	case 32:
		return 'IndianRed';
	break;
	case 33:
		return 'Indigo';
	break;
	case 34:
		return 'LightSkyBlue';
	break;
	case 35:
		return 'Magenta';
	break;
	case 36:
		return 'Maroon';
	break;
	case 37:
		return 'MediumAquaMarine';
	break;
	case 38:
		return 'YellowGreen';
	break;
	case 39:
		return 'MediumOrchid';
	break;
	case 40:
		return 'MediumPurple';
	break;
	case 41:
		return 'MediumSeaGreen';
	break;
	case 42:
		return 'MediumSlateBlue';
	break;
	case 43:
		return 'MediumSpringGreen';
	break;
	case 44:
		return 'MediumTurquoise';
	break;
	case 45:
		return 'MediumVioletRed';
	break;
	case 46:
		return 'MidnightBlue';
	break;
	case 47:
		return 'Olive';
	break;
	case 48:
		return 'OliveDrab';
	break;
	case 49:
		return 'OrangeRed';
	break;
	case 50:
		return 'Orchid';
	break;
	case 51:
		return 'PaleVioletRed';
	break;
	case 52:
		return 'Peru';
	break;
	case 53:
		return 'Pink';
	break;
	case 54:
		return 'Plum';
	break;
	case 55:
		return 'PowderBlue';
	break;
	case 56:
		return 'Purple';
	break;
	case 57:
		return 'RosyBrown';
	break;
	case 58:
		return 'RoyalBlue';
	break;
	case 59:
		return 'SaddleBrown';
	break;
	case 60:
		return 'Salmon';
	break;
	case 61:
		return 'SandyBrown';
	break;
	case 62:
		return 'SeaGreen';
	break;
	case 63:
		return 'Sienna';
	break;
	case 64:
		return 'Silver';
	break;
	case 65:
		return 'SkyBlue';
	break;
	case 66:
		return 'SlateBlue';
	break;
	case 67:
		return 'SlateGray';
	break;
	case 68:
		return 'SpringGreen';
	break;
	case 69:
		return 'SteelBlue';
	break;
	case 70:
		return 'Tan';
	break;
	case 71:
		return 'Teal';
	break;
	case 72:
		return 'Thistle';
	break;
	case 73:
		return 'Turquoise';
	break;
	default:
		// 30 random hues with step of 12 degrees
		var hue = Math.floor(Math.random() * 30) * 12;
		
		return $.Color({
		hue: hue,
		saturation: 0.9,
		lightness: 0.6,
		alpha: 0.7,
		}).toHexString();
	break;
	};
};

function colourNameToHex(colour)
{
    var colours = {"aliceblue":"#f0f8ff","antiquewhite":"#faebd7","aqua":"#00ffff","aquamarine":"#7fffd4","azure":"#f0ffff",
    "beige":"#f5f5dc","bisque":"#ffe4c4","black":"#000000","blanchedalmond":"#ffebcd","blue":"#0000ff","blueviolet":"#8a2be2","brown":"#a52a2a","burlywood":"#deb887",
    "cadetblue":"#5f9ea0","chartreuse":"#7fff00","chocolate":"#d2691e","coral":"#ff7f50","cornflowerblue":"#6495ed","cornsilk":"#fff8dc","crimson":"#dc143c","cyan":"#00ffff",
    "darkblue":"#00008b","darkcyan":"#008b8b","darkgoldenrod":"#b8860b","darkgray":"#a9a9a9","darkgreen":"#006400","darkkhaki":"#bdb76b","darkmagenta":"#8b008b","darkolivegreen":"#556b2f",
    "darkorange":"#ff8c00","darkorchid":"#9932cc","darkred":"#8b0000","darksalmon":"#e9967a","darkseagreen":"#8fbc8f","darkslateblue":"#483d8b","darkslategray":"#2f4f4f","darkturquoise":"#00ced1",
    "darkviolet":"#9400d3","deeppink":"#ff1493","deepskyblue":"#00bfff","dimgray":"#696969","dodgerblue":"#1e90ff",
    "firebrick":"#b22222","floralwhite":"#fffaf0","forestgreen":"#228b22","fuchsia":"#ff00ff",
    "gainsboro":"#dcdcdc","ghostwhite":"#f8f8ff","gold":"#ffd700","goldenrod":"#daa520","gray":"#808080","green":"#008000","greenyellow":"#adff2f",
    "honeydew":"#f0fff0","hotpink":"#ff69b4",
    "indianred ":"#cd5c5c","indigo":"#4b0082","ivory":"#fffff0","khaki":"#f0e68c",
    "lavender":"#e6e6fa","lavenderblush":"#fff0f5","lawngreen":"#7cfc00","lemonchiffon":"#fffacd","lightblue":"#add8e6","lightcoral":"#f08080","lightcyan":"#e0ffff","lightgoldenrodyellow":"#fafad2",
    "lightgrey":"#d3d3d3","lightgreen":"#90ee90","lightpink":"#ffb6c1","lightsalmon":"#ffa07a","lightseagreen":"#20b2aa","lightskyblue":"#87cefa","lightslategray":"#778899","lightsteelblue":"#b0c4de",
    "lightyellow":"#ffffe0","lime":"#00ff00","limegreen":"#32cd32","linen":"#faf0e6",
    "magenta":"#ff00ff","maroon":"#800000","mediumaquamarine":"#66cdaa","mediumblue":"#0000cd","mediumorchid":"#ba55d3","mediumpurple":"#9370d8","mediumseagreen":"#3cb371","mediumslateblue":"#7b68ee",
    "mediumspringgreen":"#00fa9a","mediumturquoise":"#48d1cc","mediumvioletred":"#c71585","midnightblue":"#191970","mintcream":"#f5fffa","mistyrose":"#ffe4e1","moccasin":"#ffe4b5",
    "navajowhite":"#ffdead","navy":"#000080",
    "oldlace":"#fdf5e6","olive":"#808000","olivedrab":"#6b8e23","orange":"#ffa500","orangered":"#ff4500","orchid":"#da70d6",
    "palegoldenrod":"#eee8aa","palegreen":"#98fb98","paleturquoise":"#afeeee","palevioletred":"#d87093","papayawhip":"#ffefd5","peachpuff":"#ffdab9","peru":"#cd853f","pink":"#ffc0cb","plum":"#dda0dd","powderblue":"#b0e0e6","purple":"#800080",
    "red":"#ff0000","rosybrown":"#bc8f8f","royalblue":"#4169e1",
    "saddlebrown":"#8b4513","salmon":"#fa8072","sandybrown":"#f4a460","seagreen":"#2e8b57","seashell":"#fff5ee","sienna":"#a0522d","silver":"#c0c0c0","skyblue":"#87ceeb","slateblue":"#6a5acd","slategray":"#708090","snow":"#fffafa","springgreen":"#00ff7f","steelblue":"#4682b4",
    "tan":"#d2b48c","teal":"#008080","thistle":"#d8bfd8","tomato":"#ff6347","turquoise":"#40e0d0",
    "violet":"#ee82ee",
    "wheat":"#f5deb3","white":"#ffffff","whitesmoke":"#f5f5f5",
    "yellow":"#ffff00","yellowgreen":"#9acd32"};

    if (typeof colours[colour.toLowerCase()] != 'undefined')
        return colours[colour.toLowerCase()];

    return false;
}

function colourHexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}
