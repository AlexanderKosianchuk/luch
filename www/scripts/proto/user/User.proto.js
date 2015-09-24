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
		
	var LeftMenuClick = function(e) {
		var target = $(e.target);
		
//		if(target.attr('id') == "editBruGeneralInfoLeftMenuRow"){
//			if(!target.hasClass('LeftMenuRowSelected')){
//				$("#leftMenuuser .LeftMenuRowSelected").removeClass('LeftMenuRowSelected', {duration:500});
//				
//				target.addClass('LeftMenuRowSelected', {duration:500});
//				
//				if(GeneralInfo == null){
//					GeneralInfo = new userGeneralInfo(langStr, srvcStrObj, eventHandler, userListFactoryContainer);
//				};
//				
//				GeneralInfo.Show(userId, userListTopMenu, userListWorkspace);
//			}
//		} else if(target.attr('id') == "editBruTplsLeftMenuRow"){
//			if(!target.hasClass('LeftMenuRowSelected') &&
//					(Templates != null)){
//				$("#leftMenuuser .LeftMenuRowSelected").removeClass('LeftMenuRowSelected', {duration:500});
//				
//				target.addClass('LeftMenuRowSelected', {duration:500});
//				
//				if(Templates == null){
//					Templates = new userTemplates(langStr, srvcStrObj, eventHandler, userListFactoryContainer);
//				};
//				
//				Templates.Show(userId, userListTopMenu, userListWorkspace);
//			}
//		} else if(target.attr('id') == "editBruCycloLeftMenuRow"){
//			if(!target.hasClass('LeftMenuRowSelected')){
//				$("#leftMenuuser .LeftMenuRowSelected").removeClass('LeftMenuRowSelected', {duration:500});
//				
//				target.addClass('LeftMenuRowSelected', {duration:500});
//				
//				//self.ShowFlightViewParamsList();
//			}
//		} else if(target.attr('id') == "editBruEventsLeftMenuRow"){
//			if(!target.hasClass('LeftMenuRowSelected')){
//				$("#leftMenuuser .LeftMenuRowSelected").removeClass('LeftMenuRowSelected', {duration:500});
//				
//				target.addClass('LeftMenuRowSelected', {duration:500});
//				
//				//self.ShowFlightViewParamsList();
//			}
//		}
		
		return this;
	}
	
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
	
	this.FillFactoryContaider = function(factoryContainer) {
		var self = this,
			task = this.task,
			userListFactoryContainer = factoryContainer;
		
		userId = this.userId;

		var pV = {
				action: actions["putUserContainer"],
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

				userListFactoryContainer.append(data['topMenu']);
				userListFactoryContainer.append(data['leftMenu']);
				userListFactoryContainer.append(data['workspace']);
				
				userListTopMenu = $('div#topMenuuser');
					
				userListLeftMenu = $('div#leftMenuuser');
				userListLeftMenu.on("click", function(e){
					LeftMenuClick(e);
				});

				userListWorkspace = $('div#userWorkspace');
				
//				if(task == null){
//					$("#editBruGeneralInfoLeftMenuRow").addClass("LeftMenuRowSelected");
//					
//					if(userListWorkspace.html() != ''){
//						userListWorkspace.empty();
//					}
//					
//					GeneralInfo = new userGeneralInfo(langStr, srvcStrObj, eventHandler, userListFactoryContainer);
//					GeneralInfo.Show(userId, userListTopMenu, userListWorkspace);
//					
//				} else if(task == actions['editinguserGeneralInfo']){
//					$("#editBruGeneralInfoLeftMenuRow").addClass("LeftMenuRowSelected");
//
//					if(userListWorkspace.html() != ''){
//						userListWorkspace.empty();
//					}
//					
//					var GeneralInfo = new userGeneralInfo(langStr, srvcStrObj, eventHandler, userListFactoryContainer);
//					GeneralInfo.Show(userId, userListTopMenu, userListWorkspace);
//					
//				} else if(task == actions['editinguserTemplates']){
//					$("#editBruTplsLeftMenuRow").addClass("LeftMenuRowSelected");
//					
//					Templates = new userTemplates(langStr, srvcStrObj, eventHandler, userListFactoryContainer);
//					Templates.Show(userId, userListTopMenu, userListWorkspace);
//				}
									
				self.ResizeUserContainer();
				
			} else {
				console.log(answ["error"]);
			}
	    });
	};
}






