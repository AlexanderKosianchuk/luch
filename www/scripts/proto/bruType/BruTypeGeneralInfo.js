///==================================================
//GENERAL INFO
///==================================================
function BruTypeGeneralInfo(langStr, srvcStrObj, eventHandler, bruTypeListFactoryContainer) {
	var langStr = langStr,
		actions = srvcStrObj["bruTypesPage"],
		flightViewOptionActions = srvcStrObj["viewOptionsPage"],
		eventHandler = eventHandler;

	var bruTypeId = null;
	
	var factoryWindow = bruTypeListFactoryContainer,
		bruTypeTopMenu = null,
		bruTypeListWorkspace = null,
		bruTypeListOptions = null,
		bruTypeListContent = null;
	
	///
	// PRIVATE
	///
	var ShowGeneralInfoOptions = function() {
		bruTypeListWorkspace.append("<div id='bruTypeOptions' class='OptionsMenu'></div>");
		bruTypeListOptions = $("div#bruTypeOptions");
		
		var fligthOptionsStr = '<table v-align="top"><tr>' +
			'<td><label>' + langStr.bruTypeLabel + " - " + '</label></td><td>' + 
			'<div>' +
		    	'<button id="createBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + langStr.createType + '</button>' +
		    '</div>' +
		    '</td><td>' + 
			'<div>' +
	    		'<button id="copyBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + langStr.copyType + '</button>' +
	    	'</div>' +
		    '</td><td>' + 
			'<div>' +
	    		'<button id="saveBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + langStr.saveType + '</button>' +
	    	'</div>' +
		    '</td><td>' + 
			'<div>' +
	    		'<button id="delBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + langStr.deleteType + '</button>' +
	    	'</div>' +
		    '</td></tr></table>';
	  
		bruTypeListOptions.append(fligthOptionsStr);

		$("div#bruTypeOptions .Button").button();
	},

	ShowGeneralInfoContent = function() {
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
	};
	
	
	///
	// PRIVILEGED
	///
	this.Show = function(extBruTypeId, extBruTypeTopMenu, extBruTypeListWorkspace) {
		bruTypeId = extBruTypeId;
		bruTypeTopMenu = extBruTypeTopMenu;
		bruTypeListWorkspace = extBruTypeListWorkspace;
		
		bruTypeListWorkspace.empty();

		ShowGeneralInfoOptions();
		ShowGeneralInfoContent();
		
	}
	
}

