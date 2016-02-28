var LANG_FILE =  location.protocol + '//' + location.host + "/lang/" + "RU.lang",
	LANG_FILE_DEFAULT =  location.protocol + '//' + location.host + "/lang/" + "Default.lang";

var LEGEND_CONTAINER_WIDTH = 170,
	LEGEND_CONTAINER_OUTER = 175,
	BOUNDARY = 1.2, //20% aditional boundary
	PARAM_TYPE_AP = "ap",
	PARAM_TYPE_BP = "bp";

var KEY_V = 86, //vertical vizir
	KEY_H = 72, //horizontal line
	KEY_N = 78, //names params
	KEY_T = 84, //table
	KEY_M = 77, //map
	KEY_G = 71, //google earth
	KEY_S = 83, //simulator
	KEY_D = 68, //distribute
	KEY_F = 70, //freze vizir
	KEY_E = 69, //exactly (rebuild params with exact current segment)
	KEY_I = 73, //info
	KEY_L = 76; //labels

$(document).ready(function(){
	
$(document).tooltip();
//=============================================================
//set placeholder size to window size
document.documentElement.style.overflowX = 'hidden';
document.documentElement.style.overflowY = 'hidden';
var $window = $(document),
	gCont = $("#graphContainer"),
	tCont = $("#tableContainer"),
	placeholder = $("#placeholder"),
	legend = $("#legend"),
	legendLabel = $("#legendLabel"),
	tableholder = $("#tableHolder"),
	openTableForm = $("#openTableForm"),
	openMapForm = $("#openMapForm"),
	openModelForm = $("#openModelForm"),
	openGoogleEarthForm = $("#openGoogleEarthForm"),
	loadingBox = $("div#loadingBox");
//=============================================================
var flightId = $("input#flightId").attr('value'),
	user = $("input#username").attr('value'),
	tplName = $("input#tplname").attr('value'),
	stepLength = $("input#stepLength").attr('value'),
	startCopyTime = $("input#startCopyTime").attr('value'),
	apParams = $.parseJSON($("textarea#apParams").text()),
	bpParams = $.parseJSON($("textarea#bpParams").text()),
	startFrame = $("input#startFrame").attr('value'),
	endFrame = $("input#endFrame").attr('value'),

	startFrameTime = $("input#startFrameTime").attr('value'),
	endFrameTime = $("input#endFrameTime").attr('value'),
	lang = Object();

loadingBox.css({
	'position': 'absolute',
	'top': ($window.height() / 2 - 30) + 'px',
	'left':  ($window.width() / 2 - 30) + 'px'
});

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

var infoForm = $("div#infoForm").dialog({
		resizable: false,
		modal: true,
		autoOpen: false,
		draggable: false,
		position: 'center',
		width: '640px',
		closeText: lang.closeLabel
	});
infoForm.css("visibility", "visible");

//=============================================================

//=============================================================
//prepare placeholder and plot
gCont.css({
	"top": '5px',
	"width": $window.width() - 15 + 'px',
	"height": $window.height() - 15 + 'px',
	}).fadeIn("fast", "linear");
placeholder.css({
	"margin-top": '30px',
	"width": $window.width() - LEGEND_CONTAINER_OUTER + 'px',
	"height": gCont.height() - 35 + 'px'
	});
legend.css({
	"margin-top": '35px',
	"height": placeholder.height() - 25 + 'px'
});

placeholder.css("width",  ($window.width() - LEGEND_CONTAINER_OUTER + 
	(apParams.length + bpParams.length) * 18) + "px");
placeholder.css("margin-left",  "-" + 
	((apParams.length + bpParams.length - 1) * 18) + "px");	

//=============================================================

//=============================================================
//flot options
var options	= {
	xaxis: {
		mode: "time",
		timezone: "browser",
		min: (new Date(startFrameTime * 1000)).getTime(),
		max: (new Date(endFrameTime * 1000)).getTime()
	},
	yaxis:{
		ticks: 0,
		//tickLength: 1,
		position : "left",
		zoomRange: [0,0],			
	},
	zoom: {
		interactive: true,
	},
	pan: {
		interactive: true,
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
	imageClassName: "canvas-image",
	imageFormat: "png"
};

//=============================================================
var Prm = new Param(flightId, 
	startFrame, endFrame,
	apParams, bpParams);

var plot = Object(),
	plotYaxArr = Object(),
	plotAxes = Object(),
	plotDataset = Object(),
	AxesWrk = Object(),
	Exc = Object(),
	Legnd = Object();

$.when(Prm.ReceiveParams()).then(
	function(status) {
		loadingBox.fadeOut();
		plot = $.plot(placeholder, Prm.data, options);	

		//distribute y axes
		plotYaxArr = plot.getYAxes();
		plotAxes = plot.getAxes();
		plotDataset = plot.getData();
			
		AxesWrk = new AxesWorker(stepLength, startCopyTime, plotAxes, user);
		AxesWrk.LoadDistribution(plotYaxArr, apParams, bpParams, flightId, tplName);

		Exc = new Exception(flightId, apParams, bpParams, Prm.refParamArr, 
			Prm.associativeParamsArr, placeholder, plotDataset, plotAxes.xaxis, plotYaxArr);

		Exc.ReceiveExcepions();
		Exc.UpdateExcSupportTools(plotAxes.xaxis, plotYaxArr);
				
		Legnd = new Legend(flightId, legend, 
			apParams, bpParams, Prm.associativeParamsArr, 
			plotAxes, plotDataset, placeholder);
		//receive legend titles
		Legnd.ReceiveLegend();	
		
		plot.draw();
		plot.pan(0);
	},
	function(status) {
		console.log(status);
	},
	function(status) {
		console.log(status);
	}
);

var Tbl = new Table(tCont, tableholder, openTableForm, flightId, 
		apParams, bpParams,
		startFrame, endFrame);	

//=============================================================	
	
//=============================================================
//highlighting clicked item and save it in clickedItem
var clickedItem = new Object(), 
	clicked = false, ctrlPressed = false;
placeholder.on('plotclick', function (event, pos, item) { 
	if(item){
		clicked = !clicked;
		if(clicked) {
			clickedItem = item;
			clickedItem.series.lines.lineWidth = clickedItem.series.lines.lineWidth + 2;
			plot.draw();
		} else {
			clickedItem.series.lines.lineWidth = clickedItem.series.lines.lineWidth - 2;
			plot.draw();
		}
	}
});
//============================================================= 

//=============================================================
//scaling chart and moving it up and down
var prevPos = null;
placeholder.on('plothover', function (event, pos, item) { 
	//label
	if (!Legnd.updateLegendTimeout) {
		if(!Legnd.crosshairLocked) {
			Legnd.updateLegendTimeout = 
				setTimeout(function() {
					var values = Prm.GetValue(plotDataset, pos.x);
					var binaries = Prm.GetBinaries(plotDataset, pos.x);
					Legnd.UpdateLegend(pos, values, binaries);
				}, 200);
		} else {
			Legnd.updateLegendTimeout = 
				setTimeout(function() {
					var values = Prm.GetValue(plotDataset, Legnd.vizirFreezePos.x);
					var binaries = Prm.GetBinaries(plotDataset, Legnd.vizirFreezePos.x);
					Legnd.UpdateLegend(Legnd.vizirFreezePos, values, binaries);
				}, 200);
		}
	}		
	
	if(clicked){
		//listenning for ctrl pressed
		var y = "y" + clickedItem.series.yaxis.n;
		if(!ctrlPressed){
			clickedItem.series.yaxis.max -= pos[y] - 
				clickedItem.datapoint[1];
			clickedItem.series.yaxis.min -= pos[y] - 
				clickedItem.datapoint[1];
			plot.pan(0);
			
			//save distribution
			setTimeout(function(){
				AxesWrk.SaveDistribution(plotYaxArr, apParams, bpParams, flightId, tplName);
			}, 500);
			
		} else {
			//this check for prevent jump out
			if(clickedItem.datapoint[1] > clickedItem.series.yaxis.min && clickedItem.datapoint[1] < clickedItem.series.yaxis.max) {
				clickedItem.series.yaxis.max -= pos[y] - 
					clickedItem.datapoint[1];
				clickedItem.series.yaxis.min += pos[y] - 
					clickedItem.datapoint[1];
			}
			plot.pan(0);
			
			//save distribution
			setTimeout(function(){
				AxesWrk.SaveDistribution(plotYaxArr, apParams, bpParams, flightId, tplName);
			}, 500);
			
			/*console.log("max" + clickedItem.series.yaxis.max);
			console.log("min " + clickedItem.series.yaxis.min);
			console.log("pos[y] " + pos[y]);*/
		};
	} else {
		if(item != null){
			Legnd.HighlightLegend(item.seriesIndex);
			//console.log(item);
		} else {
			//to hide all highlight
			Legnd.HighlightLegend(-1);
		}
	}
	
	if(horLineSetting){
		Legnd.SuportHorizontAfterCreation();
	}
	
	//show current time
	Legnd.pos = pos;
	Legnd.ShowVisirTime();
	
	if(Legnd.displayNeed){
		Legnd.ShowSeriesNames(plotAxes.xaxis, plotYaxArr);
	}
	
});
//=============================================================

//=============================================================
//function returns true when ctrl pressed and false after it up	
placeholder.on("plotpan", function (event, currPlot) {
	Legnd.legendTitlesNotSet = true;
	Exc.UpdateExcSupportTools(plotAxes.xaxis, plotYaxArr);
	Legnd.UpdateBarContainersPos(plotAxes.xaxis, plotYaxArr);
				
	if(Legnd.showSeriesLabelsNeed){
		Legnd.ShowSeriesLabels(plotAxes.xaxis, plotYaxArr);
	}
});

placeholder.on("plotzoom", function (event, currPlot) {
	Legnd.legendTitlesNotSet = true;
	Exc.UpdateExcSupportTools(plotAxes.xaxis, plotYaxArr);
	Legnd.UpdateBarContainersPos(plotAxes.xaxis, plotYaxArr);

	if(Legnd.showSeriesLabelsNeed){
		Legnd.ShowSeriesLabels(plotAxes.xaxis, plotYaxArr);
	}
});
//=============================================================

//=============================================================
//var loopCount = 0,
//	looping = false;
legend.on("mouseover", function(e){
	var el = $(e.target);
	if(el.attr('class') == 'legendLabel'){
		var labelText = el.text().substring(),
			seriesLabel = labelText.substring(0, labelText.indexOf('=') - 2),
			seriesLabelHovered = seriesLabel,
			series = plot.getData();
		for(var i = 0; i < series.length; i++){
			labelText = series[i].label;
			seriesLabel = labelText.substring(0, labelText.indexOf('=') - 1); 
			if(seriesLabelHovered == seriesLabel){
				//looping = true;
				//transition(series[i], plot);
				series[i].shadowSize += 2;
				series[i].lines.lineWidth += 2;
	
				plot.draw();
				break;
			}
		}
	}
});
//=============================================================

//=============================================================
legend.on("mouseout", function(e){
	var el = $(e.target);
	if(el.attr('class') == 'legendLabel'){
		var labelText = el.text().substring(),
			seriesLabel = labelText.substring(0, labelText.indexOf('=') - 2),
			seriesLabelHovered = seriesLabel,
			series = plot.getData();
		for(var i = 0; i < series.length; i++){
			labelText = series[i].label;
			seriesLabel = labelText.substring(0, labelText.indexOf('=') - 1); 
			if(seriesLabelHovered == seriesLabel){
				//looping = false;
				series[i].shadowSize -= 2;
				series[i].lines.lineWidth -= 2;
				plot.draw();
				break;
			}
		}
	}
});
//=============================================================

//=============================================================
/*var timer;
function transition(series, plot) {
    if(loopCount < 5) {
    	series.shadowSize = loopCount;
    	loopCount++;
    } else if ((loopCount >= 6) && (loopCount < 12)){
    	series.shadowSize--;
    	loopCount++;  
    } else {
    	loopCount = 0;    	
    }
    plot.draw();
    if(looping == true){
		timer = setTimeout(function(){
			transition(series, plot);
		}, 100);
    } else {
    	series.shadowSize = 0;
		plot.draw();    
		clearTimeout(timer, 2000);
    }
}*/
//=============================================================

//=============================================================
//build bar
//horLineSetting supports on plotover event hor line moving
var horLineSetting = false;
$(document).keydown(function(event) {
	
	var plotYaxArr = plot.getYAxes(),
		plotAxes = plot.getAxes();
	if (event.which == KEY_V) {
		var barContainer = $(Legnd.AppendSectionBar());
		Legnd.UpdateBarContainersPos(plotAxes.xaxis, plotYaxArr);
	}
	//build bar whith names
	if(event.which == KEY_N) {
		if(!Legnd.crosshairLocked) {
			Legnd.displayNeed = !Legnd.displayNeed;
			Legnd.ShowSeriesNames(plotAxes.xaxis, plotYaxArr);
		}
	}
	//put series labels
	if(event.which == KEY_L) {
		Legnd.showSeriesLabelsNeed = !Legnd.showSeriesLabelsNeed;
		Legnd.seriesLabelsValues = Prm.GetValue(plotDataset, Legnd.pos.x);
		Legnd.seriesLabelsTime = Legnd.pos.x;
		Legnd.ShowSeriesLabels(plotAxes.xaxis, plotYaxArr);
	}
	//open table tab
	if(event.which == KEY_T){
		Tbl.OpenTableInNewTab();
	}
	//open map tab
	if(event.which == KEY_M){
		openMapForm.submit();
	}
	//open simulator tab
	if(event.which == KEY_S){
		openModelForm.submit();
	}
	//open google Earth tab
	if(event.which == KEY_G){
		openGoogleEarthForm.submit();
	}
	//open info form
	if(event.which == KEY_I){
		if(infoForm.dialog("isOpen")) {
			infoForm.dialog("close");
		} else {
			infoForm.dialog("open");
		}
	}
	//distribute
	if(event.which == KEY_D){
		var series = plot.getData();
		plotYaxArr = plot.getYAxes();
		plotAxes = plot.getAxes();
		AxesWrk.Distribute(plotYaxArr, plotAxes, series, apParams.length);
		plot.draw();
		plot.pan(0);
		
		//save distribution
		AxesWrk.SaveDistribution(plotYaxArr, apParams, bpParams, flightId, tplName);
	}
	
	//freeze vizir
	if(event.which == KEY_F){
		if(Legnd.crosshairLocked) {
			Legnd.RemoveSectionBar($(Legnd.vizirBarContainer));
			plot.unlockCrosshair();
			Legnd.crosshairLocked = !Legnd.crosshairLocked;
		} else {
			Legnd.vizirFreezePos = Legnd.pos;
			Legnd.vLineColor = "rgba(170, 0, 0, 0.80)";
			Legnd.vizirBarContainer = Legnd.AppendSectionBar();
			Legnd.vLineColor = 'darkgrey';
			Legnd.UpdateBarContainersPos(plotAxes.xaxis, plotYaxArr);
			plot.lockCrosshair(1);
			Legnd.crosshairLocked = !Legnd.crosshairLocked;
			Legnd.displayNeed = false;
			Legnd.ShowSeriesNames(plotAxes.xaxis, plotYaxArr);
			
			var values = Prm.GetValue(plotDataset, plotAxes.xaxis.min);
			Legnd.ShowSeriesLabels(plotAxes.xaxis, plotYaxArr, values);
		}
	}
	
	//exact param by curr startFrame and endFrame
	if(event.which == KEY_E){
		var currXmin = plotAxes.xaxis.min,
			currXmax = plotAxes.xaxis.max;
		
		Prm.startFrame = (currXmin - startCopyTime) / 1000 / stepLength;
		Prm.endFrame = (currXmax - startCopyTime) / 1000 / stepLength;
		
		AxesWrk.SaveDistribution(plotYaxArr, apParams, bpParams, flightId, tplName);
		
		$.when(Prm.ReceiveParams()).then(
			function(status) {			
				plot.setData(Prm.data);	
					
				AxesWrk.LoadDistribution(plotYaxArr, apParams, bpParams, flightId, tplName);

				plot.draw();
	            plot.pan(0);
	            
	            Legnd.axes = plot.getAxes();
	            plotDataset = plot.getData();
	            Legnd.dataset = plotDataset;
	            
	            Legnd.UpdateBarContainersPos(plotAxes.xaxis, plotYaxArr);
	            Exc.UpdateExcSupportTools(plotAxes.xaxis, plotYaxArr);
	            
			},
			function(status) {
				console.log(status);
			},
			function(status) {
				console.log(status);
			}
		);
	}
	
	//build horizontal line
	if(event.which == KEY_H){
		if(clicked){
			Legnd.CreateHorizont(clickedItem.seriesIndex);

			clicked = !clicked;
			clickedItem.series.lines.lineWidth = clickedItem.series.lines.lineWidth - 2;
			plot.draw();
			horLineSetting = !horLineSetting;
		}else {
			if(horLineSetting){
				horLineSetting = !horLineSetting;
			}
		}
	}	
});
//=============================================================

//=============================================================
//function returns true when ctrl pressed and false after it up
document.addEventListener("keydown", keyDownCtrl, false);
function keyDownCtrl(e) {
	if(e.ctrlKey) {
		ctrlPressed = true;
		document.removeEventListener("keydown", keyDownCtrl, false);
		document.addEventListener("keyup", keyUpCtrl, false);
	} else {
		ctrlPressed = false;
	}
}

function keyUpCtrl(e) {
	if(e.ctrlKey) {
		ctrlPressed = true;
	} else {
		ctrlPressed = false;
		document.addEventListener("keydown", keyDownCtrl, false);
		document.removeEventListener("keyup", keyUpCtrl, false);
	}
}
//=============================================================

//=============================================================
//change default scaling by x to scaling by y when shift pressed
document.addEventListener("keydown", keyDownShift, false);
function keyDownShift(e) {
	if(e.shiftKey) {
		var yAxArr = plot.getYAxes();
		for(var i = 0; i < yAxArr.length; i++){
			yAxArr[i].options.zoomRange = null;
		}
		plot.getXAxes()[0].options.zoomRange = [0,0];	
		document.addEventListener("keyup", keyUpShift, false);
		document.removeEventListener("keydown", keyDownShift, false);
	} else {
		var yAxArr = plot.getYAxes();
		for(var i = 0; i < yAxArr.length; i++){
			yAxArr[i].options.zoomRange = [0,0];
		}
		plot.getXAxes()[0].options.zoomRange = null;	
	};
}

function keyUpShift(e) {
	if(e.shiftKey) {
		var yAxArr = plot.getYAxes();
		for(var i = 0; i < yAxArr.length; i++){
			yAxArr[i].options.zoomRange = null;
		}
		plot.getXAxes()[0].options.zoomRange = [0,0];	
	} else {
		var yAxArr = plot.getYAxes();
		for(var i = 0; i < yAxArr.length; i++){
			yAxArr[i].options.zoomRange = [0,0];
		}
		plot.getXAxes()[0].options.zoomRange = null;	
		document.addEventListener("keydown", keyDownShift, false);
		document.removeEventListener("keyup", keyUpShift, false);
	};
} 
//=============================================================

});







