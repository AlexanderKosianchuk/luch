var TPL_CACHE = "cache",
	TPL_ADD = "add",
	TPL_DEFAULT = "default",
	TPL_DEL = "del",
	
	RECEIVE_LEGENT = 'rcvLegend';

var ParamSetTemplate = function () {
	this.flightId = $("input#flightId").attr('value'),
	this.username = $("input#username").attr('value'),
	//show params from template
	this.templatesList = $("option#tplOption"),
	this.comment = $("textarea#tplComment"),
	
	this.scriptAddrDummyInfo = location.protocol + '//' + location.host + "/asyncParamInfo.php";
	this.scriptAddrDummyTpl  = location.protocol + '//' + location.host + "/asyncTplServer.php";
};
//=============================================================

//=============================================================
ParamSetTemplate.prototype.ShowTempltInfo = function(selectedOption){
	var self = this;
	if(selectedOption.data("comment") == "") {
		self.ReceiveTempltInfo(selectedOption).done(function(paramsInfo){
				var commentText = new String();
				$.each(paramsInfo, function(i, value){
					commentText += (i + 1) + ") " + value + ";\n";
				});
				selectedOption.data("comment", commentText);
				self.comment.text(commentText);	
			}
		);
	} else {
		self.comment.text(selectedOption.data("comment"));
	}
	
};
//=============================================================

//=============================================================	
ParamSetTemplate.prototype.ReceiveTempltInfo = function (selectedOption){
	var paramsCodeArray = selectedOption.data("params").split(', '),
		paramArrString = new String();

	for(var i = 0; i < paramsCodeArray.length; i++) {
		if(i == paramsCodeArray.length - 1){
			paramArrString += paramsCodeArray[i];
		} else {
			paramArrString += paramsCodeArray[i] + "-";
		} 
	}
	
	/*var scriptAddrInfo = this.scriptAddrDummyInfo + 
		"?flightId=" + this.flightId + 
		"&paramCode=" + paramArrString;
	console.log(scriptAddrInfo);*/
	
	var scriptAddrInfo = this.scriptAddrDummyInfo,
	postData = {
		action: RECEIVE_LEGENT,
		flightId: this.flightId, 
		paramCode: paramArrString
	};
	
							
	return $.ajax({
		type: "POST",
		data: postData,
		url: scriptAddrInfo,
		dataType: "json",
		async: true
	});
};
//=============================================================

//=============================================================
ParamSetTemplate.prototype.CacheParams = function (selectedParams){	
	/*var scriptAddrTpl = this.scriptAddrDummyTpl + 
		"?flightId=" + this.flightId + 
		"&action=" + TPL_CACHE + 
		"&paramCode=" + selectedParams;
	console.log(scriptAddrTpl);*/
	
	var scriptAddrTpl = this.scriptAddrDummyTpl,
	postData = {
		flightId: this.flightId, 
		action: TPL_CACHE, 
		paramCode: selectedParams,
		username: this.username
	};
							
	return $.ajax({
		type: "POST",
		data: postData,
		url: scriptAddrTpl,
		async: true
	});
};
//=============================================================

//=============================================================
ParamSetTemplate.prototype.CreateTemplate = function (templateName, selectedParams){
	/*var scriptAddrTpl = this.scriptAddrDummyTpl + 
		"?flightId=" + this.flightId + 
		"&action=" + TPL_ADD + 
		"&tplName=" + templateName + 
		"&paramCode=" + selectedParams;
	console.log(scriptAddrInfo);*/
	
	var scriptAddrTpl = this.scriptAddrDummyTpl,
		postData = {
			flightId: this.flightId, 
			action: TPL_ADD, 
			tplName: templateName,
			paramCode: selectedParams,
			username: this.username
		};
							
	return $.ajax({
		type: "POST",
		data: postData,
		url: scriptAddrTpl,
		async: true
	});
};
//=============================================================

//=============================================================
ParamSetTemplate.prototype.DeleteTemplate = function (templateName){
	/*var scriptAddrTpl = this.scriptAddrDummyTpl + 
		"?flightId=" + this.flightId + 
		"&action=" + TPL_DEL + 
		"&tplName=" + templateName;
	console.log(scriptAddrInfo);*/
	
	var scriptAddrTpl = this.scriptAddrDummyTpl,
		postData = {
			flightId: this.flightId, 
			action: TPL_DEL, 
			tplName: templateName,
			username: this.username
		};

							
	return $.ajax({
		type: "POST",
		data: postData,
		url: scriptAddrTpl,
		async: true
	});
};

//=============================================================

//=============================================================
ParamSetTemplate.prototype.SetDefaultTemplate = function (templateName){
	/*var scriptAddrTpl = this.scriptAddrDummyTpl + 
		"?flightId=" + this.flightId + 
		"&action=" + TPL_DEL + 
		"&tplName=" + templateName;
	console.log(scriptAddrInfo);*/
	
	var scriptAddrTpl = this.scriptAddrDummyTpl,
		postData = {
			flightId: this.flightId, 
			action: TPL_DEFAULT, 
			tplName: templateName,
			username: this.username
		};

							
	return $.ajax({
		type: "POST",
		data: postData,
		url: scriptAddrTpl,
		async: true
	});
};
	