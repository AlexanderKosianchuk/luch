//=============================================================
//┏━━━┓╋╋╋╋╋╋╋╋╋╋╋┏┓
//┃┏━━┛╋╋╋╋╋╋╋╋╋╋┏┛┗┓
//┃┗━━┳┓┏┳━━┳━━┳━┻┓┏╋┳━━┳━┓
//┃┏━━┻╋╋┫┏━┫┃━┫┏┓┃┃┣┫┏┓┃┏┓┓
//┃┗━━┳╋╋┫┗━┫┃━┫┗┛┃┗┫┃┗┛┃┃┃┃
//┗━━━┻┛┗┻━━┻━━┫┏━┻━┻┻━━┻┛┗┛
//╋╋╋╋╋╋╋╋╋╋╋╋╋┃┃
//╋╋╋╋╋╋╋╋╋╋╋╋╋┗┛
//=============================================================
function Exception(flightId, apParams, bpParams, refParamArr, associativeParamsArr, placeholder, data, xAxis, yAxesArr){
			
	this.apParams = apParams;
	this.bpParams = bpParams;
	this.paramCount = this.apParams.length + this.bpParams.length;
	this.refParamArr = refParamArr;
	this.excLabelId = 0;
	this.excContainersArr = new Array();
	this.associativeParamsArr = associativeParamsArr;
	this.ph = placeholder;
	this.dataset = data;
	this.xAxis = xAxis;
	this.yAxesArr = yAxesArr;
	
	this.scriptAddrDummyExc = location.protocol + '//' + 
		location.host + "/asyncFlightExc.php?flightId=" + 
		flightId + "&refParam=";
	
	this.excRectanglesArr = new Array();
	this.barContainersArr = new Array();
	this.barMainContainersArr = new Array();
	this.linesContainersArr = new Array();
	
}
//=============================================================

//=============================================================
Exception.prototype.ReceiveExcepions = function(){
	//receive flight exceptions for selected params
	
	var self = this;
	for(var i = 0; i < this.paramCount; i++){
		var refParam = this.refParamArr[i],
			scriptAddrExc = this.scriptAddrDummyExc + refParam;
		console.log("e " + scriptAddrExc);
		
		$.ajax({
			url: scriptAddrExc,
			dataType: 'json',
			success: function(inData) { return inData; },
			async: false,
		}).done(function(excDataArray){
			if(excDataArray != 'null'){
				if(excDataArray.length > 0) {
					for(var j = 0; j < excDataArray.length; j++) {
						
						var paramDetails = self.associativeParamsArr[refParam];
			
						self.BuildExcContainer(self.excLabelId, 
							refParam, 
							excDataArray[j][0], //startTime
							excDataArray[j][1], //endTime
							excDataArray[j][2], //code
							excDataArray[j][3], //value
							decodeURIComponent(escape(excDataArray[j][4])),//comment encoded because or cyrillic
							excDataArray[j][5], //visualization type
							paramDetails[0], //yAxNum
							paramDetails[1]);  //color
						
						var selector = 'div#excLabel' + self.excLabelId;
						self.excLabelId++;
						self.excContainersArr.push($(selector));
					};
				};
			} else {
				console.log("No flight exceptions");
			}
		}).fail(function(e){
			console.log(e);
		});
	};
};
//=============================================================

//=============================================================
Exception.prototype.BuildExcContainer = function(id, refParam, startTime, endTime, 
	content, value, comment, visType, yAxNum, color) {
	
	var self = this;
		//$startTime, $endTime, $code, $value, $excComment, $visualization
		//VISUALIZATION TYPES
		//A - autoshow 
		//E - right verticle line
		//S - left verticle line
		//U - underground rectangle
		//C - full description in box
			
		var sender = $('<div/>', {
			id: 'excLabel' + id,
			class: 'ExcLabel',
			'data-refParam': refParam,
			'data-time': startTime,
			'data-endtime': endTime,
			'data-value': value,
			'data-yax': yAxNum,
			'data-id': id,
			'data-code': content,
			'data-title': comment,
			'data-supporttoolsshown': false,
			'data-subscribers': '',
			title: comment,
			html: content})
		.css({
			"position": 'absolute',
			"display": 'none',
			"border": '1px solid #999',
			"font-size": '14px',
			"padding": '2px',
			"background-color": "#" + color,
			"border-radius": '3px',
			"z-index": '1',
			"opacity": '0.6'})
		.appendTo("body").fadeIn(200);	
		
		if(visType.indexOf("C") > -1){
			var curSubscribers = sender.data("subscribers"),
				newSubscriber = curSubscribers;
			
			if(curSubscribers.length > 0){
				newSubscriber += ",";
			}
			
			newSubscriber += "self";
		}
		
		if(visType.indexOf("C") > -1){
			var curSubscribers = sender.data("subscribers"),
				newSubscriber = curSubscribers;
			
			if(curSubscribers.length > 0){
				newSubscriber += ",";
			}
			
			newSubscriber += "self";
			sender.data("subscribers", newSubscriber);
		}
		
		if(visType.indexOf("U") > -1){
			var curSubscribers = sender.data("subscribers"),
				newSubscriber = curSubscribers;
			
			if(curSubscribers.length > 0){
				newSubscriber += ",";
			}
			
			newSubscriber += 'svgRectangle' + id;
			sender.data("subscribers", newSubscriber);
		}
		
		if(visType.indexOf("S") > -1){
			var curSubscribers = sender.data("subscribers"),
				newSubscriber = curSubscribers;
			
			if(curSubscribers.length > 0){
				newSubscriber += ",";
			}
			
			newSubscriber += 'excMainSection' + 'S' + id;
			sender.data("subscribers", newSubscriber);
		}
		
		if(visType.indexOf("E") > -1){
			var curSubscribers = sender.data("subscribers"),
				newSubscriber = curSubscribers;
			
			if(curSubscribers.length > 0){
				newSubscriber += ",";
			}
			
			newSubscriber += 'excMainSection' + 'E' + id ;
			sender.data("subscribers", newSubscriber);
		}
		
		if(visType.indexOf("A") > -1){
			self.ShowHideExcSupportTools(sender, self.xAxis);
		}
			
		sender[0].onclick = function(){
			self.ShowHideExcSupportTools(sender, self.xAxis);	
		}
};
//=============================================================

//=============================================================
//
Exception.prototype.ShowHideExcSupportTools = function(sender, xAxis) {
	this.ShowHideExcFullText(sender);
	this.ShowHideExcRectangle(sender, xAxis);
	this.ShowHideExcStartSection(sender, xAxis);
	this.ShowHideExcEndSection(sender, xAxis);
	
	this.UpdateExcSupportTools(xAxis, this.yAxesArr);
	
	var shown = sender.data('supporttoolsshown');
	sender.data('supporttoolsshown', !shown);
}
//=============================================================

//=============================================================
//
Exception.prototype.UpdateExcSupportTools = function(xAxis, yAxArr){
	this.UpdateExcContainersPos(xAxis, yAxArr);
	this.UpdateRectanglePos(xAxis);
	this.UpdateBarContainersPos(xAxis, yAxArr);
}
//=============================================================

//=============================================================
//updating flight exceptions on plotpan or plotzoom
Exception.prototype.UpdateExcContainersPos = function(xAxis, yAxArr){
	var xMin = xAxis.min.toFixed(0) - 1, 
		xMax = xAxis.max.toFixed(0);
		
	for(var i = 0; i < this.excContainersArr.length; i++)
	{
		var excCont = this.excContainersArr[i],
			excTime = excCont.data('time'),
			excValue = excCont.data('value'),
			yAxNum = excCont.data('yax'),
			yAxis = yAxArr[yAxNum],
			yMin = yAxArr[yAxNum].min,
			yMax = yAxArr[yAxNum].max,
			excCoordX = xAxis.p2c(excTime),
			excCoordY = yAxis.p2c(excValue);
			
		if(((excTime > xMin) && (excTime < xMax)) && 
		   ((excValue > yMin) && (excValue < yMax))){
			excCont.css({
				top: excCoordY + 40,
				left: excCoordX + 15, } ).
			fadeIn(200);				
		} else {
			excCont.fadeOut(200);				
		};
	};
};
//=============================================================

//=============================================================
//
Exception.prototype.ShowHideExcFullText = function(sender){
	var excCont = sender,
		excContId = excCont.data('id'),
		excContSubscribers = excCont.data('subscribers');
	
	if(excContSubscribers.indexOf("self") > -1) {	
		if(excCont.text() == excCont.data('code')){
			var fullText = excCont.data('code') + ";" + excCont.data('title');
			fullText = fullText.replace(/;/g, ';<br>');
			excCont.html(fullText);
			excCont.removeAttr('title');
		} else {
			excCont.html(excCont.data('code'));
			excCont.attr('title', excCont.data('title'));
		}
	}
}
//=============================================================

//=============================================================
//
Exception.prototype.ShowHideExcRectangle = function(sender, xAxis){
	var excCont = sender,
		excContId = excCont.data('id'),
		excContSubscribers = excCont.data('subscribers');
		
	if(excContSubscribers.indexOf("svgRectangle") > -1) {
		var rectangleSelector = $("svg#svgRectangle" + excContId);
		
		if(rectangleSelector.length) {	
			rectangleSelector.remove();
		} else {
			var excStartTime = excCont.data('time'),
				excEndTime = excCont.data('endtime'),
				excColor = excCont.css("background-color");
			
			var r = this.PutRectangle(excContId, excStartTime, excEndTime, excColor, xAxis);
			this.excRectanglesArr.push(r);
		}
	}
}
//=============================================================

//=============================================================
//
Exception.prototype.PutRectangle = function(id, startTime, endTime, color, xAxis){
	var self = this,
		xMin = xAxis.min.toFixed(0) - 1, 
		xMax = xAxis.max.toFixed(0),
		rectangleLeft = xAxis.p2c(startTime),
		rectangleRight = xAxis.p2c(endTime),
		rectangleWidth = rectangleRight - rectangleLeft;
	
	return $('<svg></svg>', {
		id: 'svgRectangle' + id,
		class: 'SvgRectangle',
		'data-starttime': startTime,
		'data-endtime': endTime })
	.css({
		"top": 42 + self.ph.height() - 32 - id * 2 + 'px',
		"left": 14 + rectangleLeft + 'px',
		"width": rectangleWidth + 'px',
		"height": 4 + 'px',
		"position": 'absolute',
		"opacity": '0.5',
		"background-color": "#" + color})
	.appendTo("body");
};
//=============================================================

//=============================================================
//
Exception.prototype.UpdateRectanglePos = function(xAxis){
	var xMin = xAxis.min.toFixed(0) - 1, 
		xMax = xAxis.max.toFixed(0);
		
	for(var i = 0; i < this.excRectanglesArr.length; i++)
	{
		var excRect = this.excRectanglesArr[i],
			rectStartTime = excRect.data('starttime'),
			rectEndTime = excRect.data('endtime');
		
		//console.log("xMin " + xMin + " xMax " + xMax + " rectStCoord " + rectStartTime + " rectEnCoord " + rectEndTime);
			
		if(((rectStartTime >= xMin) && (rectStartTime <= xMax)) && ((rectEndTime >= xMin) && (rectEndTime <= xMax))){
			var rectStCoord = xAxis.p2c(rectStartTime),
				rectWidth = xAxis.p2c(rectEndTime) - xAxis.p2c(rectStartTime);
			
			excRect.css({
				left: 14 + rectStCoord,
				width: rectWidth } ).
			fadeIn(200);
		} else if(((rectStartTime <= xMin) && (rectStartTime <= xMax)) && ((rectEndTime >= xMin) && (rectEndTime <= xMax))){
			var rectStCoord = xAxis.p2c(xMin),
				rectWidth = xAxis.p2c(rectEndTime) - xAxis.p2c(xMin);
			
			excRect.css({
				left: 14 + rectStCoord,
				width: rectWidth } ).
			fadeIn(200);
		} else if(((rectStartTime >= xMin) && (rectStartTime <= xMax)) && ((rectEndTime >= xMin) && (rectEndTime >= xMax))){
			var rectStCoord = xAxis.p2c(rectStartTime),
				rectWidth = xAxis.p2c(xMax) - xAxis.p2c(rectStartTime);
			
			excRect.css({
				left: 14 + rectStCoord,
				width: rectWidth } ).
			fadeIn(200);
		} else if(((rectStartTime <= xMin) && (rectStartTime <= xMax)) && ((rectEndTime >= xMin) && (rectEndTime >= xMax))){
			var rectStCoord = xAxis.p2c(xMin),
				rectWidth = xAxis.p2c(xMax) - xAxis.p2c(xMin);
			
			excRect.css({
				left: 14 + rectStCoord,
				width: rectWidth } ).
			fadeIn(200);
		} else {
			excRect.fadeOut(200);				
		};
	};	
};
//=============================================================

//=============================================================
//
Exception.prototype.ShowHideExcStartSection = function(sender, xAxis){
	var self = this,
		excCont = sender,
		excContId = excCont.data('id'),
		excContCode = excCont.data('code'),
		excContSubscribers = excCont.data('subscribers');

	//do we have left section?
	if(excContSubscribers.indexOf("excMainSection" + "S") > -1) {	
		var leftExcSection = $("div#excMainSection" + "S" + excContId);
		
		//do we we need to show it or to hide
		if(excCont.data('supporttoolsshown')){
			
			//maybe it is already removed by user
			if (leftExcSection.length > 0){
				self.RemoveSectionBar(leftExcSection);
			}
		} else {
			//we need to show left section
			var excContStartTime = excCont.data('time'),
				apValues = self.GetValue(self.dataset, excContStartTime),
				bpValues = self.GetBinaries(self.dataset, excContStartTime),
				color = excCont.css('background-color');
				color = self.rgb2hex(color);
			
			self.PutSectionBar(excContId, "S", excContCode, excContStartTime, apValues, bpValues, color);
			
		}	
	}
}
//=============================================================

//=============================================================
//
Exception.prototype.ShowHideExcEndSection = function(sender, xAxis){
	var self = this,
		excCont = sender,
		excContId = excCont.data('id'),
		excContCode = excCont.data('code'),
		excContSubscribers = excCont.data('subscribers');

	//do we have left section?
	if(excContSubscribers.indexOf("excMainSection" + "E") > -1) {	
		var rightExcSection = $("div#excMainSection" + "E" + excContId);
		
		//do we we need to show it or to hide
		if(excCont.data('supporttoolsshown')){
						
			//maybe it is already removed by user
			if (rightExcSection.length > 0){
				self.RemoveSectionBar(rightExcSection);
			}
		} else {
			//we need to show left section
			var excContStartTime = excCont.data('endtime'),
				apValues = self.GetValue(self.dataset, excContStartTime),
				bpValues = self.GetBinaries(self.dataset, excContStartTime),
				color = excCont.css('background-color');
				color = self.rgb2hex(color);
			
			self.PutSectionBar(excContId, "E", excContCode, excContStartTime, apValues, bpValues, color);
			
		}
	}
}
//=============================================================

//=============================================================
//
Exception.prototype.PutSectionBar = function(senderId, lay, excCode, x, apValues, bpValues, senderColor){
	var self = this,
		startId = this.barContainersArr.length;
	
	for (var i = 0; i < apValues.length; i++) {
		var id = "excBarLabel" + (this.barContainersArr.length + 1),
			refParam = self.refParamArr[i],
			time = x,
			value = apValues[i],
			yAxNum = i,
			color = self.associativeParamsArr[refParam][1];
		
		var s = self.PutBarContainer(id, refParam, time, value, value, yAxNum, color);
		self.barContainersArr.push(s);
	}

	var j = 0;
	for (var i = apValues.length; i < self.refParamArr.length; i++) {
		var id = 'excBarLabel' + (self.barContainersArr.length + 1),
			refParam = self.refParamArr[i],
			time = x,
			value = 1,
			content = bpValues[j],
			yAxNum = i,
			color = self.associativeParamsArr[refParam][1];
		
		if(content == true) {
			var s = self.PutBarContainer(id, refParam, time, value, "T", yAxNum, color);
			self.barContainersArr.push(s);
		}
		
		j++;
	}
	var s = self.CreateLineContainer(senderId, lay, x, senderColor);
	self.linesContainersArr.push(s);
	s = self.PutBarMainContainer(senderId, lay, x, self.toHHMMSS(x), excCode, startId, 
			self.barContainersArr.length, self.linesContainersArr.length, senderColor);
	self.barMainContainersArr.push(s);
	
	//delete bar on mainLable click
	s[0].ondblclick = function(){
		self.RemoveSectionBar(s);	
	};
	
	return s;

};
//=============================================================

//=============================================================
//
Exception.prototype.PutBarMainContainer = function(id, lay, time, content, excCode, startId, endId, lineId, color) {
	return $('<div/>', {
		id: 'excMainSection' + lay + id,
		class: 'excMainSection',
		'data-time': time,
		'data-startid': startId,
		'data-endid': endId,
		'data-lineid': lineId,
		html: lay + "-" + content + "(" + excCode + ")"})
	.css({
		"position": 'absolute',
		"display": 'none',
		"border": '1px solid #999',
		"padding": '2px',
		"top": '20px',
		"border-radius": '3px',
		"color": 'black',
		"background-color" : '#' + color,
		"font-size": '12px',
		"font-weight": "bold",
		"opacity": '0.6' })
	.appendTo("body");
};

//=============================================================

//=============================================================
//
Exception.prototype.PutBarContainer = function(id, refParam, time, value, content, yAxNum, color) {
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
		"font-weight": "bold",
		"text-shadow": '1px 1px 0px grey, 0 0 7px white',
		"background-color" : 'transpatant',
		"opacity": '0.75'})
	.appendTo("body");
};
//=============================================================

//=============================================================
//
Exception.prototype.CreateLineContainer = function(id, lay, time, color) {
	self = this;
	//lay - left(l) or right(r)
	return $('<svg></svg>', {
		id: 'svgExcLines' + lay + id,
		'data-time': time })
	.css({
		"top": '43px',
		"width": '1px',
		"height": self.ph.height() - 30,
		"position": 'absolute',
		"background-color": "#" + color})
	.appendTo("body");
};
//=============================================================

//=============================================================
//updating bars on plotpan or plotzoom
Exception.prototype.UpdateBarContainersPos = function(xAxis, yAxArr){
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
				left: excCoordX + 15, } ).
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
				left: excCoordX + 10 } ).
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
				left: excCoordX + 14 } ).
			fadeIn(200);				
		} else {
			barCont.fadeOut(200);				
		};
	};
};
//=============================================================

//=============================================================
//
Exception.prototype.RemoveSectionBar = function(mainBar) {
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
//Get value by x coord by interpolating
Exception.prototype.GetValue = function(dataset, x) {	
	var yArr = Array();
	for (var i = 0; i < this.apParams.length; ++i) {
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
//=============================================================
//Get value by x coord by interpolating
Exception.prototype.GetBinaries = function(dataset, x){	
	var bpPrasentArray = new Array();
	for (var i = this.apParams.length; i < this.paramCount; ++i) {
		var series = dataset[i],
			bpPrasent = false,
			notFound = true;
			
		// Find the nearest points, x-wise
		for (var j = 0; j < series.data.length; ++j) {
			if(series.data[j] != null)
			{
				if (series.data[j][0] > x) {
					notFound = false;	
					break;
				} else {
					notFound = true;					
				};
			};
		}
				
		if((j > 0) && (!notFound)){
			if(series.data[j - 1] != null){
				bpPrasent = true;
			};
		}
		
		bpPrasentArray.push(bpPrasent);
	}
	return bpPrasentArray;
}
//=============================================================

//=============================================================
//UNIX_timestamp to hour+':'+min+':'+sec
Exception.prototype.toHHMMSS = function(UNIX_timestamp){
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
//=============================================================

//=============================================================
Exception.prototype.rgb2hex = function(rgb) {
	rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
	return "" + this.hex(rgb[1]) + this.hex(rgb[2]) + this.hex(rgb[3]);
}
Exception.prototype.hex = function(x) {
	var hexDigits = new Array
    ("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"); 
	
	return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
}