var CHART_SRC = location.protocol + '//' + location.host + "/view/chart.php",
	LEGEND_CONTAINER_OUTER = 175,
	PARAM_TYPE_AP = "ap",
	PARAM_TYPE_BP = "bp";
	
function Chart(window, document, langStr, srvcStrObj, eventHandler) 
{ 
	this.langStr = langStr;
	this.actions = srvcStrObj["chartPage"];

	this.window = window;
	this.document = document;
		
	this.eventHandler = eventHandler;
	this.chartFactoryContainer = null;
	this.chartTopMenu = null;
	this.chartWorkspace = null;
	this.chartContent = null;
	
	/*this.mainContainer = $("div.MainContainer");
	this.loadingBox = null;
	this.chartWorkspace = null;
	this.chartTopMenu = null;
	this.chartContent = null;*/
	
	this.legend = null;
	this.placeholder = null;
	this.plot = null;
	this.Prm = null;
	this.plotYaxArr = null;
	this.plotAxes = null;
	this.plotDataset = null;
	this.AxesWrk = null;
	this.Exc = null;
	this.Legnd = null;
	
	//
	this.clickedItem = new Object();
	this.clicked = false;
	this.ctrlPressed = false;
	this.shiftPressed = false;
	this.horLineSetting = false;
	this.markingCount = 1;
	this.keybordEventsSupported = false;
	//self.horLineSetting supports on plotover event hor line moving
	
	//data need to display chart
	this.user = null;
	this.tplName = null;
	this.flightId = null;
	this.stepLength = null;
	this.startCopyTime = null;
	this.apParams = null;
	this.bpParams = null;
	this.startFrame = null;
	this.endFrame = null;

	this.startFrameTime = null;
	this.endFrameTime = null;
}

Chart.prototype.FillFactoryContaider = function(factoryContainer) {
	var self = this;
	this.chartFactoryContainer = factoryContainer;	

	var pV = {
			action: self.actions["putChartContainer"],
			data: { 
				data: 'data'
			}
	};
	
	$.ajax({
		type: "POST",
		data: pV,
		dataType: 'json',
		url: CHART_SRC,
		async: true
	}).fail(function(msg){
		console.log(msg);
	}).done(function(answ) {
		if(answ["status"] == "ok") {
			var data = answ['data'];
			PutTopMenu(self);
			
			self.chartFactoryContainer.append(data['workspace']);
			
			self.chartWorkspace = $('div#chartWorkspace');
			self.chartContent = $('div#graphContainer');
			
			self.loadingBox = $("div#loadingBox").css("top", self.window.height() / 2 - 40);
			self.legend = $('div#legend');
			self.placeholder = $('div#placeholder');
			
			self.ResizeChartContainer();
			self.document.scrollTop(factoryContainer.data("index") * self.window.height());
			
			self.LoadFlotChart();			
		} else {
			console.log(answ["error"]);
		}
    });
	
	function PutTopMenu(self){
		self.chartTopMenu = $("<div></div>")
			.attr("id", 'topMenuChart')
			.addClass('TopMenu')
			.appendTo(self.chartFactoryContainer);
		
		$("<label></label>")
			.attr("id", 'figurePrint')
			.addClass('TopLeftChartButt')
			.appendTo(self.chartTopMenu)
			.on("click", function(e){
				if(self.plot != null) {
					var prms = self.Prm.apArr.concat(self.Prm.bpArr);
					PrintFile(self.flightId, 
							self.plotAxes.xaxis.min, self.plotAxes.xaxis.max, 
							prms);
				}
			}).append(
				$("<span><span>")
					.addClass('TopLeftChartButtSpan')
					.text(self.langStr.chartPrintTable)
			);
		
		$("<label></label>")
			.attr("id", 'chartPrint')
			.addClass('TopMenuLeftSecondButt')
			.appendTo(self.chartTopMenu)
			.on("click", function(e){
				if(self.plot != null) {
					OpenChartInNewWindow(self);
				}
			}).append(
				$("<span><span>")
					.addClass('TopLeftChartButtSpan')
					.text(self.langStr.chartPrintInNewWindow)
			);
	}
	
	function PrintFile(flightId, fromTime, toTime, prms){
		var pV = {
				action: self.actions["figurePrint"],
				data: { 
					flightId: flightId,
					fromTime: fromTime,
					toTime: toTime,
					prms: prms
				}
		};
		
		$.ajax({
			type: "POST",
			data: pV,
			dataType: 'json',
			url: CHART_SRC,
			async: true
		}).fail(function(msg){
			console.log(msg);
		}).done(function(answ) {
			if(answ["status"] == "ok") {
				var url = answ["data"];
				location.href = url;
				//window.open = url;
			}
		});
	}
	
	function OpenChartInNewWindow(self){		
		if (typeof location.origin === 'undefined')
		    location.origin = location.protocol + '//' + location.host;
		
		var getParams = '/chart.php?flightId=' + self.flightId + "&" + 
			"tplName=" + self.tplName + "&" +
			"stepLength=" + self.stepLength + "&" +
			"startCopyTime=" + self.startCopyTime + "&" +
			"startFrame=" + self.startFrame + "&" +
			"endFrame=" + self.endFrame;
		window.open(location.origin + getParams, '_blank');
	}
}

/*Chart.prototype.ShowChartContainer = function() {
	var self = this;
	
	if(self.chartWorkspace != null){
		self.chartWorkspace.remove();
	}
		
	var pV = {
			action: self.actions["putChartContainer"],
			data: { 
				data: null
			}
	};
	
	$.ajax({
		type: "POST",
		data: pV,
		dataType: 'json',
		url: CHART_SRC,
		async: true
	}).fail(function(msg){
		console.log(msg);
	}).done(function(answ) {
		if(answ["status"] == "ok") {
			var data = answ['data'];

			self.mainContainer.after(data['chartWorkspace']);
			
			self.chartWorkspace = $('div#chartWorkspace');
			
			self.chartWorkspace.append(data['topMenu']);
			self.chartWorkspace.append(data['chartContent']);
			
			self.chartTopMenu = $('div#chartTopMenu');
			self.chartContent = $('div#graphContainer');

			self.loadingBox = $("div#loadingBox").css("top", self.window.height() / 2 - 40);
			self.legend = $('div#legend');
			self.placeholder = $('div#placeholder');
			
			self.ResizeChartContainer();
			self.window.scrollTop(self.window.height() * 2);
			
			self.LoadFlotChart();
		} else {
			console.log(answ["error"]);
		}
    });
}*/

Chart.prototype.ResizeChartContainer = function(e) {
	var self = this;
	if(self.chartWorkspace != null){
		self.chartWorkspace.css({
			"left": 0,
			"top" : self.window.height() * 2,
			"height": self.window.height(),
			"width": self.window.width()
		});
	}
	
	if((self.chartWorkspace != null) && 
		(self.chartTopMenu != null) && 
		(self.chartContent != null)){
		
		self.chartContent.css({
			"left": 0,
			"top" : self.chartTopMenu.height(),
			"width" : self.window.width(),
			"height": self.chartWorkspace.height() - 
				self.chartTopMenu.height()
		});
	}
	
	if((self.chartContent != null) && 
			(self.placeholder != null) && 
			(self.legend != null) &&
			(self.apParams != null) && 
			(self.bpParams != null)){
		
		self.placeholder.css({
			"margin-top": '30px',
			"width": self.window.width() - LEGEND_CONTAINER_OUTER + 'px',
			"height": self.chartContent.height() - 35 + 'px'
			});
		self.legend.css({
			"margin-top": '35px',
			"width": LEGEND_CONTAINER_OUTER + "px",
			"height": self.placeholder.height() - 25 + 'px'
		});
	
		self.placeholder.css("width",  (self.window.width() - (self.legend.width() + 30) + 
			(self.apParams.length + self.bpParams.length) * 18) + "px");
		
		if((self.apParams.length == 1) && (self.bpParams.length == 0)){
			self.placeholder.css("margin-left",  "-7px");	
		} else {
			self.placeholder.css("margin-left",  "-" + 
				((self.apParams.length + self.bpParams.length - 1) * 18) + "px");	
		}
	}
	
	self.eventHandler.trigger("resizeShowcase");

	return false;
}

Chart.prototype.SetChartData = function(flightId, tplName, 
		stepLength, startCopyTime, startFrame, endFrame,
		apParams, bpParams){
	
	this.flightId = parseInt(flightId);
	this.tplName = tplName;
	this.stepLength = parseFloat(stepLength);
	this.startCopyTime = parseInt(startCopyTime);
	this.startFrame = parseInt(startFrame);
	this.endFrame = parseInt(endFrame);
	this.apParams = apParams;
	this.bpParams = bpParams;
	
	this.startFrameTime = this.startCopyTime + (this.startFrame * this.stepLength);
	this.endFrameTime = this.startCopyTime + (this.endFrame * this.stepLength);	
	
	this.markingCount = this.apParams.length + this.bpParams.length + 1;
}

Chart.prototype.LoadFlotChart = function() {
	//flot options
	var self = this,
		options	= {
			xaxis: {
				mode: "time",
				timezone: "browser",	
				tickColor: "rgba(0, 0, 0, 0)",
				min: (new Date(self.startFrameTime * 1000)).getTime(),
				max: (new Date(self.endFrameTime * 1000)).getTime()
			},
			yaxis:{
				ticks: 0,
				/*tickLength: 10,*/
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
				tickColor: "rgba(220, 220, 220, 1)",
				borderWidth: 1,
				backgroundColor: "#fff",
				markingsLineWidth: 1,
				markings: function (axes) {
				    var markings = [];				    
				    for (var x = Math.floor(axes.yaxis.min); 
				    	x < axes.yaxis.max;
				    	x += Math.abs(axes.yaxis.max - axes.yaxis.min) / self.markingCount) {

						markings.push({ yaxis: { from: x, to: x }, color: "#E8E8E8" });
				    }					
				    return markings;
				  }

			},
			legend: {         
				container: self.legend,            
				noColumns: 1,
			},  
			lines: {
				lineWidth: 1,
			},
			imageClassName: "canvas-image",
			imageFormat: "png"
		};
	
	self.Prm = new Param(self.flightId, 
			self.startFrame, self.endFrame,
			self.apParams, self.bpParams, self.actions);
	

	$.when(self.Prm.ReceiveParams()).then(
		function(status) {
			self.loadingBox.fadeOut();			
			self.plot = $.plot(self.placeholder, self.Prm.data, options);	

			//distribute y axes
			self.plotYaxArr = self.plot.getYAxes();
			self.plotAxes = self.plot.getAxes();
			self.plotDataset = self.plot.getData();
				
			self.AxesWrk = new AxesWorker(self.stepLength, self.startCopyTime, self.plotAxes, self.actions);
			
			
			self.AxesWrk.LoadDistribution(self.plotYaxArr, self.apParams, self.bpParams, self.flightId, self.tplName);

			self.Exc = new Exception(self.flightId, 
					self.apParams, self.bpParams, self.Prm.refParamArr, 
					self.Prm.associativeParamsArr, self.placeholder, self.chartContent, 
					self.plotDataset, self.plotAxes.xaxis, self.plotYaxArr, self.actions);

			self.Exc.ReceiveExcepions();
			self.Exc.UpdateExcSupportTools(self.plotAxes.xaxis, self.plotYaxArr);
			
			self.Legnd = new Legend(self.flightId, self.legend, 
					self.apParams, self.bpParams, self.Prm.associativeParamsArr, 
					self.plotAxes,self. plotDataset, self.placeholder, self.chartContent, self.actions);
			//receive legend titles
			self.Legnd.ReceiveLegend();	
			
			self.SupportPlotEvents();
			self.SupportLegendEvents();	
			if(!self.keybordEventsSupported){
				self.keybordEventsSupported = self.SupportKeyBoardEvents();
			}
			
			self.plot.draw();
			self.plot.pan(0);
		},
		function(status) {
			console.log(status);
		},
		function(status) {
			console.log(status);
		}
	);
}

Chart.prototype.SupportPlotEvents = function(e) {
	//=============================================================	
	
	//=============================================================
	//highlighting clicked item and save it in clickedItem
	var self = this;
	
	self.placeholder.on('plotclick', function (event, pos, item) { 
		if(item){
			self.clicked = !self.clicked;
			if(self.clicked) {
				self.clickedItem = item;
				self.clickedItem.series.lines.lineWidth = self.clickedItem.series.lines.lineWidth + 2;
				self.plot.draw();
			} else {
				self.clickedItem.series.lines.lineWidth = self.clickedItem.series.lines.lineWidth - 2;
				self.plot.draw();
			}
		}
	});
	//============================================================= 

	//=============================================================
	//scaling chart and moving it up and down
	var prevPos = null;
	self.placeholder.on('plothover', function (event, pos, item) { 	
		//label
		if (!self.Legnd.updateLegendTimeout) {
			if(!self.Legnd.crosshairLocked) {
				self.Legnd.updateLegendTimeout = 
					setTimeout(function() {
						var values = self.Prm.GetValue(self.plotDataset, pos.x);
						var binaries = self.Prm.GetBinaries(self.plotDataset, pos.x);
						self.Legnd.UpdateLegend(pos, values, binaries);
					}, 200);
			} else {
				self.Legnd.updateLegendTimeout = 
					setTimeout(function() {
						var values = self.Prm.GetValue(self.plotDataset, self.Legnd.vizirFreezePos.x);
						var binaries = self.Prm.GetBinaries(self.plotDataset, self.Legnd.vizirFreezePos.x);
						self.Legnd.UpdateLegend(self.Legnd.vizirFreezePos, values, binaries);
					}, 200);
			}
		}		
		
		if(self.clicked){
			//listenning for ctrl pressed
			var y = "y" + self.clickedItem.series.yaxis.n;
			if(!self.ctrlPressed){
				self.clickedItem.series.yaxis.max -= pos[y] - 
					self.clickedItem.datapoint[1];
				self.clickedItem.series.yaxis.min -= pos[y] - 
					self.clickedItem.datapoint[1];
				self.plot.pan(0);
				
				//save distribution
				setTimeout(function(){
					self.AxesWrk.SaveDistribution(self.plotYaxArr, self.apParams, self.bpParams, self.flightId, self.tplName);
				}, 500);
				
			} else {
				//this check for prevent jump out
				if(self.clickedItem.datapoint[1] > self.clickedItem.series.yaxis.min && self.clickedItem.datapoint[1] < self.clickedItem.series.yaxis.max) {
					self.clickedItem.series.yaxis.max -= pos[y] - 
						self.clickedItem.datapoint[1];
					self.clickedItem.series.yaxis.min += pos[y] - 
						self.clickedItem.datapoint[1];
				}
				self.plot.pan(0);
				
				//save distribution
				setTimeout(function(){
					self.AxesWrk.SaveDistribution(self.plotYaxArr, self.apParams, self.bpParams, self.flightId, self.tplName);
				}, 500);
			};
		} else {
			if(item != null){
				self.Legnd.HighlightLegend(item.seriesIndex);
				//console.log(item);
			} else {
				//to hide all highlight
				self.Legnd.HighlightLegend(-1);
			}
		}
		
		if(self.horLineSetting){
			self.Legnd.SuportHorizontAfterCreation();
		}
		
		//show current time
		self.Legnd.pos = pos;
		self.Legnd.ShowVisirTime();
		
		if(item){
			self.Legnd.leadParamValBox.css('display', 'block');
			var label = item.series.label.split('='),
				val = item.datapoint[1];
				label = label[0].trim();
				self.Legnd.ShowLeadParamVal(val, label);
		} else {
			self.Legnd.leadParamValBox.css('display', 'none');
		} 
				
		if(self.Legnd.displayNeed){
			self.Legnd.ShowSeriesNames(self.plotAxes.xaxis, self.plotYaxArr);
		}
		
	});
	//=============================================================

	//=============================================================
	//function returns true when ctrl pressed and false after it up	
	self.placeholder.on("plotpan", function (event, currPlot) {
		self.Legnd.legendTitlesNotSet = true;

		self.Exc.UpdateExcSupportTools(self.plotAxes.xaxis, self.plotYaxArr);
		self.Legnd.UpdateBarContainersPos(self.plotAxes.xaxis, self.plotYaxArr);
					
		if(self.Legnd.showSeriesLabelsNeed){
			self.Legnd.ShowSeriesLabels(self.plotAxes.xaxis, self.plotYaxArr);
		}
		
		if(self.Legnd.showSeriesCodeLabelsNeed){
			self.Legnd.ShowSeriesCodes(self.plotAxes.xaxis, self.plotYaxArr);
		}
	});

	self.placeholder.on("plotzoom", function (event, currPlot) {
		self.Legnd.legendTitlesNotSet = true;
		self.Exc.UpdateExcSupportTools(self.plotAxes.xaxis, self.plotYaxArr);
		self.Legnd.UpdateBarContainersPos(self.plotAxes.xaxis, self.plotYaxArr);

		if(self.Legnd.showSeriesLabelsNeed){
			self.Legnd.ShowSeriesLabels(self.plotAxes.xaxis, self.plotYaxArr);
		}
		
		if(self.Legnd.showSeriesCodeLabelsNeed){
			self.Legnd.ShowSeriesCodes(self.plotAxes.xaxis, self.plotYaxArr);
		}
	});
	
	//prevent scrolling window during scrolling plot
	self.chartWorkspace.mousewheel(function(event, delta) {
		var target = $(event.target);
		event.stopPropagation();
		event.preventDefault();

	});
}

//=============================================================

//=============================================================
Chart.prototype.SupportLegendEvents = function(e) {
	var self = this;
	
	//var loopCount = 0,
	//looping = false;
	self.legend.on("mouseover", function(e){
		var el = $(e.target);
		if(el.attr('class') == 'legendLabel'){
			var labelText = el.text().substring(),
				seriesLabel = labelText.substring(0, labelText.indexOf('=') - 2),
				seriesLabelHovered = seriesLabel,
				series = self.plot.getData();
			for(var i = 0; i < series.length; i++){
				labelText = series[i].label;
				seriesLabel = labelText.substring(0, labelText.indexOf('=') - 1); 
				if(seriesLabelHovered == seriesLabel){
					//looping = true;
					//transition(series[i], plot);
					series[i].shadowSize += 2;
					series[i].lines.lineWidth += 2;
		
					self.plot.draw();
					break;
				}
			}
		}
	});
	
	//=============================================================

	//=============================================================
	
	self.legend.on("mouseout", function(e){
		var el = $(e.target);
		if(el.attr('class') == 'legendLabel'){
			var labelText = el.text().substring(),
				seriesLabel = labelText.substring(0, labelText.indexOf('=') - 2),
				seriesLabelHovered = seriesLabel,
				series = self.plot.getData();
			for(var i = 0; i < series.length; i++){
				labelText = series[i].label;
				seriesLabel = labelText.substring(0, labelText.indexOf('=') - 1); 
				if(seriesLabelHovered == seriesLabel){
					//looping = false;
					series[i].shadowSize -= 2;
					series[i].lines.lineWidth -= 2;
					self.plot.draw();
					break;
				}
			}
		}
	});
}

//=============================================================

//=============================================================
Chart.prototype.SupportKeyBoardEvents = function(e) {
	var self = this;
	
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
		KEY_L = 76, //labels
		KEY_C = 67, //codes
		KEY_ARROW_LEFT = 37, //pan left
		KEY_ARROW_RIGHT = 39,  //pan right
		KEY_ARROW_UP = 38, //pan up
		KEY_ARROW_DOWN = 40,  //pan down
		KEY_PLUS = 107,  //add one more marking line
		KEY_MINUS = 109,  //remove one marking line
		SHIFT = 16,
		CTRL = 17; 
	
	//build bar
	self.document.keyup(function(event) {
		if (event.which == KEY_V) {
			var barContainer = $(self.Legnd.AppendSectionBar());
			self.Legnd.UpdateBarContainersPos(self.plotAxes.xaxis, self.plotYaxArr);
		}
		//build bar whith names
		if(event.which == KEY_N) {
			if(!self.Legnd.crosshairLocked) {
				self.Legnd.displayNeed = !self.Legnd.displayNeed;
				self.Legnd.ShowSeriesNames(self.plotAxes.xaxis, self.plotYaxArr);
			}
		}
		//put series labels
		if(event.which == KEY_L) {
			self.Legnd.showSeriesLabelsNeed = !self.Legnd.showSeriesLabelsNeed;
			self.Legnd.seriesLabelsValues = self.Prm.GetValue(self.plotDataset, self.Legnd.pos.x);
			self.Legnd.seriesLabelsTime = self.Legnd.pos.x;
			self.Legnd.ShowSeriesLabels(self.plotAxes.xaxis, self.plotYaxArr);
		}
		//put series labels
		if(event.which == KEY_C) {
			self.Legnd.showSeriesCodeLabelsNeed = !self.Legnd.showSeriesCodeLabelsNeed;
			self.Legnd.seriesLabelsValues = self.Prm.GetValue(self.plotDataset, self.Legnd.pos.x);
			self.Legnd.seriesCodeLabelsTime = self.Legnd.pos.x;
			self.Legnd.ShowSeriesCodes(self.plotAxes.xaxis, self.plotYaxArr);
		}
		//open table tab
		/*if(event.which == KEY_T){
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
		}*/
		//distribute
		if(event.which == KEY_D){
			var series = self.plotDataset;
			if(self.shiftPressed){
				self.AxesWrk.DistributeByBinary(self.plotYaxArr, self.plotAxes, series, self.apParams.length);
				self.plot.draw();
				self.plot.pan(0);
				
				//save distribution
				self.AxesWrk.SaveDistribution(self.plotYaxArr, self.apParams, self.bpParams, self.flightId, self.tplName);
			} else {
				self.AxesWrk.distributeByBinaryCoef = 1;
				self.AxesWrk.Distribute(self.plotYaxArr, self.plotAxes, series, self.apParams.length);
				self.plot.draw();
				self.plot.pan(0);
				
				//save distribution
				self.AxesWrk.SaveDistribution(self.plotYaxArr, self.apParams, self.bpParams, self.flightId, self.tplName);
			}
		}
		
		//freeze vizir
		if(event.which == KEY_F){
			if(self.Legnd.crosshairLocked) {
				self.Legnd.RemoveSectionBar($(self.Legnd.vizirBarContainer));
				self.plot.unlockCrosshair();
				self.Legnd.crosshairLocked = !self.Legnd.crosshairLocked;
			} else {
				self.Legnd.vizirFreezePos = self.Legnd.pos;
				self.Legnd.vLineColor = "rgba(170, 0, 0, 0.80)";
				self.Legnd.vizirBarContainer =self. Legnd.AppendSectionBar();
				self.Legnd.vLineColor = 'darkgrey';
				self.Legnd.UpdateBarContainersPos(self.plotAxes.xaxis, self.plotYaxArr);
				self.plot.lockCrosshair(1);
				self.Legnd.crosshairLocked = !self.Legnd.crosshairLocked;
				self.Legnd.displayNeed = false;
				self.Legnd.ShowSeriesNames(self.plotAxes.xaxis, self.plotYaxArr);
			}
		}
		
		//exact param by curr startFrame and endFrame
		if(event.which == KEY_E){
			var currXmin = self.plotAxes.xaxis.min,
				currXmax = self.plotAxes.xaxis.max;
			
			self.Prm.startFrame = (currXmin - self.startCopyTime) / 1000 / self.stepLength;
			self.Prm.endFrame = (currXmax - self.startCopyTime) / 1000 / self.stepLength;
			
			self.AxesWrk.SaveDistribution(self.plotYaxArr, self.apParams, self.bpParams, self.flightId, self.tplName);
			
			$.when(self.Prm.ReceiveParams()).then(
				function(status) {			
					self.plot.setData(self.Prm.data);	
						
					self.AxesWrk.LoadDistribution(self.plotYaxArr, self.apParams, self.bpParams, self.flightId, self.tplName);

					self.plot.draw();
					self.plot.pan(0);
		            
					self.Legnd.axes = self.plot.getAxes();
					self.plotDataset = self.plot.getData();
					self.Legnd.dataset = self.plotDataset;
		            
					self.Legnd.UpdateBarContainersPos(self.plotAxes.xaxis, self.plotYaxArr);
					self.Exc.UpdateExcSupportTools(self.plotAxes.xaxis, self.plotYaxArr);
		            
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
			if(self.clicked){
				self.Legnd.CreateHorizont(self.clickedItem.seriesIndex);

				self.clicked = !self.clicked;
				self.clickedItem.series.lines.lineWidth = self.clickedItem.series.lines.lineWidth - 2;
				self.plot.draw();
				self.horLineSetting = !self.horLineSetting;
			}else {
				if(self.horLineSetting){
					self.horLineSetting = !self.horLineSetting;
				}
			}
		}
		
		if(event.which == CTRL){
			self.ctrlPressed = false;
		}
		
		if(event.which == SHIFT) {
			self.shiftPressed = false;
			var yAxArr = self.plot.getYAxes();
			for(var i = 0; i < yAxArr.length; i++){
				yAxArr[i].options.zoomRange = [0,0];
			}
			self.plot.getXAxes()[0].options.zoomRange = null;	
		}
		
		//add one more marking line
		if(event.which == KEY_PLUS){
			self.markingCount++;
			self.plot.draw();
		}
		
		//remove one marking line
		if(event.which == KEY_MINUS){
			if(self.markingCount > 1){
				self.markingCount--;
				self.plot.draw();
			}
		}
	});
	
	self.document.keydown(function(event) {
		if(event.which == KEY_ARROW_LEFT){
			var delta = (self.plotAxes.xaxis.max - self.plotAxes.xaxis.min) / 500;// 0.5 percent
			self.plotAxes.xaxis.max += delta;
			self.plotAxes.xaxis.min += delta;		
			self.plot.draw();
			self.plot.pan(0);
		}	
		if(event.which == KEY_ARROW_RIGHT){
			var delta = (self.plotAxes.xaxis.max - self.plotAxes.xaxis.min) / 500;
			self.plotAxes.xaxis.max -= delta;
			self.plotAxes.xaxis.min -= delta;
			self.plot.draw();
			self.plot.pan(0);
		}
		
		if(event.which == KEY_ARROW_UP){
			event.stopPropagation();
			event.preventDefault();
			
			for(var i = 0; i < self.plotYaxArr.length; i++){
				var delta = (self.plotYaxArr[i].max - self.plotYaxArr[i].min) / 100; // one percent

				self.plotYaxArr[i].max += delta;
				self.plotYaxArr[i].min += delta;
			}
			
			self.plot.draw();
			self.plot.pan(0);
		}	
		
		if(event.which == KEY_ARROW_DOWN){
			for(var i = 0; i < self.plotYaxArr.length; i++){
				var delta = (self.plotYaxArr[i].max - self.plotYaxArr[i].min) / 100;

				self.plotYaxArr[i].max -= delta;
				self.plotYaxArr[i].min -= delta;
			}
			
			self.plot.draw();
			self.plot.pan(0);
		}
		
		if(event.which == CTRL) {
			self.ctrlPressed = true;
		}
		
		if(event.which == SHIFT) {
			self.shiftPressed = true;
			var yAxArr = self.plot.getYAxes();
			for(var i = 0; i < yAxArr.length; i++){
				yAxArr[i].options.zoomRange = null;
			}
			self.plot.getXAxes()[0].options.zoomRange = [0,0];	
		}

	});	
	
	return true;
}
