///==================================================
//GENERAL INFO
///==================================================
function BruTypeCyclo(langStr, srvcStrObj) {
	this.langStr = langStr;
	this.actions = srvcStrObj["bruTypesPage"];
	this.flightViewOptionActions = srvcStrObj["viewOptionsPage"];
	
	this.bruTypeId = null;
	
	this.bruTypeListWorkspace = null;
	this.bruTypeListOptions = null;
	this.bruTypeListContent = null;
}

BruTypeCyclo.prototype.ShowGeneralInfoOptions = function() {
	var self = this;
	
	self.bruTypeListWorkspace.append("<div id='bruTypeOptions' class='OptionsMenu'></div>");
	self.bruTypeListOptions = $("div#bruTypeOptions");
	
	var fligthOptionsStr = '<table v-align="top"><tr>' +
		'<td><label>' + this.langStr.bruTypeLabel + " - " + '</label></td><td>' + 
		'<div>' +
	    	'<button id="createBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + this.langStr.createType + '</button>' +
	    '</div>' +
	    '</td><td>' + 
		'<div>' +
    		'<button id="copyBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + this.langStr.copyType + '</button>' +
    	'</div>' +
	    '</td><td>' + 
		'<div>' +
    		'<button id="saveBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + this.langStr.saveType + '</button>' +
    	'</div>' +
	    '</td><td>' + 
		'<div>' +
    		'<button id="delBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + this.langStr.deleteType + '</button>' +
    	'</div>' +
	    '</td></tr></table>';
  
	self.bruTypeListOptions.append(fligthOptionsStr);

	$("div#bruTypeOptions .Button").button();
}

BruTypeCyclo.prototype.ShowGeneralInfoContent = function() {
	var self = this;	

	/*var pV = {
			action: self.actions["putBruTypeContainer"],
			data: { 
				data: 'data'
			}
	};
	
	$.ajax({
		type: "POST",
		data: pV,
		dataType: 'json',
		url: BRU_SRC,
		async: true
	}).fail(function(msg){
		console.log(msg);
	}).done(function(answ) {
		if(answ["status"] == "ok") {
			var data = answ['data'];
			
		} else {
			console.log(answ["error"]);
		}
	})*/
}