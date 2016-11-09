//=============================================================
//┏━━━┓╋╋╋╋╋╋╋╋┏┓┏┓┏┓╋╋╋╋┏┓
//┃┏━┓┃╋╋╋╋╋╋╋╋┃┃┃┃┃┃╋╋╋╋┃┃
//┃┃╋┃┣┓┏┳━━┳━━┫┃┃┃┃┣━━┳━┫┃┏┳━━┳━┓
//┃┗━┛┣╋╋┫┃━┫━━┫┗┛┗┛┃┏┓┃┏┫┗┛┫┃━┫┏┛
//┃┏━┓┣╋╋┫┃━╋━━┣┓┏┓┏┫┗┛┃┃┃┏┓┫┃━┫┃
//┗┛╋┗┻┛┗┻━━┻━━┛┗┛┗┛┗━━┻┛┗┛┗┻━━┻┛
//=============================================================
var TPL_GET_PARAM_MINMAX = 'getminmax',
 	TPL_SET_PARAM_MINMAX = 'setminmax',
	scriptAddrTplServer = location.protocol + '//' + location.host
	+ "/asyncTplServer.php";

function AxesWorker(extStepLength, extStartCopyTime, plotAxes, user){
	this.stepLength = extStepLength;
	this.startCopyTime = extStartCopyTime;	
	this.axes = plotAxes;
	this.user = user;
	this.startFrame = 0;
	this.endFrame = 0;
	this.redrawNeed = false;
	this.frameRange = 0;
	this.frameOffset = 0;
	this.distributionProc = 0;
	this.savingDistribution = true;
};
//=============================================================

//=============================================================
AxesWorker.prototype.Distribute = function(yAxArr, xAx, series, apCount){
	var corridorsNum = apCount;
	for(var i = 0; i < apCount; i++){
		var yMax = series[i].yaxis.datamax,
			yMin = series[i].yaxis.datamin,
			curCorridor = 0;
			
		if(yMax > 0){
			curCorridor = (yMax - yMin);
		} else {
			curCorridor = -(yMin - yMax);			
		}
		
		yAxArr[i].max = yMax + (i * curCorridor);
		yAxArr[i].min = yMin - 
			((corridorsNum - i) * curCorridor);		
	}
	
	var bpCount = yAxArr.length - apCount;
	
	if(bpCount > 0) {
		var busyCorridor = ((apCount - 1) / apCount * 100),
			freeCorridor = 100 - busyCorridor,//100%
			curCorridor = freeCorridor / bpCount,
			j = 0;
			
		for(var i = apCount; i < yAxArr.length; i++){
				
			yAxArr[i].max = 100 - (curCorridor * j);
			yAxArr[i].min = 0 - (curCorridor * j);
			j++;			
		};
	}
	
	xAx.xaxis.max = series[0].xaxis.datamax;
	xAx.xaxis.min = series[0].xaxis.datamin;
};

//=============================================================

//=============================================================
AxesWorker.prototype.LoadDistribution = function(yAxArr, apParams, bpParams, flightId, tplName){
	var paramsArr = apParams.concat(bpParams);
	
	for(var i = 0; i < paramsArr.length; i++)
	{
		var pV = {
				action: TPL_GET_PARAM_MINMAX,
				flightId: flightId,
				paramCode: paramsArr[i],
				tplName: tplName,
				username: this.user
		};
		
		$.ajax({
			type: "POST",
			data: pV,
			dataType: 'json',
			url: scriptAddrTplServer,
			async: false
		}).done(function(receivedMinMax){
			var minMax = receivedMinMax;
			
			yAxArr[i].max = minMax['max'];
			yAxArr[i].min = minMax['min'];	
			
		}).fail(function(e){
			console.log(e);
		});
	}
};

//=============================================================

//=============================================================
AxesWorker.prototype.SaveDistribution = function(yAxArr, apParams, bpParams, flightId, tplName){
	var paramsArr = apParams.concat(bpParams),
		self = this;
	
	//all async saving complete
	if(self.distributionProc == 0) {
		self.distributionProc = paramsArr.length;
		
		for(var i = 0; i < paramsArr.length; i++)
		{
			var pV = {
					action: TPL_SET_PARAM_MINMAX,
					flightId: flightId,
					paramCode: paramsArr[i],
					tplName: tplName,
					max: yAxArr[i].max,
					min: yAxArr[i].min,
					username: this.user
			};
			
			$.ajax({
				type: "POST",
				data: pV,
				url: scriptAddrTplServer,
				//async: true
			}).done(function(e){
				self.distributionProc--;
			}).fail(function(e){
				console.log(e);
			});
		}
	}
};
