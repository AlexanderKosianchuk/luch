//=============================================================
//┏━━━┓
//┃┏━┓┃
//┃┗━┛┣━━┳━┳━━┳┓┏┓
//┃┏━━┫┏┓┃┏┫┏┓┃┗┛┃
//┃┃╋╋┃┏┓┃┃┃┏┓┃┃┃┃
//┗┛╋╋┗┛┗┻┛┗┛┗┻┻┻┛
//=============================================================

function Param(flightId, 
	startFrame, endFrame,
	apParams, bpParams, actions){
	
	this.flightId = flightId;
	this.apCount = 0;
	this.bpCount = 0;
	this.paramCount = 0;
	this.apArr = apParams;
	this.bpArr = bpParams;
	this.refParamArr = new Array();
	this.associativeParamsArr = new Array();
	this.data = Array();
	this.receivedParams = Array();

	this.startFrame = startFrame;
	this.endFrame = endFrame;
	
	this.actions = actions;
		
	if(this.apArr != null){	
		this.apCount = this.apArr.length;
		this.refParamArr = this.refParamArr.concat(this.apArr);
		this.paramCount += this.apCount;
	}
	
	if(this.bpArr != null){	
		this.bpCount = this.bpArr.length;
		this.refParamArr = this.refParamArr.concat(this.bpArr);
		this.paramCount += this.bpCount;
	}
};
//=============================================================

//=============================================================
Param.prototype.ReceiveParams = function(){
	var self = this,
		dfd = new $.Deferred();
	self.receivedParams = Array();
	
	// Show a "working..." message every half-second
	setTimeout(function working() {
		if ( dfd.state() === "pending" ) {
			dfd.notify( "working... " );
			setTimeout( working, 500 );
		}
	}, 1 );
	
	//iterational receiving ap data arrays
	for (var i = 0; i < this.apCount; i++) {
		var apName = self.apArr[i];
		self.GetApParam(apName, i, dfd);
	}
	//=============================================================
		
	//=============================================================
	//iterational receiving bp data arrays
	for (var i = 0; i < this.bpCount; i++) {
		var bpName = self.bpArr[i];
		self.GetBpParam(bpName, i, dfd);
	}

	return dfd.promise();
};
//=============================================================

//=============================================================
Param.prototype.GetApParam = function(paramCode, i, dfd){
	var self = this,
		apDataArray = Array();
	
		pV = {
			action: self.actions['getApParamValue'],
			data:{
				flightId: self.flightId,
				paramApCode: paramCode,
				totalSeriesCount: self.apCount,
				startFrame: self.startFrame,	
				endFrame: self.endFrame
			}
		};

	$.ajax({
		data: pV,
		type: "POST",
		dataType: "json",
		url: CHART_SRC,
		async: true
	}).done(function(receivedParamPoints){		
		apDataArray = receivedParamPoints;
		var pV = {
				action: self.actions['getParamInfo'],
				data: {
					flightId: self.flightId,
					paramCode: paramCode
				}
		};
		
		$.ajax({
			type: "POST",
			data: pV,
			dataType: 'json',
			url: CHART_SRC,
			async: true
		}).done(function(receivedInfo){
			var color = receivedInfo['color'],
				nm = receivedInfo['name'];
			var apDataFlotSeries = {
					data: apDataArray,
					label: paramCode + "_" + nm + " = 0.00", 
					yaxis: i + 1,
					color: "#" + color,
					shadowSize: 0, 
					lines: { lineWidth: 1, show: true }
				};
			self.data[i] = apDataFlotSeries;
			if (self.associativeParamsArr[paramCode] === undefined) {
				self.associativeParamsArr[paramCode] = [i, color];
			}
			
			self.receivedParams.push(paramCode);
			if(self.receivedParams.length == (self.apCount + self.bpCount)) {
				dfd.resolve(paramCode);
			}
			
		}).fail(function(mess){
			dfd.reject(mess);
		});
	}).fail(function(mess){
		dfd.reject(mess);
	});

}
//=============================================================

//=============================================================
Param.prototype.GetBpParam = function(paramCode, i, dfd){
	var self = this,
		bpDataArray = Array(),
		color = String(),
		pV = {
			action: self.actions['getBpParamValue'],
			data:{
				flightId: self.flightId,
				paramBpCode: paramCode
			}
		};

	$.ajax({
		type: "POST",
		data: pV,
		dataType: 'json',
		url: CHART_SRC,
		async: true
	}).done(function(receivedParamPoints){
		bpDataArray = receivedParamPoints;
		
		var pV = {
				action: self.actions['getParamColor'],
				data: {
					flightId: self.flightId,
					paramCode: paramCode
				}
		};
		
		$.ajax({
			type: "POST",
			data: pV,
			dataType: 'json',
			url: CHART_SRC,
			async: true
		}).done(function(receivedColor){
			color = receivedColor;
			var bpDataFlotSeries = {
					data: bpDataArray,
					label: paramCode +  " = F", 
					yaxis: self.apCount + i + 1,
					color: "#" + color,
					points: { symbol: "square", show: true, radius: 1, }, 
					shadowSize: 0, 
					lines: { lineWidth: 1, show: true, }	
				};
			self.data[self.apCount + i] = bpDataFlotSeries;
			if (self.associativeParamsArr[paramCode] === undefined) {
				self.associativeParamsArr[paramCode] = [self.apCount + i, color];
			}
			
			self.receivedParams.push(paramCode);
			if(self.receivedParams.length == (self.apCount + self.bpCount)) {
				dfd.resolve(paramCode);
			}
			
		}).fail(function(mess){
			dfd.reject(mess);
		});
	}).fail(function(mess){
		dfd.reject(mess);
	});
}

//=============================================================
/*Param.prototype.ReceiveParams = function(){
	//receive first ap, publish it, build data arrays
	var postValues = {
		paramApCode: this.apArr.join('-')	
	}
			
	//this.scriptAddrDummyAp += postValues.paramApCode;
	
	console.log("a " + this.scriptAddrDummyAp);
	
	receiveArray = $.ajax({
		type: 'POST',
		data: postValues,
		url: this.scriptAddrDummyAp,
		success: function(inData) { return inData; },
		async: this.asyncReceive
	});
	
	var apDataArray = $.parseJSON(receiveArray.responseText);
	for(var i = 0; i < apDataArray.length; i++){
		var paramData = apDataArray[i],
			seriesColor = this.Rainbow(i),
			apDataFlotSeries = {
				data: paramData,
				label: this.apArr[i] +  " = 0.00", 
				yaxis: i + 1,
				color: seriesColor,
				lines:{show: true}
			};
		this.data.push(apDataFlotSeries);
		this.associativeParamsArr[this.apArr[i]] = [i, seriesColor];
	}
	
//=============================================================
	
//=============================================================
	//iterational receiving bp data arrays
	var postValues = {
		paramBpCode: this.bpArr.join('-')	
	}

	scriptAddrBp = this.scriptAddrDummyBp + 
		"&paramBpCode=" + postValues.paramBpCode;
		
	console.log("b " + scriptAddrBp);
	
	receiveArray = $.ajax({
		type: 'GET',
		data: postValues,
		url: scriptAddrBp,
		success: function(inData) { return inData; },
		async: this.asyncReceive
	});
		
	var bpDataArray = $.parseJSON(receiveArray.responseText);
	console.log(bpDataArray);
	for (var i = 0; i < this.bpCount; i++) {
		var paramData = bpDataArray[i],
			seriesColor = this.Rainbow(this.apCount + i),
			bpDataFlotSeries = {
				data: paramData,
				label: this.bpArr[i], 
				yaxis: this.apCount + i + 1,
				color: seriesColor,
				points: { symbol: "square", show: true, radius: 1 }, 
				shadowSize: 0, 
				lines:{ lineWidth: 3, show: true},	
			};
		this.data.push(bpDataFlotSeries);
		this.associativeParamsArr[this.bpArr[i]] = 
			[this.apCount + i, seriesColor];
	}
	
	this.updateParamTimeout = null;
}*/
//=============================================================

//=============================================================
//Get value by x coord by interpolating
Param.prototype.GetValue = function(dataset, x) {	
	var yArr = Array();
	for (var i = 0; i < this.apCount; ++i) {
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
Param.prototype.GetBinaries = function(dataset, x){	
	var bpPrasentArray = new Array();
	for (var i = this.apCount; i < this.paramCount; ++i) {
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
};
//=============================================================