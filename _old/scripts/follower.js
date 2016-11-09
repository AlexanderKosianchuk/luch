var LEGEND_CONTAINER_WIDTH = 170;
var LEGEND_CONTAINER_OUTER = 175;
var BOUNDARY = 1.2; //20% aditional boundary
var PARAM_TYPE_AP = "ap";
var PARAM_TYPE_BP = "bp";
var KEY_V = 86;
var KEY_H = 72;
var KEY_N = 78;
var KEY_MULT = 106;
var KEY_T = 84;
var KEY_M = 77;

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
	tableholder = $("#tableHolder"),
	openTableForm = $("#openTableForm"),
	openMapForm = $("#openMapForm");
//=============================================================
var flightId = $("input#flightId").attr('value'),
	stepLength = $("input#stepLength").attr('value'),
	startCopyTime = $("input#startCopyTime").attr('value'),
	apParamsEnc = $("textarea#apParams").text(),
	bpParamsEnc = $("textarea#bpParams").text(),
	startFrame = $("input#startFrame").attr('value'),
	endFrame = $("input#endFrame").attr('value');

//=============================================================
var Prm = new Param(flightId, 
	startFrame, endFrame,
	apParamsEnc, bpParamsEnc);

Prm.ReceiveParams();	
//=============================================================

//=============================================================
//flot options
var options	= {
	xaxis: {
		mode: "time",
		timezone: "browser",			
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
		hoverable: true, 
		clickable: true,
		tickColor: "rgba(255, 255, 255, 0)",
		borderWidth: 1,
		backgroundColor: { colors: ["#EEE", "#DDD"], },
		markingsLineWidth: 1,

	},
	legend: {         
		container: legend,            
		noColumns: 1,
	},  
	lines: {
		lineWidth: 1,
	}
};
//=============================================================

//=============================================================
//prepare placeholder and plot
gCont.css({
	"top": '5px',
	"width": $window.width() - 15 + 'px',
	"height": $window.height() - 15 + 'px',
	}).fadeToggle("slow", "linear");
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
	(Prm.apCount + Prm.bpCount) * 18) + "px");
placeholder.css("margin-left",  "-" + 
	((Prm.apCount + Prm.bpCount - 1) * 18) + "px");	
var plot = $.plot(placeholder, Prm.data, options);	

legendLabel = $("#legendLabel");

//distribute y axes
var plotYaxArr = plot.getYAxes(),
	plotAxes = plot.getAxes(),
	plotDataset = plot.getData();
	
var AxesWrk = new AxesWorker(stepLength, startCopyTime, 
	plotAxes);

AxesWrk.Distribute(plotYaxArr, Prm.apCount);
//plot.draw();
plot.pan(0);

/*var Exc = new Exception(flightId, 
		Prm.paramCount, Prm.refParamArr, 
		Prm.associativeParamsArr);
		Exc.ReceiveExcepions();
		Exc.UpdateExcContainersPos(plotAxes.xaxis, plotYaxArr);*/
		
var Legnd = new Legend(flightId, legend, 
	Prm.apArr, Prm.bpArr, Prm.associativeParamsArr, plotAxes, plotDataset, placeholder);
	//receive legend titles
	Legnd.ReceiveLegend();
	
var Tbl = new Table(tCont, tableholder, openTableForm, flightId, 
		Prm.apArr, Prm.bpArr,
		startFrame, endFrame);

updateChart();

function updateChart(){
	if((endFrame - startFrame) > 1000){
		startFrame++;
		endFrame++;
	} else {
		endFrame++;	
	}
	Prm = new Param(flightId, 
			startFrame, endFrame,
			apParamsEnc, bpParamsEnc);

	Prm.UpdateScriptsValues();
	Prm.ReceiveParams();
	placeholder.css("width",  ($window.width() - LEGEND_CONTAINER_OUTER + 
		(Prm.apCount + Prm.bpCount) * 18) + "px");
	placeholder.css("margin-left",  "-" + 
		((Prm.apCount + Prm.bpCount - 1) * 18) + "px");	
	plot = $.plot(placeholder, Prm.data, options);	

	legendLabel = $("#legendLabel");

	//distribute y axes
	plotYaxArr = plot.getYAxes();
	plotAxes = plot.getAxes();
	plotDataset = plot.getData();
		
	AxesWrk = new AxesWorker(stepLength, startCopyTime, 
		plotAxes);
	AxesWrk.GetXRange();
	AxesWrk.Distribute(plotYaxArr, Prm.apCount);
	plot.pan(0);
	//plot.draw();
	setTimeout(updateChart, 2000);
}


//=============================================================	
	
//=============================================================
//highlighting clicked item and save it in clickedItem
var clickedItem = new Object(), 
	clicked = false, ctrlPressed = false;
placeholder.on('plotclick', function (event, pos, item) { 
	if(item){
		clicked = !clicked;
		if(clicked){
			clickedItem = item;
			clickedItem.series.lines.lineWidth = clickedItem.series.lines.lineWidth + 2;
			plot.draw();
		}else{
			clickedItem.series.lines.lineWidth = clickedItem.series.lines.lineWidth - 2;
			plot.draw();
		}
	}
});
//============================================================= 

//=============================================================
//scaling chart and moving it up and down
placeholder.on('plothover', function (event, pos, item) { 
	//label
	if (!Legnd.updateLegendTimeout) {
		Legnd.updateLegendTimeout = 
			setTimeout(function() {
				var values = Prm.GetValue(plotDataset, pos.x);
				var binaries = Prm.GetBinaries(plotDataset, pos.x);
				Legnd.UpdateLegend(pos, values, binaries);
			}, 100);
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
		} else {
			//this check for prevent jump out
			clickedItem.series.yaxis.max -= pos[y] - 
				clickedItem.datapoint[1];
			clickedItem.series.yaxis.min += pos[y] - 
				clickedItem.datapoint[1];
			plot.pan(0);			
		};
	};
	
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
	//Exc.UpdateExcContainersPos(plotAxes.xaxis, plotYaxArr);
	Legnd.UpdateBarContainersPos(plotAxes.xaxis, plotYaxArr);
				
	/*AxesWrk.axes = plot.getAxes();
	AxesWrk.GetXRange();
	
	if(AxesWrk.redrawNeed){	
	
	AxesWrk.redrawNeed = false;
			
	if (!Prm.updateParamTimeout) {
		Prm.updateParamTimeout = 
				setTimeout(function() {
				//Prm.asyncReceive = true;
				Prm.data = new Array();

				Prm.startFrame = AxesWrk.startFrame;
				Prm.endFrame = AxesWrk.endFrame;
				Prm.UpdateScriptsValues();
				
				Prm.ReceiveParams();
				plot = $.plot(placeholder, Prm.data, options);
				plotYaxArr = plot.getYAxes();
				AxesWrk.Distribute(plotYaxArr, Prm.apCount);
				//plot.draw();
				plot.pan(0);
			}, 1000);
		}
	}*/
});

placeholder.on("plotzoom", function (event, currPlot) {
	Legnd.legendTitlesNotSet = true;
	//Exc.UpdateExcContainersPos(plotAxes.xaxis, plotYaxArr);
	Legnd.UpdateBarContainersPos(plotAxes.xaxis, plotYaxArr);
		
	/*AxesWrk.axes = plot.getAxes();
	AxesWrk.GetXRange();
	
	if(AxesWrk.redrawNeed){	
	
	AxesWrk.redrawNeed = false;
		
	if (!Prm.updateParamTimeout) {
		Prm.updateParamTimeout = 
			setTimeout(function() {
				//Prm.asyncReceive = true;
				Prm.data = new Array();
				AxesWrk.axes = plot.getAxes();
				AxesWrk.GetXRange();

				Prm.startFrame = AxesWrk.startFrame;
				Prm.endFrame = AxesWrk.endFrame;
				Prm.UpdateScriptsValues();
				
				Prm.ReceiveParams();
			    plot = $.plot(placeholder, Prm.data, options);
				plotYaxArr = plot.getYAxes();
				AxesWrk.Distribute(plotYaxArr, Prm.apCount);
				//plot.draw();
				plot.pan(0);

			}, 1000);
		}
	}*/
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
				console.log(series[i].lines.lineWidth);
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
		Legnd.AppendSectionBar();
		Legnd.UpdateBarContainersPos(plotAxes.xaxis, plotYaxArr);
		//delete bar on mainLable click
		$('div.BarMainLabel').each(function(){
			this.ondblclick = function(){
				Legnd.RemoveSectionBar($(this));	
			};
		});
	}
	//build bar whith names
	if(event.which == KEY_N) {
		Legnd.displayNeed = !Legnd.displayNeed;
		Legnd.ShowSeriesNames(plotAxes.xaxis, plotYaxArr);
	}
	//open table tab
	if(event.which == KEY_T){
		Tbl.OpenTableInNewTab();
	}
	if(event.which == KEY_M){
		openMapForm.submit();
	}
	//open table tab
	if(event.which == KEY_H){
		if(clicked){
			Legnd.CreateHorizont(clickedItem.seriesIndex);
			//delete bar on mainLable click
			$('div.horValueLabel').each(function(){
				this.ondblclick = function(){
					Legnd.RemoveHorizont($(this));	
				};
			});
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







