var ACTION_USER_LOGOUT = 'logout',
	ACTION_USER_CREATE = 'create',
	ACTION_USER_EDIT = 'edit',
	ACTION_USER_DELETE = 'delete',
		
	SCRIPT_ADDR_USER_OPERATION = location.protocol + '//' + location.host + "/asyncUserOperation.php",
	
	LANG_FILE = location.protocol + '//' + location.host + "/lang/" + "RU.lang",
	LANG_FILE_DEFAULT =  location.protocol + '//' + location.host + "/lang/" + "Default.lang";

$(document).ready(function(){	
	
	var lang = Object();
	
	$.ajax({
		url: LANG_FILE,
		dataType: 'json',
		async: false,
		success: function(data) {
			lang = data;
		}
	}).fail(function() {
		$.ajax({
			url: LANG_FILE_DEFAULT,
			dataType: 'json',
			async: false,
			success: function(data) {
				lang = data;
			}
		});
	});
	
	var messageBox = $('div#dialog').dialog({
			resizable: false,
			modal: true,
			autoOpen: false,
			draggable: true,
			buttons: [{
		            text: lang.returnToMain,
		            click: function() { 
		            	location.href=location.protocol + '//' + location.host + '/index.php';
	            }},{
		            text: lang.closeLabel,
		            click: function() { 
		            	$(this).dialog("close");
	            }}],
			}),
		dialogText = $("div#dialog p");
	
	var userListActions = $('form#usersList #userAction'),
		userCreationInfo = $("label#userCreationInfo"),
		login = $("input#login"),
		company = $("input#company"),
		pwd1 = $("input#pwd1"),
		pwd2 = $("input#pwd2"),
		privilege = $("select#privilege"),
		mySubscriber = $("input#mySubscriber"),
		nonce = $("input#nonce"),
		createUserBut = $("input#createUserBut"),
		author = $("input#author");			

	createUserBut.on('click', function(e){
		e.preventDefault();
		var optionPrivilege = $("select#privilege option:selected"),
			emptyInput = $('input:text[value=""]'),
			privilegeList = '';
		
		for(var i = 0; i < optionPrivilege.length; i++){
			privilegeList += $(optionPrivilege[i]).text() + ",";
		}
		
		privilegeList = privilegeList.substr(0, privilegeList.length - 1);
		console.log(emptyInput);
		
		if((privilegeList.length > 0) && (emptyInput.length == 0)) {
			if(pwd1.val() != pwd2.val()){
				pwd1.val('');
				pwd2.val('');
				userCreationInfo.text(lang.passwordsAreNotTheSame);
			} else {
				
				var pV = {
					action: ACTION_USER_CREATE,
					user: login.val(),
					company: company.val(),
					pwd: pwd1.val(),
					privilege: privilegeList,
					mySubscriber:mySubscriber.val(),
					author:author.val()
				};
				
				//=========================
				//flights
				//=========================
				var selectedFlightToAllowAccess = "";
				$("input#flightToAllowAccess").each(function() {
					var $this = $(this);
					if($this.prop("checked")){
						selectedFlightToAllowAccess += $this.data("flightid") + ",";
					}
				});
				
				selectedFlightToAllowAccess = selectedFlightToAllowAccess.substr(0, 
						selectedFlightToAllowAccess.length-1);
				
				if(selectedFlightToAllowAccess.length > 0){
					pV["permittedFlights"] = selectedFlightToAllowAccess;
				}
				//=========================
				//slices
				//=========================
				var selectedSliceToAllowAccess = "";
				$("input#sliceToAllowAccess").each(function() {
					var $this = $(this);
					if($this.prop("checked")){
						selectedSliceToAllowAccess += $this.data("sliceid") + ",";
					}
				});
				
				selectedSliceToAllowAccess = selectedSliceToAllowAccess.substr(0, 
						selectedSliceToAllowAccess.length-1);
				
				if(selectedSliceToAllowAccess.length > 0){
					pV["permittedSlices"] = selectedSliceToAllowAccess;
				}
				//=========================
				//engine discrep
				//=========================
				var selectedEngineDescrepToAllowAccess = "";
				$("input#engineDescrepToAllowAccess").each(function() {
					var $this = $(this);
					if($this.prop("checked")){
						selectedEngineDescrepToAllowAccess += $this.data("enginedescrepid") + ",";
					}
				});
				
				selectedEngineDescrepToAllowAccess = selectedEngineDescrepToAllowAccess.substr(0, 
						selectedEngineDescrepToAllowAccess.length-1);
				
				if(selectedEngineDescrepToAllowAccess.length > 0){
					pV["permittedEngines"] = selectedEngineDescrepToAllowAccess;
				}
				//=========================
				//bruType
				//=========================
				var selectedBruTypeToAllowAccess = "";
				$("input#bruTypeToAllowAccess").each(function() {
					var $this = $(this);
					if($this.prop("checked")){
						selectedBruTypeToAllowAccess += $this.data("brutypeid") + ",";
					}
				});
				
				selectedBruTypeToAllowAccess = selectedBruTypeToAllowAccess.substr(0, 
						selectedBruTypeToAllowAccess.length-1);
				
				if(selectedBruTypeToAllowAccess.length > 0){
					pV["permittedBruTypes"] = selectedBruTypeToAllowAccess;
				}
				//=========================
				//users
				//=========================
				var selectedUsersToAllowAccess = "";
				$("input#usersToAllowAccess").each(function() {
					var $this = $(this);
					if($this.prop("checked")){
						selectedUsersToAllowAccess += $this.data("userid") + ",";
					}
				});
				
				selectedUsersToAllowAccess = selectedUsersToAllowAccess.substr(0, 
						selectedUsersToAllowAccess.length-1);
				
				if(selectedUsersToAllowAccess.length > 0){
					pV["permittedUsers"] = selectedUsersToAllowAccess;
				}
				
				console.log(pV);
							
				//=========================
				$.ajax({
					type: "POST",
					data: pV,
					dataType: 'json',
					url: SCRIPT_ADDR_USER_OPERATION,
					async: true
				}).fail(function(data){
					console.log(data);
				}).done(function(data){
					if(data != 'ok'){
						userCreationInfo.text(lang[data]);
					} else {
						dialogText.text(lang.userCreatedSuccesfully);
						messageBox.dialog("open");
					}
				});
			}
		} else {
			userCreationInfo.text(lang.notAllFieldsMatched);
		}
	});
});
