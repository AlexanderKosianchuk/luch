var USER_SRC = location.protocol + '//' + location.host + "/view/user.php";

function User(window, document, langStr, srvcStrObj, eventHandler) 
{ 
	var langStr = langStr,
	srvcStrObj = srvcStrObj,
	actions = srvcStrObj["userPage"];
	
	eventHandler = eventHandler;
	window = window;
	document = document;
	
	this.userId = null;
	this.task = null;
	
	var userId = null;
	
	///
	// PRIVATE
	///
	 
	var userListFactoryContainer = null,
		userListTopMenu = null,
		userListLeftMenu = null,
		userListWorkspace = null;
				
	
	///
	// PRIVILEGED
	///
	
	this.ResizeUserContainer = function(e) {
		eventHandler.trigger("resizeShowcase");
		return this;
	};
	
	this.logout = function(e) {
		var pV = {
				action: actions['userLogout'],
				data: { 
					data: 'data'
				}
		};
		
		$.ajax({
			type: "POST",
			data: pV,
			dataType: 'json',
			url: USER_SRC,
			async: true
		}).fail(function(data){
			console.log(data);
		}).done(function(data){
			location.reload();
		});
	};
	
	this.changeLanguage = function(lang) {
		var pV = {
				action: actions['userChangeLanguage'],
				data: { 
					lang: lang
				}
		};
		
		$.ajax({
			type: "POST",
			data: pV,
			dataType: 'json',
			url: USER_SRC,
			async: true
		}).fail(function(data){
			console.log(data);
		}).done(function(data){
			location.reload();
		});
	};
	
	this.FillFactoryContaider = function(factoryContainer) {
		var self = this,
			userListFactoryContainer = factoryContainer;
		
		userId = this.userId;

		var pV = {
				action: actions["buildUserTable"],
				data: { 
					data: 'data'
				}
		};
		
		$.ajax({
			type: "POST",
			data: pV,
			dataType: 'json',
			url: USER_SRC,
			async: true
		}).fail(function(msg){
			console.log(msg);
		}).done(function(answ) {
			if(answ["status"] == "ok") {
				var userTable = answ['data'],
					sortCol = answ['sortCol'],
					sortType = answ['sortType'];
				userListFactoryContainer.append(userTable);
				self.SupportDataTable(sortCol, sortType);											
				self.ResizeUserContainer();
				
			} else {
				console.log(answ["error"]);
			}
	    });
	};
	
	this.SupportDataTable = function(sortColumn, sortType) {
		var self = this,
			sortType = sortType.toLowerCase();
		
		var oTable = $('#userTable').dataTable( {
			"bInfo": false,
			"bSort": true,
			"aoColumnDefs": [
			    { 'bSortable': false, 'aTargets': [0] },
			    { "sClass": "UserCheckboxCenter", 'aTargets': [0] }
			],
			"order": [[ sortColumn, sortType]],
			"bFilter": false,
			"bLengthChange": false,
	        "bAutoWidth": false,
	        "bProcessing": true,
			"bServerSide": true,
			"aLengthMenu": false,
			"bPaginate": false,
	        "sAjaxSource": USER_SRC,
	        "fnServerData": function ( sSource, aoData, fnCallback) {     	
				var pV = {
					action: actions["segmentTable"],
					data: {
						data: aoData
					}
				};
				
				$.ajax({
					"dataType": 'json', 
					"type": "POST", 
					"url": sSource, 
					"data": pV,
					"success": fnCallback
				}).done(function(a){
					//self.SupportTableContent();
				})
				.fail(function(a){
					console.log(a);
				});
			},
	        "oLanguage": langStr.dataTable,
		});	
		
		$("#tableCheckAllItems").on("click", function(e){
			var el = $(e.target);
			
			if(el.attr("checked") == "checked"){
				$(".ItemsCheck").removeAttr("checked");
				$(".ItemsCheck").prop("checked", false);
				el.removeAttr("checked");
			} else {
				$(".ItemsCheck").attr("checked", "checked");
				$(".ItemsCheck").prop("checked", true);
				el.attr("checked", "checked");
			}
		});
	}
}






