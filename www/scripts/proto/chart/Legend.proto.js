//=============================================================
//┏┓╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋┏┓
//┃┃╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋┃┃
//┃┃╋╋┏━━┳━━┳━━┳━┓┏━┛┃
//┃┃╋┏┫┃━┫┏┓┃┃━┫┏┓┫┏┓┃
//┃┗━┛┃┃━┫┗┛┃┃━┫┃┃┃┗┛┃
//┗━━━┻━━┻━┓┣━━┻┛┗┻━━┛
//╋╋╋╋╋╋╋┏━┛┃
//╋╋╋╋╋╋╋┗━━┛
//=============================================================

function Legend(flightId, legendContainer, apParams, bpParams, associativeParamsArr,
	plotAxes, plotDataset, placeholder, contentChartContainer, actions){

	this.flightId = flightId;
	this.actions = actions;
	this.dataset = plotDataset;
	this.ph = placeholder;
	this.ccCont = contentChartContainer;
	this.axes = plotAxes;
	this.pos = new Object();
	this.apArr = apParams;
	this.bpArr = bpParams;
	this.apCount = 0;
	this.bpCount = 0;
	this.paramCount = 0;
	if(this.apArr != null){	
		this.apCount = this.apArr.length;
		this.paramCount += this.apCount;
	}
	
	if(this.bpArr != null){	
		this.bpCount = this.bpArr.length;
		this.paramCount += this.bpCount;
	}
	this.associativeParamsArr = associativeParamsArr;
	this.legendTitlesArr = new Array();
	this.legndCont = legendContainer;
	this.legendTitlesNotReceived = true;
	this.legendTitlesNotSet = true;
	this.updateLegendTimeout = null;
	
	this.paramInfo = new Array();
	this.dfr = $.Deferred();
	
	this.visirTimeBox = $('<div id="visirTimeBox"></div>').css({
		'background-color': "transparent",
		'position': "absolute",
		'font-size': '14px',
		'top': '1px'
	});
	this.ccCont.append(this.visirTimeBox);
	
	$("<div></div>")
	.attr("id","leadParamValBox")
	.addClass("LeadParamValBox")	
	.appendTo($('body'));
	this.leadParamValBox = $("#leadParamValBox");
	
	this.displayNeed = false;
	this.vizirBarContainer = Object();
	this.vizirFreezePos = 0;
	this.crosshairLocked = false;
	this.showSeriesLabelsNeed = false;
	this.seriesLabelsValues = new Array();
	this.seriesLabelsTime = 0;
	this.vLineColor = '#A9A9A9';
	
	this.barContainersArr = new Array();
	this.barMainContainersArr = new Array();
	this.linesContainersArr = new Array();
	this.horizontsContainersArr = new Array();
	this.horizontsValueContainersArr = new Array();
	this.seriesNamContainersArr = new Array();
	this.seriesLeftLabelsContainersArr = new Array();
}

Legend.prototype.UpdateLegend = function(pos, valuesArr, 
	binariesArr) 
{
	//update each time legends because it can be lost after zoom or pan
	this.updateLegendTimeout = null;
	var legndLabls = this.legndCont.find(".legendLabel");
	
	if (pos.x < this.axes.xaxis.min || pos.x > this.axes.xaxis.max ||
		pos.y < this.axes.yaxis.min || pos.y > this.axes.yaxis.max) {
		return;
	}
	//update legend only for ap
	for (var i = 0; i < this.apCount; ++i) {
		var series = this.dataset[i];
		y = valuesArr[i];
		s = series.label.substring(0, series.label.indexOf('='));
		legndLabls.eq(i).text(s + " = " + Number(y).toFixed(2));
	}
	
	//update legend bp
	var j = 0;
	for (var i = this.apCount; i < this.paramCount; ++i) {
		var series = this.dataset[i];
		if(binariesArr[j] == true){
			legndLabls.eq(i).css({
				"color" : "red",
			});
			s = series.label.substring(0, series.label.indexOf('='));
			legndLabls.eq(i).text(s + " = " + "T");
		} else {
			legndLabls.eq(i).css({
				"color" : "#525552",
			});
			s = series.label.substring(0, series.label.indexOf('='));
			legndLabls.eq(i).text(s + " = " + "F");
		}
		j++;
	}
	
	//if legend table was rebuils afret zoom or resize append titles
	if(this.legendTitlesNotSet) {
		for (var i = 0; i < (this.paramCount); ++i) {
			var $this = legndLabls.eq(i);
			$this.attr("title", this.paramInfo[i]);
		}
		this.legendTitlesNotSet = false;			
	};
};
//=============================================================

//=============================================================
//highlight overed series
Legend.prototype.HighlightLegend = function(seriesIndex) 
{
	legndLabls = this.legndCont.find(".legendLabel");
	if(seriesIndex >= 0){
		for($i = 0; $i < legndLabls.length; $i++) {
			legndLabls.eq($i ).css({
				"background-color" : "transparent",
			});
		}
		legndLabls.eq(seriesIndex).css({
			"background-color" : "rgb(10,10,220, 0.15)",
		});
	} else {
		for($i = 0; $i < legndLabls.length; $i++) {
			legndLabls.eq($i ).css({
				"background-color" : "transparent",
			});
		}
	}

};
//=============================================================

//=============================================================
//updating legends by vizir and appendint text attr when first call for tool tips
Legend.prototype.ReceiveLegend = function(){
	var legndLabls = this.legndCont.find(".legendLabel"),
		self = this;
	if(this.legendTitlesNotReceived) {
		var paramCodes = self.apArr.concat(self.bpArr),		
		pV = {
			action: self.actions["rcvLegend"],
			data:{
				flightId: self.flightId,
				paramCodes: paramCodes
			}
		};

		$.ajax({
			data: pV,
			type: "POST",
			url: CHART_SRC,
			dataType: 'json',
			success: function(inData){
				self.dfr.resolve;					
			},
			async: true,
		}).done(function(inData){
			for (var i = 0; i < self.paramCount; i++) {
				var $this = legndLabls.eq(i);
				//console.log(inData[i] + " " + i);
				self.paramInfo.push(inData[i]);
				$this.attr("title", inData[i]);
				self.legendTitlesArr.push(inData[i]);
			};
		}).fail(function(answ){
			console.log(answ);
		});
		
 		this.legendTitlesNotReceived = false;
	};
};
//=============================================================

//=============================================================
//show visir time in div above it
Legend.prototype.ShowVisirTime = function(){
	if (this.pos.x > this.axes.xaxis.min && this.pos.x < this.axes.xaxis.max) {
		this.visirTimeBox.text(this.toHHMMSS(this.pos.x));
		this.visirTimeBox.css({'left': this.pos.pageX});
	}
};
//=============================================================

//=============================================================
//show lead param val in div above point
Legend.prototype.ShowLeadParamVal = function(val, label){
	if (this.pos.x > this.axes.xaxis.min && this.pos.x < this.axes.xaxis.max) {
		this.leadParamValBox.text(label + " = " + val);
		this.leadParamValBox.css({
			'left': this.pos.pageX,
			'top': this.pos.pageY - 25,
			});
	}
};
//=============================================================

//=============================================================
//
Legend.prototype.AppendSectionBar = function(){
	var self = this;
	if (this.pos.x > this.axes.xaxis.min && this.pos.x < this.axes.xaxis.max) {
		var startId = this.barContainersArr.length,
			legndLabls = this.legndCont.find(".legendLabel"),
			labelText = legndLabls.eq(0).text(),
			value = labelText.substring(labelText.indexOf('=') + 1, labelText.length);
		if(value != null){
			for (var i = 0; i < this.apCount; i++) {
				var labelText = legndLabls.eq(i).text(),
					id = "barLabel" + (this.barContainersArr.length + 1),
					refParam = this.apArr[i],
					time = this.pos.x,
					value = labelText.substring(labelText.indexOf('=') + 2, labelText.length),
					yAxNum = i,
					color = this.associativeParamsArr[refParam][1];
				
				var s = this.CreateBarContainer(id, refParam, time, value, value, yAxNum, color);
				this.barContainersArr.push(s);
			}
		}

		var j = 0;
		for (var i = this.apCount; i < this.paramCount; i++) {
			var labelText = legndLabls.eq(i).text(),
				id = 'barLabel' + (this.barContainersArr.length + 1),
				refParam = this.bpArr[j],
				time = this.pos.x,
				value = 1,
				content = labelText.substring(labelText.indexOf('=') + 2, labelText.length),
				yAxNum = i,
				color = this.associativeParamsArr[refParam][1];
			
			if(content == "T") {
				var s = this.CreateBarContainer(id, refParam, time, value, content, yAxNum, color);
				this.barContainersArr.push(s);
			}
			j++;
		}
		var s = this.CreateLineContainer(this.pos.x);
		this.linesContainersArr.push(s);
		s = this.CreateBarMainContainer(this.pos.x, self.toHHMMSS(this.pos.x), startId, 
				this.barContainersArr.length,
				this.linesContainersArr.length);
		this.barMainContainersArr.push(s);
		
		//delete bar on mainLable click
		s[0].ondblclick = function(){
			self.RemoveSectionBar(s);	
		};
			
		return s;
	} else {
		return null;
	}
};
//=============================================================

//=============================================================
//
Legend.prototype.CreateBarMainContainer = function(time, content, startId, endId, lineId) {
	 var self = this,
	 barMainLabel = $('<div/>', {
		id: 'barMainLabel' + (self.barMainContainersArr.length + 1),
		class: 'BarMainLabel',
		'data-time': time,
		'data-startid': startId,
		'data-endid': endId,
		'data-lineid': lineId,
		html: content})
	.css({
		"position": 'absolute',
		"display": 'none',
		"border": '1px solid #999',
		"padding": '2px',
		"top": '18px',
		"border-radius": '3px',
		"color": 'black',
		"background-color" : 'white',
		"font-size": '12px',
		"opacity": '0.6' })
	.appendTo(self.ccCont);
	
	return barMainLabel;

};
//=============================================================

//=============================================================
//
Legend.prototype.CreateBarContainer = function(id, refParam, time, value, content, yAxNum, color) {
	var self = this;
	
	return $('<div/>', {
		id: id,
		class: 'BarLabel',
		'data-refParam': refParam,
		'data-time': time,
		'data-yAx': yAxNum,
		'data-value': value,
		html: content})
	.css({
		"position": 'absolute',
		"display": 'none',
		"border": '0px',
		"padding": '2px',
		"color": "#" + color,
		"font-size": '14px',
		"text-shadow": '1px 1px 0px grey, 0 0 7px white',
		"background-color" : 'transpatant',
		"opacity": '0.75'})
	.appendTo(self.ccCont);
};
//=============================================================

//=============================================================
//
Legend.prototype.CreateLineContainer = function(time) {
	var self = this;
	return $('<svg></svg>', {
		id: 'svgLines' + (self.linesContainersArr.length + 1),
		'data-time': time })
	.css({
		"top": '38px',
		"width": '1px',
		"height": self.ph.height() - 30,
		"position": 'absolute',
		"background-color": self.vLineColor})
	.appendTo(self.ccCont);
};
//=============================================================

//=============================================================
//
Legend.prototype.RemoveSectionBar = function(mainBar) {
	var startId = mainBar.data('startid'),
		endId = mainBar.data('endid'),
		lineId = mainBar.data('lineid');
	this.linesContainersArr[lineId - 1].remove();
	for(var i = startId; i < endId; i++)
	{
		this.barContainersArr[i].remove();
	};	
	return mainBar.remove();
};
//=============================================================

//=============================================================
//updating bars on plotpan or plotzoom
Legend.prototype.UpdateBarContainersPos = function(xAxis, yAxArr){
	var xMin = xAxis.min.toFixed(0), 
		xMax = xAxis.max.toFixed(0);
		
	for(var i = 0; i < this.barContainersArr.length; i++){
		var barCont = this.barContainersArr[i],
			barTime = barCont.data('time'),
			barValue = barCont.data('value'),
			yAxNum = barCont.data('yax'),
			yAxis = yAxArr[yAxNum],
			yMin = yAxArr[yAxNum].min,
			yMax = yAxArr[yAxNum].max,
			excCoordX = xAxis.p2c(barTime),
			excCoordY = yAxis.p2c(barValue);
			
		if(((barTime > xMin) && (barTime < xMax)) && 
		   ((barValue > yMin) && (barValue < yMax))){
			barCont.css({
				top: excCoordY + 20,
				left: excCoordX + 5, } ).
			fadeIn(200);				
		} else {
			barCont.fadeOut(200);				
		};
	};	
	
	for(var i = 0; i < this.barMainContainersArr.length; i++){
		var barCont = this.barMainContainersArr[i],
			barTime = barCont.data('time'),
			excCoordX = xAxis.p2c(barTime);
			
		if((barTime > xMin) && (barTime < xMax)){
			barCont.css({
				left: excCoordX + 5 } ).
			fadeIn(200);				
		} else {
			barCont.fadeOut(200);				
		};
	};	
	
	for(var i = 0; i < this.linesContainersArr.length; i++){
		var barCont = this.linesContainersArr[i],
			barTime = barCont.data('time'),
			excCoordX = xAxis.p2c(barTime);
			
		if((barTime > xMin) && (barTime < xMax)){
			barCont.css({
				"left": excCoordX + 6,
				"height": this.ph.height() - 30
			})
			.fadeIn(200);				
		} else {
			barCont.fadeOut(200);				
		};
	};
	
	for(var i = 0; i < this.horizontsContainersArr.length; i++){
		var phPos = this.ph.offset(),
			horLine = this.horizontsContainersArr[i],
			horLineVal = horLine.data('value'),
			yAxNum = horLine.data('yax'),
			yMin = yAxArr[yAxNum].min,
			yMax = yAxArr[yAxNum].max,			
			yAxis = yAxArr[yAxNum],
			excCoordY = yAxis.p2c(horLineVal) + phPos.top;
			
		if((horLineVal > yMin) && (horLineVal < yMax)){
			horLine.css({
				top: excCoordY + 7//just missing pix for corect disp
			}).
			fadeIn(200);				
		} else {
			horLine.fadeOut(200);				
		};
	};	
	
	for(var i = 0; i < this.horizontsValueContainersArr.length; i++){
		var phPos = this.ph.offset(),
			horValueCont = this.horizontsValueContainersArr[i],
			horValue = horValueCont.html(),
			yAxNum = horValueCont.data('yax'),
			yMin = yAxArr[yAxNum].min,
			yMax = yAxArr[yAxNum].max,			
			yAxis = yAxArr[yAxNum],
			excCoordY = yAxis.p2c(horValue) + phPos.top;
			
		if((horValue > yMin) && (horValue < yMax)){
			horValueCont.css({
				top: excCoordY + 7//just missing pix for corect disp
			}).
			fadeIn(200);				
		} else {
			horValueCont.fadeOut(200);				
		};
	};	

};
//=============================================================

//=============================================================
//resize lines to use method on graph container resizing
Legend.prototype.ResizeLines = function(){
	var self = this;
	for(var i = 0; i < self.linesContainersArr.length; i++)
	{
		var barCont = self.linesContainersArr[i];
		barCont.css("height", self.ph.height() - 30);
	}
};

//=============================================================

//=============================================================
//
Legend.prototype.CreateHorizont = function(yAxNum) {
	var self = this,
		e = self.CreateHorizontContainer(yAxNum),
		h = self.CreateHorizontValueContainer(yAxNum);
	self.horizontsContainersArr.push(e);
	self.horizontsValueContainersArr.push(h);
	
	//delete bar on mainLable click
	h[0].ondblclick = function(){
		self.RemoveHorizont(h);	
	};
};
//=============================================================

//=============================================================
//
Legend.prototype.CreateHorizontContainer = function(yAxNum) {
	var self = this;
	return $('<svg></svg>', {
		id: 'svgHorLine' + (self.horizontsContainersArr.length + 1),
		"data-yAx": yAxNum,
		"data-value": self.pos["y"+(yAxNum + 1)]})
		.css({
			"top": self.pos.pageY,
			"left": '14px',
			"width": self.ph.width() + self.ph.offset().left - 28,//left is negative num. 28 just missing pix num
			"height": '1px',
			"position": 'absolute',
			"background-color": 'darkgrey'})
		.appendTo(self.ccCont);
};
//=============================================================

//=============================================================
//
Legend.prototype.CreateHorizontValueContainer = function(yAxNum) {
	var self = this;
	return $('<div></div>', {
		id: 'horValue' + (self.horizontsValueContainersArr + 1),
		class : 'horValueLabel',
		"data-yAx": yAxNum,
		"data-horId": self.horizontsContainersArr.length,
		html: Number(self.pos["y"+(yAxNum+1)]).toFixed(2) })
		.css({
			"position": 'absolute',
			"display": 'block',
			"border": '1px solid #999',
			"padding": '2px',
			"top": self.pos.pageY,
			"left": '1px',
			"border-radius": '3px',
			"color": 'black',
			"background-color" : 'white',
			"font-size": '12px',
			"opacity": '0.6' })
		.appendTo(self.ccCont);
};
//=============================================================

//=============================================================
//
Legend.prototype.SuportHorizontAfterCreation = function() {
	var self = this,
	horLine = self.horizontsContainersArr[self.horizontsContainersArr.length - 1],
	horValue = self.horizontsValueContainersArr[self.horizontsValueContainersArr.length - 1],
	yAxNum = horLine.data('yax'),		
	value = self.pos["y"+(yAxNum + 1)];
	horLine.attr({
		"data-value": value
	}).css({
		top: self.pos.pageY
	});
	
	horValue.html(Number(value).toFixed(2))
	.css({
		top: self.pos.pageY
	});
};
//=============================================================

//=============================================================
//
Legend.prototype.RemoveHorizont = function(horizont) {
	var horId = horizont.data('horid');
	this.horizontsContainersArr[horId].remove();	
	return horizont.remove();
};
//=============================================================

//=============================================================
//
Legend.prototype.ShowSeriesNames = function(xAxis, yAxArr) {
	var displayNamesState = this.displayNeed ? 'block' : 'none';
	//if cursor in placeholder
	if(this.pos.x > this.axes.xaxis.min && this.pos.x < this.axes.xaxis.max) {
	//if series name bar not build, and titles already received then do it 
		if((!(this.seriesNamContainersArr.length > 0)) && 
			(this.legendTitlesArr.length > 0)) {
						
			for (var i = 0; i < this.apCount; i++) {
				var id = 'seriesLabel' + (this.seriesNamContainersArr.length + 1),
					refParam = this.apArr[i],
					time = "-",
					yAxNum = i,
					value = "-",
					content = refParam + ", " + this.legendTitlesArr[i],
					color = this.associativeParamsArr[refParam][1];
				var el = this.CreateBarContainer(id, refParam, time, value, content, yAxNum, color);
				this.seriesNamContainersArr.push(el);
			}

	
			var j = 0;
			for (var i = this.apCount; i < this.paramCount; i++) {
				var id = 'seriesLabel' + (this.seriesNamContainersArr.length + 1),
					refParam = this.bpArr[j],
					time = "-",
					value = "1",
					content = refParam + ", " + this.legendTitlesArr[i],
					yAxNum = i,
					color = this.associativeParamsArr[refParam][1];				
					var el = this.CreateBarContainer(id, refParam, time, value, content, yAxNum, color);
					this.seriesNamContainersArr.push(el);
				j++;
			}
		}
		
		for (var i = 0; i < this.apCount; i++) {
			var seriesNamCont = this.seriesNamContainersArr[i],
				labelText = legndLabls.eq(i).text(),
				time = this.pos.x,
				yAxNum = i,
				value = labelText.substring(labelText.indexOf('=') + 2, labelText.length),
				yAxis = yAxArr[yAxNum],
				yMin = yAxArr[yAxNum].min,
				yMax = yAxArr[yAxNum].max,
				excCoordY = yAxis.p2c(value),
				excCoordX = xAxis.p2c(time);
			if((value > yMin) && (value < yMax)){
				seriesNamCont.css({
					'display': displayNamesState,
					top: excCoordY + 20,
					left: excCoordX + 25
				});
			} else {
				seriesNamCont.fadeOut(200);					
			}
		}
	
		var j = 0;
		for (var i = this.apCount; i < this.paramCount; i++) {
			var seriesNamCont = this.seriesNamContainersArr[i],
				time = this.pos.x,
				yAxNum = i,
				value = 1; //always for bp
				yAxis = yAxArr[yAxNum],
				yMin = yAxArr[yAxNum].min,
				yMax = yAxArr[yAxNum].max,
				excCoordX = xAxis.p2c(time),
				excCoordY = yAxis.p2c(value);
			if((value > yMin) && (value < yMax)){
				seriesNamCont.css({
					'display': displayNamesState,
					top: excCoordY + 20,
					left: excCoordX + 25
				});
			} else {
				seriesNamCont.fadeOut(200);					
			}
				
			j++;
		}
	}
};

//=============================================================

//=============================================================
//
Legend.prototype.ShowSeriesLabels = function(xAxis, yAxArr, paramValues) {
	var displayNamesState = this.showSeriesLabelsNeed ? 'block' : 'none';

	//if series name bar not build, and titles already received then do it 
	if((!(this.seriesLeftLabelsContainersArr.length > 0)) && 
		(this.seriesLabelsValues.length > 0) &&
		(this.legendTitlesArr.length > 0)) {
					
		for (var i = 0; i < this.apCount; i++) {
			var id = 'seriesLeftLabel' + (this.seriesLeftLabelsContainersArr.length + 1),
				refParam = this.apArr[i],
				time = "-",
				yAxNum = i,
				value = "-",
				content = refParam + ", " + this.legendTitlesArr[i],
				color = this.associativeParamsArr[refParam][1];
			var el = this.CreateBarContainer(id, refParam, time, value, content, yAxNum, color);
			this.seriesLeftLabelsContainersArr.push(el);
		}


		var j = 0;
		for (var i = this.apCount; i < this.paramCount; i++) {
			var id = 'seriesLeftLabel' + (this.seriesLeftLabelsContainersArr.length + 1),
				refParam = this.bpArr[j],
				time = "-",
				value = "1",
				content = refParam + ", " + this.legendTitlesArr[i],
				yAxNum = i,
				color = this.associativeParamsArr[refParam][1];				
				var el = this.CreateBarContainer(id, refParam, time, value, content, yAxNum, color);
				this.seriesLeftLabelsContainersArr.push(el);
			j++;
		}
	}
	
	for (var i = 0; i < this.apCount; i++) {
		var seriesNamCont = this.seriesLeftLabelsContainersArr[i],
			labelText = legndLabls.eq(i).text(),
			time = this.seriesLabelsTime,
			yAxNum = i,
			value = this.seriesLabelsValues[i],
			yAxis = yAxArr[yAxNum],
			yMin = yAxArr[yAxNum].min,
			yMax = yAxArr[yAxNum].max,
			xMin = this.axes.xaxis.min,
			xMax = this.axes.xaxis.max,
			excCoordY = yAxis.p2c(value),
			excCoordX = xAxis.p2c(time);
		if((value > yMin) && (value < yMax) && 
			(this.seriesLabelsTime > xMin) && (this.seriesLabelsTime < xMax)){
			seriesNamCont.css({
				'display': displayNamesState,
				top: excCoordY + 20,
				left: excCoordX + 5
			});
		} else {
			seriesNamCont.fadeOut(200);					
		}
	}

	var j = 0;
	for (var i = this.apCount; i < this.paramCount; i++) {
		var seriesNamCont = this.seriesLeftLabelsContainersArr[i],
			time = this.seriesLabelsTime,
			yAxNum = i,
			value = 1; //always for bp
			yAxis = yAxArr[yAxNum],
			yMin = yAxArr[yAxNum].min,
			yMax = yAxArr[yAxNum].max,
			xMin = this.axes.xaxis.min,
			xMax = this.axes.xaxis.max,
			excCoordX = xAxis.p2c(time),
			excCoordY = yAxis.p2c(value);
		if((value > yMin) && (value < yMax) && 
			(this.seriesLabelsTime > xMin) && (this.seriesLabelsTime < xMax)){
			seriesNamCont.css({
				'display': displayNamesState,
				top: excCoordY + 20,
				left: excCoordX + 5
			});
		} else {
			seriesNamCont.fadeOut(200);					
		}
			
		j++;
	}

};
//=============================================================

//=============================================================
//UNIX_timestamp to hour+':'+min+':'+sec
Legend.prototype.toHHMMSS = function(UNIX_timestamp){
	 var a = new Date(UNIX_timestamp);
	 //var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
     /*var year = a.getFullYear();
     var month = months[a.getMonth()];
     var date = a.getDate();*/
     var hour = a.getHours(),
     	 min = a.getMinutes(),
     	 sec = a.getSeconds();
     
     if(hour.toString().length < 2){
    	 hour = '0' + hour;
     }
     
     if(min.toString().length < 2){
    	 min = '0' + min;
     }
     
     if(sec.toString().length < 2){
    	 sec = '0' + sec;
     }

     var time = /*date+','+month+' '+year+' '+*/hour+':'+min+':'+sec ;
     return time;
}