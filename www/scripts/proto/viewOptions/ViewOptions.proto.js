var FLIGHTS_VIEW_OPTIONS_SRC = location.protocol + '//' + location.host + "/view/viewOptions.php";
	
function FlightViewOptions(window, document, langStr, srvcStrObj, eventHandler) 
{ 
	this.langStr = langStr;
	this.actions = srvcStrObj["viewOptionsPage"];
	this.bruTypeTasks = srvcStrObj["bruTypesPage"];
	this.printerTasks = srvcStrObj["printerPage"];
	
	this.flightId = null;
	this.task = null;
	
	this.window = window;
	this.document = document;
	
	this.eventHandler = eventHandler;
	this.flightOptionsFactoryContainer = null;
	this.flightOptionsTopMenu = null;
	this.flightOptionsLeftMenu = null;
	this.flightOptionsWorkspace = null;
	this.flightOptionsOptions = null;
	this.flightOptionsContent = null;
	
	this.rangeSlider = null;
}

FlightViewOptions.prototype.FillFactoryContaider = function(factoryContainer) {
	var self = this;
	this.flightOptionsFactoryContainer = factoryContainer;	

	var pV = {
			action: self.actions["putViewOptionsContainer"],
			data: { 
				data: 'data'
			}
	};
	
	$.ajax({
		type: "POST",
		data: pV,
		dataType: 'json',
		url: FLIGHTS_VIEW_OPTIONS_SRC,
		async: true
	}).fail(function(msg){
		console.log(msg);
	}).done(function(answ) {
		if(answ["status"] == "ok") {
			var data = answ['data'];

			self.flightOptionsFactoryContainer.append(data['topMenu']);
			self.flightOptionsFactoryContainer.append(data['leftMenu']);
			self.flightOptionsFactoryContainer.append(data['workspace']);
			
			self.flightOptionsTopMenu = $('div#topMenuOptionsView');
				
			self.flightOptionsLeftMenu = $('div#leftMenuOptionsView');
			self.flightOptionsLeftMenu.on("click", function(e){
				self.leftMenuClick(e);
			});
			
			self.flightOptionsWorkspace = $('div#flightOptionsWorkspace');
			
			if(self.task == null){
				self.ShowFlightViewTemplates();				
			} else if(self.task == self.actions['getBruTemplates']){
				$("#leftMenuOptionsView .LeftMenuRowSelected").removeClass('LeftMenuRowSelected');
				$("#templatesLeftMenuRow").addClass('LeftMenuRowSelected');
				
				self.ShowFlightViewTemplates();				
			} else if(self.task == self.actions['getEventsList']){
				$("#leftMenuOptionsView .LeftMenuRowSelected").removeClass('LeftMenuRowSelected');
				$("#eventsLeftMenuRow").addClass('LeftMenuRowSelected');
			
				self.ShowFlightViewEvents();			
			} else if(self.task == self.actions['getParamList']){
				$("#leftMenuOptionsView .LeftMenuRowSelected").removeClass('LeftMenuRowSelected');
				$("#paramsListLeftMenuRow").addClass('LeftMenuRowSelected');
				
				self.ShowFlightViewParamsList();
			}									
			
			self.ResizeFlightViewOptionsContainer();
			self.document.scrollTop(factoryContainer.data("index") * self.window.height());
			
		} else {
			console.log(answ["error"]);
		}
    });
}

FlightViewOptions.prototype.ShowFlightViewTemplates = function() {	
	if(this.flightOptionsWorkspace.html() != ''){
		this.flightOptionsWorkspace.empty();
	}
	
	this.ShowTopMenuTempltListButtons();
	this.ShowFlightViewTempltListOptions();
	this.ShowTempltList();
}

FlightViewOptions.prototype.ShowFlightViewEvents = function() {
	if(this.flightOptionsWorkspace.html() != ''){
		this.flightOptionsWorkspace.empty();
	}
	
	this.ShowTopMenuEventsListButtons();
	this.ShowFlightViewEventsListOptions();
	this.ShowEventsList();
}

FlightViewOptions.prototype.ShowFlightViewParamsList = function() {
	if(this.flightOptionsWorkspace.html() != ''){
		this.flightOptionsWorkspace.empty();
	}
	
	this.ShowTopMenuParamsListButtons();
	this.ShowFlightViewParamsListOptions();
	this.ShowParamList();
}

FlightViewOptions.prototype.ResizeFlightViewOptionsContainer = function(e) {
	var self = this;
	
	
	/*if((self.flightOptionsWorkspace != null) && 
			(self.flightOptionsLeftMenu != null) && 
			(self.flightOptionsTopMenu != null) &&
			(self.flightOptionsContent != null)){
		
		self.flightOptionsContent.css({
			"left": 0,
			"top" : 0,
			"width" : self.window.width() - self.flightOptionsLeftMenu.width() - 20,
			"height": self.flightOptionsWorkspace.height() - 
				self.flightOptionsTopMenu.height() - 10
		});
	}*/
	
	self.eventHandler.trigger("resizeShowcase");

	return false;
}

///====================================================
//
///====================================================
FlightViewOptions.prototype.leftMenuClick = function(e){
	var self = this,
	target = $(e.target);
	e.stopPropagation();
	
	if(target.attr('id') == "templatesLeftMenuRow"){
		if(!target.hasClass('LeftMenuRowSelected')){
			$("#leftMenuOptionsView .LeftMenuRowSelected")
				.removeClass('LeftMenuRowSelected');
			
			target.addClass('LeftMenuRowSelected');
			$("#leftMenuOptionsView .SearchBox").prop('disabled', true);
			
			self.ShowFlightViewTemplates();
		}
	} else if(target.attr('id') == "eventsLeftMenuRow"){
		if(!target.hasClass('LeftMenuRowSelected')){
			$("#leftMenuOptionsView .LeftMenuRowSelected")
				.removeClass('LeftMenuRowSelected');
			
			target.addClass('LeftMenuRowSelected');
			$("#leftMenuOptionsView .SearchBox").prop('disabled', true);
			
			self.ShowFlightViewEvents();
		}
	} else if(target.attr('id') == "paramsListLeftMenuRow"){
		if(!target.hasClass('LeftMenuRowSelected')){
			$("#leftMenuOptionsView .LeftMenuRowSelected")
				.removeClass('LeftMenuRowSelected');
			
			target.addClass('LeftMenuRowSelected');
			$("#leftMenuOptionsView .SearchBox").prop('disabled', true);
			
			self.ShowFlightViewParamsList();
		}
	}
}