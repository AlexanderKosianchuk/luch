//=============================================================
//┏━━━┓╋╋╋╋╋╋╋╋╋┏┓╋╋╋╋╋╋┏┓
//┃┏━┓┃╋╋╋╋╋╋╋╋╋┃┃╋╋╋╋╋┏┛┗┓
//┃┃╋┗╋━━┳━━┳━┳━┛┣┳━┓┏━┻┓┏╋━━┓
//┃┃╋┏┫┏┓┃┏┓┃┏┫┏┓┣┫┏┓┫┏┓┃┃┃┃━┫
//┃┗━┛┃┗┛┃┗┛┃┃┃┗┛┃┃┃┃┃┏┓┃┗┫┃━┫
//┗━━━┻━━┻━━┻┛┗━━┻┻┛┗┻┛┗┻━┻━━┛
//=============================================================
var COORDINATES_ACTION_GET_COORD = 'coord',
	COORDINATES_ACTION_GET_PARAMS = 'params';

function Coordinate(flightId, startFrame, endFrame){
	this.flightId = flightId;
	this.startFrame = startFrame;
	this.endFrame = endFrame;
	this.scriptAddrCoord = location.protocol + '//' + 
		location.host + "/asyncCoordinateTrans.php";
	
	console.log();
};
//=============================================================

//=============================================================
Coordinate.prototype.ReceiveCoordinates = function(){
	var self = this,
	coordDataArray = Array(),
	pV = {
		flightId: self.flightId,
		action: COORDINATES_ACTION_GET_COORD/*,
		startFrame:this.startFrame,
		endFrame:this.endFrame*/
	};
	
	if(this.startFrame != -1)
	{
		pV["startFrame"] = this.startFrame;
	}
	
	if(this.startFrame != -1)
	{
		pV["endFrame"] = this.endFrame;
	}

	$.ajax({
		data: pV,
		type: "POST",
		dataType: "json",
		url: self.scriptAddrCoord,
		async: false
	}).done(function(data){
		coordDataArray = data;	
	}).fail(function(msg){ 
		console.log(msg);
	});
	
	/*scriptAddrCoord = this.scriptAddrCoord + "?flightId="+this.flightId
	console.log("c " + scriptAddrCoord);
	
	$.ajax({
		dataType: "json",
		url: scriptAddrCoord,
		success: function(inData) {	coordDataArray = inData; },
		async: false,
	});*/
				
	return coordDataArray;
};

//=============================================================

//=============================================================
Coordinate.prototype.ReceiveParams = function(){
	console.log("c " + this.scriptAddrCoord);
	
	var self = this,
	apDataArray = Array(),
	pV = {
		flightId: self.flightId,
		action: "params",
		frame: self.startFrame,
	};

	$.ajax({
		data: pV,
		type: "POST",
		dataType: "json",
		url: self.scriptAddrCoord,
		async: false
	}).done(function(receivedParamPoints){
		apDataArray = receivedParamPoints;	
	}).always(function(msg){ 
		console.log(msg);
	});
				
	return apDataArray;
};

//=============================================================