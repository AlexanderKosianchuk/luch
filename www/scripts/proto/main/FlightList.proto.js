var FLIGHTS_VIEW_SRC = location.protocol + '//' + location.host + "/view/flights.php";
	
function FlightList(langStr, srvcStrObj, eventHandler) 
{ 	
	this.langStr = langStr;
	this.actions = srvcStrObj['flightsPage'];
	
	this.eventHandler = eventHandler;
	this.flightListFactoryContainer = null;
	this.flightListTopMenu = null;
	this.flightListLeftMenu = null;
	this.flightListWorkspace = null;
	this.flightListOptions = null;
	this.flightListContent = null;
}

FlightList.prototype.FillFactoryContaider = function(factoryContainer) {
	var self = this;
	self.flightListFactoryContainer = factoryContainer;
			
	var pV = {
			action: self.actions["flightGeneralElements"],
			data: { 
				data: 'data'
			}
	};
	
	$.ajax({
		type: "POST",
		data: pV,
		dataType: 'json',
		url: FLIGHTS_VIEW_SRC,
		async: true
	}).fail(function(msg){
		console.log(msg);
	}).done(function(answ) {
		if(answ["status"] == "ok") {
			var data = answ['data'];

			self.flightListFactoryContainer.append(data['topMenu']);
			self.flightListFactoryContainer.append(data['leftMenu']);
			self.flightListFactoryContainer.append(data['fileUploadBlock']);
			
			self.flightListTopMenu = $('div#topMenuFlightList');			
			self.flightListLeftMenu = $('div#leftMenuFlightList');
			
			self.flightListLeftMenu.on("click", function(e){
				self.leftMenuClick(e);
			});
			
			self.topMenuUserButtClick();
			
			self.flightListFactoryContainer.append("<div id='flightListWorkspace' class='WorkSpace'></div>");
			self.flightListWorkspace = $("div#flightListWorkspace");
			
			self.ShowFlightsListInitial();
			self.TriggerResize();
			self.TriggerUploading();			
		} else {
			console.log(answ["error"]);
		}
    });
}

FlightList.prototype.topMenuUserButtClick = function(){
	var self = this;
	$("#userTopButt").on("click", function(e){
		self.eventHandler.trigger("userLogout");
	});
}

FlightList.prototype.leftMenuClick = function(e){
	var self = this,
	target = $(e.target);

	if(target.attr('id') == "flightLeftMenuRow"){
		if(!target.hasClass('LeftMenuRowSelected')){
			$("#leftMenuFlightList .LeftMenuRowSelected")
				.removeClass('LeftMenuRowSelected', {duration:500});
			
			target.addClass('LeftMenuRowSelected', {duration:500});
			
			//self.ShowFlightViewTemplates();
		}
	} else if(target.attr('id') == "bruTypesLeftMenuRow"){
		if(!target.hasClass('LeftMenuRowSelected')){
			$("#leftMenuFlightList .LeftMenuRowSelected")
				.removeClass('LeftMenuRowSelected', {duration:500});
			
			target.addClass('LeftMenuRowSelected', {duration:500});
			
			//self.ShowFlightViewEvents();
		}
	} else if(target.attr('id') == "docsLeftMenuRow"){
		if(!target.hasClass('LeftMenuRowSelected')){
			$("#leftMenuFlightList .LeftMenuRowSelected")
				.removeClass('LeftMenuRowSelected', {duration:500});
			
			target.addClass('LeftMenuRowSelected', {duration:500});
			
			//self.ShowFlightViewParamsList();
		}
	} else if(target.attr('id') == "usersLeftMenuRow"){
		if(!target.hasClass('LeftMenuRowSelected')){
			$("#leftMenuFlightList .LeftMenuRowSelected")
				.removeClass('LeftMenuRowSelected', {duration:500});
			
			target.addClass('LeftMenuRowSelected', {duration:500});
			
			//self.ShowFlightViewParamsList();
		}
	}
}

FlightList.prototype.ShowFlightViewOptions = function() {
	var self = this;

	if(self.flightListWorkspace != null) {
		self.flightListWorkspace.append("<div id='flightListOptions' class='OptionsMenu'></div>");
		self.flightListOptions = $("div#flightListOptions");
		
		var fligthOptionsStr = "<table v-align='top'><tr><td><label>" + this.langStr.flightList + " - " + "</label></td><td>";	
		fligthOptionsStr += 
			'<div>' +
		    	'<button id="selectFligthOptionsMenu" class="Button" style="margin-right:1px; min-width:155px;">' + this.langStr.initial + '</button>' +
		    	/*'<button id="sortFligthOptionsMenu">' + this.langStr.groupType + '</button>' +*/
		    '</div>' +
		    '<ul class="GroupType">' +
		    	'<li id="inTwoColumns">' + this.langStr.inTwoColumns + '</li>' +
		    	'<li id="treeView">' + this.langStr.treeView + '</li>' +
		    	'<li id="tableView">' + this.langStr.tableView + '</li>' +
		    	'<li id="byAditionalInfo" style="border:none;">' + this.langStr.byAditionalInfo + 
		    		'<input id="byAditionalInfoInput" style="min-width:155px;" type="text"/></li>' +
		    '</ul></td><td>' + 
		    '<button id="fileMenu" class="Button">' + this.langStr.fileMenu + '</button>'+
		    	'<ul class="FileMenuItems">' +
		    	'</ul>' +
		    '</td></tr></table>';
	    
		self.flightListOptions.append(fligthOptionsStr);
		 
	//	 $("button#sortFligthOptionsMenu").button({
	//		 text: false,
	//         icons: {
	//        	 primary: "ui-icon-triangle-1-s"
	//         },
	//
	//	 });
	
		 $("button#fileMenu").button({ disabled: true });
	
	//	 $("button#sortFligthOptionsMenu").on('click', function(e){
	//	    $(this).data('state', ($(this).data('state') == 'asc') ? 'desc' : 'asc');
	//	    $("button#sortFligthOptionsMenu").button({
	//	        icons: {
	//	            primary: ($(this).data('state') == "asc") ? "ui-icon-triangle-1-n" : "ui-icon-triangle-1-s"
	//	        }
	//	    });
	//
	//    	 return false;
	//     });
		 
		 var buttonSelectFligthOptionsMenu = $("button#selectFligthOptionsMenu").button();
		 buttonSelectFligthOptionsMenu.click(function(e) {
			 var menu = $(this).parent().next().show().position({
				 my: "left top",
	             at: "left bottom",
	             of: this
			 });
			 $(document).on("click", function(e) {
				 var target = $(e.target);			 
				 if(target.attr('id') !== 'byAditionalInfoInput'){
					 menu.hide();			 
				 }
			 });		 
			 return false;
		 }).parent()
			 .buttonset()
			 .next()
			 .hide()
			 .menu();
		 
		 $('#inTwoColumns').on("click", function(e) {
			 $("div#view").css("display", "none");
			 $("button#fileMenu").button({ disabled: true });
			 
			 self.ShowFlightsByPath();
			 buttonSelectFligthOptionsMenu.button({
				  label: self.langStr.inTwoColumns
			 });
		 });
		 
		 $('#treeView').on("click", function(e) {	
			 $("div#view").css("display", "none");
			 $("button#fileMenu").button({ disabled: true });
			 
			 self.ShowFlightsTree();
			 buttonSelectFligthOptionsMenu.button({
				  label: self.langStr.treeView
			 });
		 });
		 
		 $('#tableView').on("click", function(e) {	
			 $("div#view").css("display", "none");
			 $("button#fileMenu").button({ disabled: true });
			 
			 self.ShowFlightsTable();
			 buttonSelectFligthOptionsMenu.button({
				  label: self.langStr.tableView
			 });
		 });
		 
		$("div#view").on("click", function(e){
			var itemsCheck = $(".ItemsCheck:checked");
			if(itemsCheck.length == 1){
				var itemsCheckType = itemsCheck.data("type");
				if(itemsCheckType = 'flight'){
					var flightId = itemsCheck.data("flightid"),
						data = [flightId, null, null]
					self.eventHandler.trigger("viewFlightOptions", data);
				}
			}
		});
	}
}

/* ==================================================
 * INITIAL VIEW
 * ================================================== */

FlightList.prototype.ShowFlightsListInitial = function() { 
	var self = this;

	self.ShowFlightViewOptions();
	
	if(self.flightListWorkspace != null) {
		
		self.flightListWorkspace.append("<div id='flightListContent' class='Content'></div>");
		self.flightListContent = $("div#flightListContent");
						
		var pV = {
			action: self.actions["flightLastView"],
			data: {
				data: 'data'
			}
		};
		
		$.ajax({
			type: "POST",
			data: pV,
			url: FLIGHTS_VIEW_SRC,
			dataType: 'json',
			async: true,
			success: function(answ) {
				if(answ['status'] == 'ok'){
					var type = answ['type'];
					if(type == self.actions["flightTwoColumnsListByPathes"]){
						var flightList = answ['data'];
						self.flightListContent.append(flightList);
						$("button#selectFligthOptionsMenu").button({
							  label: self.langStr.inTwoColumns
						});
						self.SupportNaviButt();
						self.MakeDragable();
						self.MakeClickable();
					} else if (type == self.actions["flightListTree"]){
						var flightList = answ['data'];
						self.flightListContent.append(flightList);
						$("button#selectFligthOptionsMenu").button({
							  label: self.langStr.treeView
						});
						self.SupportJsTree();
						self.ResizeFlightList();
					} else if (type == self.actions["flightListTable"]){
						var flightList = answ['data'],
							sortCol = answ['sortCol'],
							sortType = answ['sortType'];
						self.flightListContent.append(flightList);
						$("button#selectFligthOptionsMenu").button({
							  label: self.langStr.tableView
						});
						self.SupportDataTable(sortCol, sortType);
						self.ResizeFlightList();
					}
					
				} else {
					console.log(data['error']);
				}
			}
		}).fail(function(msg){
			console.log(msg);
		});
	}
};

FlightList.prototype.ResizeFlightList = function(e) {
	var self = this;
	
	var tree = $(".Tree"),
		treeContent = $(".TreeContent");
	if((tree.length > 0) && (treeContent.length > 0)){
		tree.css('height', self.flightListContent.height() - 5);
		treeContent.css('height', self.flightListContent.height() - 5);
	}
}

FlightList.prototype.TriggerResize = function() { 
	this.eventHandler.trigger("resizeShowcase");
}

FlightList.prototype.TriggerUploading = function() { 
	this.eventHandler.trigger("uploading");
}

/* ==================================================
 * TWO COLUMN VIEW
 * ================================================== */

FlightList.prototype.ShowFlightsByPath = function() { 
	var self = this;
	
	self.flightListContent.slideUp(function(e){
		self.flightListContent.empty();
		/*self.mainContainerOptions.slideUp(function(e){
			self.mainContainerOptions.empty();
			self.ShowFlightViewOptions();
			self.mainContainerOptions.slideDown(function(e){*/
					
				var pV = {
					action: self.actions["flightTwoColumnsListByPathes"],
					data: {
						data: 'data'
					}
				};
				
				$.ajax({
					type: "POST",
					data: pV,
					url: FLIGHTS_VIEW_SRC,
					dataType: 'json',
					async: true,
					success: function(answ) {
						if(answ['status'] == 'ok'){
							var flightList = answ['data'];
							self.flightListContent.append(flightList);
							self.flightListContent.slideDown();
							self.SupportNaviButt();
							self.MakeDragable();
							self.MakeClickable();
						} else {
							console.log(data['error']);
						}
					}
				}).fail(function(msg){
					console.log(msg);
				});
			/*});
		});*/
	});
};

FlightList.prototype.MakeDragable = function() { 
	var self = this;
	$(".FolderPathInTwoColumnContainer").css("width", self.flightListContent.width() / 2 - 25);
	$(".FolderInTwoColumnContainer").css("width", self.flightListContent.width() / 2 - 25);
	
	$('ul#sortableRight').sortable({
		placeholder: "ui-state-highlight",
	    receive: function (event, ui) { 
	    	var target = $(this),
    			sender = ui.item,
    			senderPath = sender.data("folderpath"),
				targetPath = target.data("curpath");
    		
	    	if(senderPath != targetPath){
		    	if(sender.hasClass("FlightInTwoColumnContainer")){
		    		var senderId = sender.data("flightid");
		    		self.ActionChangePath("flight", senderId, targetPath);
		    		
			    	sender.fadeOut(function(e){
			    		sender.remove(); // remove original item
			        });
		    	} else if(sender.hasClass("FolderInTwoColumnContainer")){ 
		    		var folderdestination = sender.data("folderdestination");
		    		if(folderdestination != targetPath){
			    		self.ActionChangePath("folder", senderPath, targetPath);
		    		
		    			sender.fadeOut(function(e){
				    		sender.remove(); // remove original item
				        });		
		    		}
	    		}
			}
	    },
	    update: function(event, ui) {
	    	var target = $(this),
    			sender = ui.item,
    			senderPath = sender.data("folderpath"),
				targetPath = target.data("curpath");

	    	if(sender.hasClass("FlightInTwoColumnContainer")){ 
	    		//if dragging and destination same remove clone
	    		if(senderPath == targetPath){    			
	    			sender.addClass("ErrorDuringDrop");
	     			sender.fadeOut(2000, function(e){
			    		sender.remove(); // remove original item
			        });
	    		}
	    	} if(sender.hasClass("FolderInTwoColumnContainer")){ 
	    		var folderdestination = sender.data("folderdestination");
	    		//if dragging and destination same remove clone
	    		if((folderdestination == targetPath) || (senderPath == targetPath)){    			
	    			sender.addClass("ErrorDuringDrop");
	     			sender.fadeOut(2000, function(e){
			    		sender.remove(); // remove original item
			        });
	    		}
	    	}
	    }
	});
	
	$('ul#sortableLeft').sortable({
		placeholder: "ui-state-highlight",
	    receive: function (event, ui) { 
	    	var target = $(this),
    			sender = ui.item,
    			senderPath = sender.data("folderpath"),
				targetPath = target.data("curpath");
	    	    		
	    	if(senderPath != targetPath){
		    	if(sender.hasClass("FlightInTwoColumnContainer")){
		    		var senderId = sender.data("flightid");
		    		self.ActionChangePath("flight", senderId, targetPath);
		    		
			    	sender.fadeOut(function(e){
			    		sender.remove(); // remove original item
			        });
		    	} else if(sender.hasClass("FolderInTwoColumnContainer")){ 
		    		var folderdestination = sender.data("folderdestination");
		    		if(folderdestination != targetPath){
			    		self.ActionChangePath("folder", senderPath, targetPath);
		    		
		    			sender.fadeOut(function(e){
				    		sender.remove(); // remove original item
				        });		
		    		}
	    		}
			}
	    },
	    update: function(event, ui) {
	    	var target = $(this),
    			sender = ui.item,
    			senderPath = sender.data("folderpath"),
				targetPath = target.data("curpath");
	    	
	    	if(sender.hasClass("FlightInTwoColumnContainer")){ 
	    		//if dragging and destination same remove clone
	    		if(senderPath == targetPath){    			
	    			sender.addClass("ErrorDuringDrop");
	     			sender.fadeOut(2000, function(e){
			    		sender.remove(); // remove original item
			        });
	    		}
	    	} if(sender.hasClass("FolderInTwoColumnContainer")){ 
	    		var folderdestination = sender.data("folderdestination");
	    		//if dragging and destination same remove clone
	    		if((folderdestination == targetPath) || (senderPath == targetPath)){    			
	    			sender.addClass("ErrorDuringDrop");
	     			sender.fadeOut(2000, function(e){
			    		sender.remove(); // remove original item
			        });
	    		}
	    	}
	    }
	});
	
	$.each($("li#draggableRight"), function(index, val){
		$(val).draggable({
			connectToSortable: '#sortableLeft',
			containment: "TwoColumnsTable",
			helper: 'clone',
	        revert: 'invalid',
	        cursor: 'move'
	    });
	});
	
	$.each($("li#draggableLeft"), function(index, val){
		$(val).draggable({
			connectToSortable: '#sortableRight',
			containment: "twoColumnsTable",
			helper: 'clone',
	        revert: 'invalid',
	        cursor: 'move'
	    });
	});
	
	$.each($("li.FolderInTwoColumnContainer"), function(index, val){
		$(val).droppable({
			drop: function(ev, ui){
				var target = $(this),
	    			sender = ui.draggable,
	    			senderPath = sender.data("folderpath"),
					//targetPath = target.data("folderpath");
					targetPath = target.data("folderdestination");
				
				target.css({
					'border': 'none',
					'width': target.width() + 4,
					'height': target.height() - 4
				});
								
		    	if((senderPath != targetPath)) {		    	
		    		sender.fadeOut(function(e){
		    			sender.remove(); // remove original item
		    			
		    			if(sender.hasClass("FlightInTwoColumnContainer")){
		    				var flightId = sender.data('flightid');
		    				self.ActionChangePath('flight', flightId, targetPath);
		    			} else if(sender.hasClass("FolderInTwoColumnContainer")){
		    				var folderId = sender.data('folderdestination');
		    				self.ActionChangePath('folder', folderId, targetPath);
		    			}
			        });
		    	} else {
		    		sender.addClass("ErrorDuringDrop", {
		    			duration: 1000,
		    			easing: "easeInQuint",
		    			complete: function(e){
		    				sender.removeClass("ErrorDuringDrop", 200, "easeOutQuint");
		    			}
		    		});
		    	}
				
			},
			over: function( event, ui ) {
				if(event.target != ui.draggable) {
					var $this = $(this);
					$this.css({
						'border': '2px solid #c8c8c8',
						'width': $this.width() - 4,
						'height': $this.height() - 4
					});
				}
			},
			out: function( event, ui ) {
				var $this = $(this);
				$this.css({
					'border': 'none',
					'width': $this.width() + 4,
					'height': $this.height() + 4
				});
			}
		});
	});
	
	$.each($("div#dropable"), function(index, val){
		$(val).droppable({
		    drop: function(ev, ui){
		    	var draggable = $(ui.draggable),
		    		target = $(ev.target),
		    		prevPosition = draggable.data("position");
		    	
		    	//console.log(target.find(":first-child").data("curpath"));
		    	//console.log(draggable.data("folderpath"));
		    	
		    	draggable.css({
					'border': 'none'
				});		
		    	
		    	draggable.removeAttr("data-position");
		    	if(prevPosition == 'Right'){
			    	draggable.attr("data-position",'Left');
			    	draggable.attr("id",'draggableLeft');
			    	$.each($("li#draggableLeft"), function(index, val){
			    		$(val).draggable({
			    			connectToSortable: '#sortableRight',
			    			helper: 'clone',
			    	        revert: 'invalid',
			    	        cursor: 'move'
			    	    });
			    	});
		    	} else if(prevPosition == 'Left'){
		    		draggable.attr("data-position",'Right');
			    	draggable.attr("id",'draggableRight');
			    	$.each($("li#draggableRight"), function(index, val){
			    		$(val).draggable({
			    			connectToSortable: '#sortableLeft',
			    			helper: 'clone',
			    	        revert: 'invalid',
			    	        cursor: 'move'
			    	    });
			    	});
		    	}
		    	self.MakeClickable();
		    }
	    })
	});   
};

FlightList.prototype.MakeClickable = function() { 
	var self = this;
	
	$("li#draggableRight").on("dblclick", function(e){
		var target = $(e.delegateTarget),
			caller = $(e.target).prop("tagName");
		
		if(caller != 'INPUT'){
			if(target.hasClass("FolderInTwoColumnContainer")){
				self.ActionShowFolder(target);
			} else if(target.hasClass("FlightInTwoColumnContainer")){
				self.ActionOnDblClick(target);
			}
		}
	});
	//});
	
	//$.each($("li#draggableLeft"), function(index, val){
	$("li#draggableLeft").on("dblclick", function(e){
		var target = $(e.delegateTarget),
			caller = $(e.target).prop("tagName");

		if(caller != 'INPUT'){
			if(target.hasClass("FolderInTwoColumnContainer")){
				self.ActionShowFolder(target);
			} else if(target.hasClass("FlightInTwoColumnContainer")){
				self.ActionOnDblClick(target);
			}
		}
	});
	//});
	
	$(".ItemsCheck").on("change", function(e){
		var checked = $("input.ItemsCheck:checked"),
			fileMenu = $('ul.FileMenuItems'),
			fileMenuButt = $("button#fileMenu"),
			inLeftColumn = 0,
			inRightColumn = 0,
			folders = new Array(),
			flights = new Array();	
		
		$.each($(".ItemsCheck:checked"), function(i, el){
			var el = $(el);
			if(el.data('type') == 'flight'){
				flights.push(el);
			} else if(el.data('type') == 'folder') {
				folders.push(el);
			}
			
			if(el.data('position') == 'Right'){
				inRightColumn++;
			} else if(el.data('position') == 'Left') {
				inLeftColumn++;
			}
		});
			
		$("div#view").css("display", "none");
		
		if((flights.length == 1) && (folders.length == 0)){
			fileMenu.empty();
			
			$("div#view").css("display", "block");
			
			if(((inRightColumn > 0) && (inLeftColumn == 0)) || ((inRightColumn == 0) && (inLeftColumn > 0))){
				fileMenu.append('<li id="move">' + self.langStr.moveItem + '</li>');
			}
			
			fileMenu.append('<li id="process">' + self.langStr.processItem + '</li>');
			fileMenu.append('<li id="export">' + self.langStr.exportItem + '</li>');
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
			 
		} else if((flights.length == 0) && (folders.length == 1)){
			fileMenu.empty();
			fileMenu.append('<li id="open">' + self.langStr.openItem + '</li>');
			fileMenu.append('<li id="rename">' + self.langStr.renameItem + '</li>');
			
			//if selected only in one column
			if(((inRightColumn > 0) && (inLeftColumn == 0)) || ((inRightColumn == 0) && (inLeftColumn > 0))){
				fileMenu.append('<li id="move">' + self.langStr.moveItem + '</li>');
			}
			fileMenu.append('<li id="export">' + self.langStr.exportItem + '</li>');
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
		} else if((flights.length > 1) && (folders.length == 0)){
			fileMenu.empty();
			
			if(((inRightColumn > 0) && (inLeftColumn == 0)) || ((inRightColumn == 0) && (inLeftColumn > 0))){
				fileMenu.append('<li id="move">' + self.langStr.moveItem + '</li>');
			}
			fileMenu.append('<li id="export">' + self.langStr.exportItem + '</li>');
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
		} else if((flights.length == 0) && (folders.length > 1)){
			fileMenu.empty();
			
			if(((inRightColumn > 0) && (inLeftColumn == 0)) || ((inRightColumn == 0) && (inLeftColumn > 0))){
				fileMenu.append('<li id="move">' + self.langStr.moveItem + '</li>');
			}
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
		} else if((flights.length >= 1) && (folders.length >= 1)){
			fileMenu.empty();
			
			if(((inRightColumn > 0) && (inLeftColumn == 0)) || ((inRightColumn == 0) && (inLeftColumn > 0))){
				fileMenu.append('<li id="move">' + self.langStr.moveItem + '</li>');
			}
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
		} else {
			fileMenu.empty();
			fileMenuButt.button({ disabled: true });
		}
		
		$("li#removeSelection").on('click', function(e){
			
			$.each($(".ItemsCheck:checked"), function(i, el){
				var el = $(el).prop('checked', false);
			});
			fileMenuButt.button({ disabled: true });
		});
		
		$("li#delete").on('click', function(e){
			var inputItemsCheck = $("input.ItemsCheck:checked");
			
			$.each(inputItemsCheck, function(i, el){
				var el = $(el),
					type = el.data('type'),
					id = undefined;
				
				if(type == 'folder'){
					id = el.data('folderdestination');
				} else if(type == 'flight'){
					id = el.data('flightid');
				}
				self.DeleteItem(type, id).done(function(answ) {
					if(answ['status'] == 'ok'){
						el.removeAttr("checked");
						var parent = el.parents("li");
						parent.fadeOut(200);
					} else {
						console.log(answ['data']['error']);
					}
				});
			});
		});
		
		$("li#process").on('click', function(e){
			var inputItemsCheck = $("input.ItemsCheck:checked");
			
			$.each(inputItemsCheck, function(i, el){
				var el = $(el),
					type = el.data('type'),
					id = undefined;
				
				if(type == 'flight'){
					id = el.data('flightid');
					self.ProcessItem(id).done(function(answ) {
						if(answ['status'] == 'ok'){
							el.removeAttr("checked");
							var parent = el.parents("li");
							parent.fadeOut(200);
						} else {
							console.log(answ['data']['error']);
						}
					});
				}
			});
		});
	});
};

//FlightList.prototype.ActionOnClick = function(sender) { 
//	var self = this;
//	console.log("ActionOnClick");
//};

FlightList.prototype.ActionOnDblClick = function(sender) { 
	var self = this;
	console.log(sender);
	console.log("ActionOnDblClick");
};

FlightList.prototype.ActionChangePath = function(senderType, sender, target) { 
	var self = this;

	var pV = {
		action: '',
		data: {
			sender: sender,
			target: target
		}
	};
	
	if(senderType == 'flight'){
		pV.action = self.actions["flightChangePath"];
	} else if(senderType == 'folder'){
		pV.action = self.actions["folderChangePath"];
	}
		
	return $.ajax({
		type: "POST",
		data: pV,
		url: FLIGHTS_VIEW_SRC,
		dataType: 'json',
		async: true
	}).fail(function(msg){
		console.log(msg);
	});	
};

FlightList.prototype.ActionShowFolder = function(sender) { 
	var self = this,
		position = sender.data("position"),
		fullpath = sender.data("folderdestination");
	
	var pV = {
			action: self.actions["flightShowFolder"],
			data: {
				position: position,
				fullpath: fullpath
			}
		};
		
	$.ajax({
		type: "POST",
		data: pV,
		url: FLIGHTS_VIEW_SRC,
		dataType: 'json',
		async: true,
		success: function(answ) {
			if(answ['status'] == 'ok'){
				var flightList = answ['data'],
					column = $("td#filesContainer" + position);
				
				column.empty();
				column.append(flightList);
				self.SupportNaviButt();
				self.MakeDragable();
				self.MakeClickable();
			} else {
				console.log(data['error']);
			}
		}
	}).fail(function(msg){
		console.log(msg);
	});
};

FlightList.prototype.SupportNaviButt = function() { 
	var self = this;
		
	$("img#upperFromPath").on("click", function(e){
		var el = $(e.target),
			position = el.parent().data("position"),
			path = el.parent().data("path");
			self.GoUpper(position, path);
	}).on('mouseover', function(e){
		$(e.target).addClass('ui-state-focus');
	}).on('mouseleave', function(e){
		$(e.target).removeClass('ui-state-focus');
	});
	
	$("img#toRootFromPath").on("click", function(e){
		var position = $(e.target).parent().data("position");
		self.UpdateColumn(position, 0);
	}).on('mouseover', function(e){
		$(e.target).addClass('ui-state-focus');
	}).on('mouseleave', function(e){
		$(e.target).removeClass('ui-state-focus');
	});
	
	$("img#refreshFolder").on("click", function(e){
		var target = $(e.target).parent(),
			position = target.data("position"),
			path = target.data("path");
		self.UpdateColumn(position, path);
	}).on('mouseover', function(e){
		$(e.target).addClass('ui-state-focus');
	}).on('mouseleave', function(e){
		$(e.target).removeClass('ui-state-focus');
	});
	
	$("img#newFolderInPath").on("click", function(e){
		var target = $(e.target).parent(),
			position = target.data("position"),
			path = target.data("path");
		self.ShowNewFolder(position, path);
	}).on('mouseover', function(e){
		$(e.target).addClass('ui-state-focus');
	}).on('mouseleave', function(e){
		$(e.target).removeClass('ui-state-focus');
	});
};

FlightList.prototype.UpdateColumn = function(position, path) { 
	var self = this;
	
	var pV = {
		action: self.actions["flightShowFolder"],
		data: {
			position: position,
			fullpath: path
		}
	};
		
	$.ajax({
		type: "POST",
		data: pV,
		url: FLIGHTS_VIEW_SRC,
		dataType: 'json',
		async: true,
		success: function(answ) {
			if(answ['status'] == 'ok'){
					var flightList = answ['data'],
						column = $("td#filesContainer" + position);
					
					column.empty();
					column.append(flightList);
					self.SupportNaviButt();
					self.MakeDragable();
					self.MakeClickable();
				} else {
					console.log(data['error']);
				}
			}
		}).fail(function(msg){
			console.log(msg);
		});
}

FlightList.prototype.GoUpper = function(position, path) { 
	var self = this;
	
	var pV = {
		action: self.actions["flightGoUpper"],
		data: {
			position: position,
			fullpath: path
		}
	};
		
	$.ajax({
		type: "POST",
		data: pV,
		url: FLIGHTS_VIEW_SRC,
		dataType: 'json',
		async: true,
		success: function(answ) {
				if(answ['status'] == 'ok'){
					var flightList = answ['data'],
						column = $("td#filesContainer" + position);
					
					column.empty();
					column.append(flightList);
					self.SupportNaviButt();
					self.MakeDragable();
					self.MakeClickable();
				} else {
					console.log(data['error']);
				}
			}
		}).fail(function(msg){
			console.log(msg);
		});
}

FlightList.prototype.ShowNewFolder = function(position, path) { 
	var self = this,
		folderContainer = $("td#filesContainer" + position + " .NonSortableList"),
		elWidth = $(".FolderPathInTwoColumnContainer").width(),
		folderpath = folderContainer.data("folderpath"),
		recentlyCreatedFolder = $("#recentlyCreatedFolder"),
		foldersNamesArr = new Array();	

	$.each($("td#filesContainer" + position + " .NonSortableList .FolderInTwoColumnContainer"), function(i, el){
		var el = $(el);
		foldersNamesArr.push(el.text());
	});
		
	//check if exist $("input#recentlyCreatedFolder")
	if($.inArray(recentlyCreatedFolder.val(), foldersNamesArr) > -1){
		recentlyCreatedFolder.css({
			"background-color": "#FFD3D3"
		});
	} else {
		recentlyCreatedFolder.parent().empty().append(recentlyCreatedFolder.val());
		var recentlyCreatedFolderName = self.langStr['newFolder'];
		
		//append counter in brackets (1), (2) ...
		var i = 1;
		while($.inArray(recentlyCreatedFolderName, foldersNamesArr) > -1){	
			if(recentlyCreatedFolderName.indexOf("(" + i + ")") != -1){
				recentlyCreatedFolderName = recentlyCreatedFolderName.replace("(" + i + ")","(" + (i + 1) + ")");
				i++;
			} else {
				recentlyCreatedFolderName += " (" + i + ")";
			}
		}
		
		var folderEl = "<li id='draggable" + position + "' class='FolderInTwoColumnContainer' style='width:"+elWidth+"px;'" +
				"data-position='" + position + "' " +
				"data-folderpath='" + folderpath + "'>" +
			"<table><tr><td style='width:100%;'>" +
			"<input id='recentlyCreatedFolder' type='text' " +
				"style='width:" + (elWidth - 60) + "px;' " +
				"value='" + recentlyCreatedFolderName + "'/>" +
			"</td><td style='width:15px; vertical-align:top;'>" +
			"<input class='ItemsCheck' type='checkbox' " + 
				"data-type='folder' " +
				"data-position='" + position + "' " +
				"data-folderpath='" + folderpath + "'/>" +
			"</td><tr></table>" + "</li>";
		folderContainer.append(folderEl);
		
		recentlyCreatedFolder = $("#recentlyCreatedFolder");	
	}
	
	recentlyCreatedFolder.focus();
	recentlyCreatedFolder.on("focusout", function(e){
		var el = $(e.target),
			text = el.val(),
			folderRow = el.closest('li'),
			folderpath = folderRow.data("folderpath"),
			position = folderRow.data("position"),
			folderContainer = $("td#filesContainer" + position + " .NonSortableList"),
			folderContainerAnotherColumn = new Object(),
			positionAnotherColumn = '',
			foldersNamesArr = new Array();	

		$.each($("td#filesContainer" + position + " .NonSortableList .FolderInTwoColumnContainer"), function(i, existEl){
			var existEl = $(existEl);
			foldersNamesArr.push(existEl.text());
		});
			
		if($.inArray(text, foldersNamesArr) == -1){	
			self.CreateNewFolder(text, folderpath).done(function(answ) {
				var folderdestination = "";
					
				if(answ['status'] == 'ok'){
					folderdestination = answ['data']['folderId'];			
					self.MakeDragable();
					self.MakeClickable();

					folderRow.data("folderdestination", folderdestination);
					folderRow.find(".ItemsCheck").data("folderdestination", folderdestination);
					
					el.parent().empty().append(text);
					
					if(position == 'Right') {
						positionAnotherColumn = 'Left',
						folderContainerAnotherColumn = $("td#filesContainer" + folderContainerAnotherColumn + " .NonSortableList");
					} else if(position == 'Left') {
						positionAnotherColumn = 'Right',
						folderContainerAnotherColumn = $("td#filesContainer" + positionAnotherColumn + " .NonSortableList");
					}
					
					//if same path in left and right shown, append to another also
					if(folderContainer.data("path") == folderContainerAnotherColumn.data("path")) {
						var folderEl = "<li id='draggable" + positionAnotherColumn + "' class='FolderInTwoColumnContainer' " +
							"data-position='" + positionAnotherColumn + "' " +
							"data-folderpath='" + folderpath + "' " + 
							"data-folderdestination='" + folderdestination + "'>" +
						"<table><tr><td style='width:100%;'>" + recentlyCreatedFolderName + 
						"</td><td style='width:15px; vertical-align:top;'>" +
						"<input class='ItemsCheck' type='checkbox' data-type='folder' "+
							"data-position='" + position + "' " +
							"data-folderpath='" + folderpath + "' " +
							"data-folderdestination='" + folderdestination + "'/>" +
						"</td><tr></table>" + "</li>";
						folderContainerAnotherColumn.append(folderEl);
					}	
				} else {
					console.log(data['error']);
				}
			});
		}
	});
	
	recentlyCreatedFolder.on("input", function(e){
		var el = $(e.target);
		el.css({
			"background-color": "#fff"
		});
		
		if($.inArray(el.val(), foldersNamesArr) > -1){
			el.css({
				"background-color": "#FFD3D3"
			});
		}
	});
}

FlightList.prototype.CreateNewFolder = function(folderName, folderPath) { 
	var self = this,
		folderdestination = 0;
	
	var pV = {
		action: self.actions["folderCreateNew"],
		data: {
			folderName: folderName,
			fullpath: folderPath
		}
	};
	
	return $.ajax({
		type: "POST",
		data: pV,
		url: FLIGHTS_VIEW_SRC,
		dataType: 'json',
		async: true
	}).fail(function(msg){
		console.log(msg);
	});
}

FlightList.prototype.RenameFolder = function(folderId, folderName) { 
	var self = this;
	
	var pV = {
		action: self.actions["folderRename"],
		data: {
			folderId: folderId,
			folderName: folderName
		}
	};
	
	return $.ajax({
		type: "POST",
		data: pV,
		url: FLIGHTS_VIEW_SRC,
		dataType: 'json',
		async: true
	}).fail(function(msg){
		console.log(msg);
	});
}

FlightList.prototype.DeleteItem = function(type, id) { 
	var self = this;
	
	var pV = {
		action: self.actions["itemDelete"],
		data: {
			type: type,
			id: id
		}
	};
	
	return $.ajax({
		type: "POST",
		data: pV,
		url: FLIGHTS_VIEW_SRC,
		dataType: 'json',
		async: true
	}).fail(function(msg){
		console.log(msg);
	});
}

FlightList.prototype.ProcessItem = function(id) { 
	var self = this;
	
	var pV = {
		action: self.actions["itemProcess"],
		data: {
			id: id
		}
	};
	
	return $.ajax({
		type: "POST",
		data: pV,
		url: FLIGHTS_VIEW_SRC,
		dataType: 'json',
		async: true
	}).fail(function(msg){
		console.log(msg);
	});
}

FlightList.prototype.ShowFlight = function(id) { 
	$("div#flightLeftMenuRow").trigger("showOptions", id);
	return false;
}

/* ==================================================
 * TREE VIEW
 * ================================================== */

FlightList.prototype.ShowFlightsTree = function() { 
	var self = this;

	self.flightListContent.slideUp(function(e){
		self.flightListContent.empty();
		/*self.mainContainerOptions.slideUp(function(e){
			self.mainContainerOptions.empty();
			self.ShowFlightViewOptions();
			self.mainContainerOptions.slideDown(function(e){*/
					
				var pV = {
					action: self.actions["flightListTree"],
					data: {
						data: 'data'
					}
				};
				
				$.ajax({
					type: "POST",
					data: pV,
					url: FLIGHTS_VIEW_SRC,
					dataType: 'json',
					async: true,
					success: function(answ) {
						if(answ['status'] == 'ok'){
							var flightList = answ['data'];
							self.flightListContent.append(flightList);
							self.flightListContent.slideDown(function(e){
								self.SupportJsTree();
								self.ResizeFlightList(e);
							});
						} else {
							console.log(data['error']);
						}
					}
				}).fail(function(msg){
					console.log(msg);
				});
			/*});
		});*/
	});
};

/*=======================================================================
 * JSTREE SERVICE
 * */
FlightList.prototype.SupportJsTree = function() { 
	var self = this,
	contentPlace = $("#jstreeContent");
	
	var treePrivate = $('#jstree').on("select_node.jstree", function(e, data){
		var selectedjsTreeNode = 0;
		if(data.node.type == 'flight'){
			selectedjsTreeNode = data.node.parent;
		} else {
			selectedjsTreeNode = data.node.id;
		}
		
		self.ShowContent(selectedjsTreeNode).done(function(answ){
			contentPlace.empty();
			if(answ['status'] == 'ok'){
				var content = answ['data'];
				contentPlace.append(content);
				self.SupportContent();
			} else {
				console.log(answ)
			}
		});
	}).on('loaded.jstree', function(e, data) {
	    // invoked after jstree has loaded
		var node = $('#jstree').jstree('get_selected'),
		selectedjsTreeNode = node[0];

		self.ShowContent(selectedjsTreeNode).done(function(answ){
			contentPlace.empty();
			if(answ['status'] == 'ok'){
				var content = answ['data'];
				contentPlace.append(content);
				self.SupportContent();
			} else {
				console.log(answ)
			}
		});
	}).on("create_node.jstree", function(e, data){
		var node = data.node,
			parentId = data.parent,
			folderName = node.text;
		
		self.CreateNewFolder(folderName, parentId).done(function(answ){
			var nodeNewId = answ["data"]['folderId'];
			data.instance.set_id(node, nodeNewId);
			data.instance.set_type(node, "folder");
		});
	}).on("delete_node.jstree", function(e, data){
		var node = data.node,
			type = node.type,
			id = data.node.id;
		
		self.DeleteItem(type, id).done(function(answ) {
			if(answ['status'] == 'ok'){
				//show root
				var rootNodeId = 0;
				$('#jstree').jstree("select_node", "#" + rootNodeId + "_anchor"); 
				self.ShowContent(0).done(function(answ){
					contentPlace.empty();
					if(answ['status'] == 'ok'){
						var content = answ['data'];
						contentPlace.append(content);
						self.SupportContent();
					} else {
						console.log(answ)
					}
				});
			} else {
				console.log(answ['data']['error']);
			}
		});

	}).on("rename_node.jstree", function(e, data){
		var node = data.node,
		id = node.id,
		folderName = node.text;
	
		self.RenameFolder(id, folderName).done(function(answ) {
			if(answ['status'] == 'ok'){
				self.ShowContent(id).done(function(answ){
					contentPlace.empty();
					if(answ['status'] == 'ok'){
						var content = answ['data'];
						contentPlace.append(content);
						self.SupportContent();
					} else {
						console.log(answ)
					}
				});
			} else {
				console.log(answ['data']['error']);
			}
		});
	}).on("move_node.jstree", function(e, data){
		var node = data.node,
		type = node.type,
		id = node.id,
		newParent = node.parent,
		isNewParentInt =  /^\+?(0|[1-9]\d*)$/.test(newParent);
		
		if(isNewParentInt){
			var parentNode = $("li#" + newParent).find("a").find("i");
			
			if(parentNode.hasClass('jstree-folder')){
				
				self.ActionChangePath(type, id, newParent).done(function(e){
					self.ShowContent(newParent).done(function(answ){
						contentPlace.empty();
						if(answ['status'] == 'ok'){
							var content = answ['data'];
							contentPlace.append(content);
							self.SupportContent();
						} else {
							console.log(answ)
						}
					});
				});
			} else {
				alert("Incorrect action");
				treePrivate.jstree("refresh");
			}
		}
	}).jstree({
		"types" : {
			"folder" : {
				"icon" : "jstree-folder"
			},
			"flight" : {
				"icon" : "jstree-file"
			}
		},
		'core' : {
			'data' : {
				"url" : FLIGHTS_VIEW_SRC,
				"type": "POST",
				"dataType" : "json", // needed only if you do not supply JSON headers
				"data" : function (node) {
					var pV = { 
						action : self.actions["receiveTree"],
						data : {
							data : 'data'
						}
					};
					return pV;
				}
			},
			"check_callback" : true
		},
	    "plugins" : ["dnd", "types", "contextmenu"],
	    "contextmenu": {
	        "items": function ($node) {
	        	var tree = $("#jstree").jstree(true);
	            return {
	                "Create": {
	                    "separator_before": false,
	                    "separator_after": false,
	                    "label": "Create",
	                    "action": function (obj) { 
	                        $node = tree.create_node($node);
	                        tree.edit($node);
	                    }
	                },
	                "Rename": {
	                    "separator_before": false,
	                    "separator_after": false,
	                    "label": "Rename",
	                    "action": function (obj) {
	                    	if($node.type != "flight") {
		                        tree.edit($node);
	                    	} else {
	                    		return false;
	                    	}
	                    }
	                },                         
	                "Remove": {
	                    "separator_before": false,
	                    "separator_after": false,
	                    "label": "Remove",
	                    "action": function (obj) { 
	                        tree.delete_node($node);
	                    }
	                }
	            };
	        }
	    }
	});
}

FlightList.prototype.ShowContent = function(folderId) { 
	var self = this,
		pV = {
			action : self.actions["showFolderContent"],
			data : {
				folderId: folderId
			}
	    };
            
    return $.ajax({
		url: FLIGHTS_VIEW_SRC,
		type: "POST",
		data: pV,
		dataType: "json",
		async: true
	}).fail(function(e){
		console.log(e);
	});
}

FlightList.prototype.SupportContent = function() { 
	var self = this;
	$(".ItemsCheck").on("change", function(e){

		var checked = $(".ItemsCheck:checked"),
			fileMenu = $('ul.FileMenuItems'),
			fileMenuButt = $("button#fileMenu"),
			folders = new Array(),
			flights = new Array();	
		
		$.each(checked, function(i, el){
			var el = $(el);
			if(el.data('type') == 'flight'){
				flights.push(el);
			} else if(el.data('type') == 'folder') {
				folders.push(el);
			}
		});
			
		$("div#view").css("display", "none");
		
		if((flights.length == 1) && (folders.length == 0)){
			fileMenu.empty();
			
			$("div#view").css("display", "block");
			
			fileMenu.append('<li id="process">' + self.langStr.processItem + '</li>');
			fileMenu.append('<li id="export">' + self.langStr.exportItem + '</li>');
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
			 
		} else if((flights.length == 0) && (folders.length == 1)){
			fileMenu.empty();
			fileMenu.append('<li id="open">' + self.langStr.openItem + '</li>');
			fileMenu.append('<li id="rename">' + self.langStr.renameItem + '</li>');
		
			fileMenu.append('<li id="export">' + self.langStr.exportItem + '</li>');
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
		} else if((flights.length > 1) && (folders.length == 0)){
			fileMenu.empty();
			
			fileMenu.append('<li id="export">' + self.langStr.exportItem + '</li>');
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
		} else if((flights.length == 0) && (folders.length > 1)){
			fileMenu.empty();
			
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
		} else if((flights.length >= 1) && (folders.length >= 1)){
			fileMenu.empty();
			
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
		} else {
			fileMenu.empty();
			fileMenuButt.button({ disabled: true });
		}
		
		$("li#open").on('click', function(e){
			var inputItemsCheck = $(".ItemsCheck:checked"),
			folderId = inputItemsCheck.data('folderdestination'),
			contentPlace = $("#jstreeContent");
			self.ShowContent(folderId).done(function(answ){
				contentPlace.empty();
				if(answ['status'] == 'ok'){
					var content = answ['data'];
					contentPlace.append(content);
					self.SupportContent();
				} else {
					console.log(answ)
				}
			});

			fileMenuButt.button({ disabled: true });
		});
		
		$("li#rename").on('click', function(e){
			var inputItemsCheck = $(".ItemsCheck:checked"),
			id = inputItemsCheck.data("folderdestination"),
			parent = inputItemsCheck.parent(),
			row = parent.parent(),
			parentText = parent.text();
			parent.text("");

			parent.append(inputItemsCheck);
			parent.append("<input id='currentChangedNameFolder' size='50' value='"+parentText+"'/>");
			
			row.off("click");
			row.on("click", function(e){
				var nodeName = $(e.target)[0].tagName;
				if(nodeName == "DIV"){
					var currentChangedNameFolder = $("#currentChangedNameFolder").val();
					parent.text("");
					parent.append(inputItemsCheck);
					parent.append(currentChangedNameFolder);
			
					self.RenameFolder(id, currentChangedNameFolder).done(function(answ) {
						if(answ['status'] != 'ok'){
							console.log(answ['data']['error']);
						}
					});
				}
			});
		});
		
		$("li#removeSelection").on('click', function(e){
			
			$.each($(".ItemsCheck:checked"), function(i, el){
				var el = $(el).prop('checked', false);
			});
			fileMenuButt.button({ disabled: true });
		});
		
		$("li#delete").on('click', function(e){
			var inputItemsCheck = $(".ItemsCheck:checked");
			
			$.each(inputItemsCheck, function(i, el){
				var el = $(el),
					type = el.data('type'),
					id = undefined;
				
				if(type == 'folder'){
					id = el.data('folderdestination');
				} else if(type == 'flight'){
					id = el.data('flightid');
				}
				self.DeleteItem(type, id).done(function(answ) {
					if(answ['status'] == 'ok'){
						el.removeAttr("checked");
						var parent = el.parents(".JstreeContentItemFlight");
						parent.fadeOut(200);
					} else {
						console.log(answ['data']['error']);
					}
				});
			});
		});
		
		$("li#process").on('click', function(e){
			var inputItemsCheck = $(".ItemsCheck:checked");
			
			$.each(inputItemsCheck, function(i, el){
				var el = $(el),
					type = el.data('type'),
					id = undefined;
				
				if(type == 'flight'){
					id = el.data('flightid');
					self.ProcessItem(id).done(function(answ) {
						if(answ['status'] == 'ok'){
							el.removeAttr("checked");
							var parent = el.parents("li");
							parent.fadeOut(200);
						} else {
							console.log(answ['data']['error']);
						}
					});
				}
			});
		});
	});
}
	
/* ==================================================
 * TABLE VIEW
 * ================================================== */
	
FlightList.prototype.ShowFlightsTable = function() { 
	var self = this;

	self.flightListContent.slideUp(function(e){
		self.flightListContent.empty();					
				var pV = {
					action: self.actions["flightListTable"],
					data: {
						data: 'data'
					}
				};
				
				$.ajax({
					type: "POST",
					data: pV,
					url: FLIGHTS_VIEW_SRC,
					dataType: 'json',
					async: true,
					success: function(answ) {
						if(answ['status'] == 'ok'){
							var flightList = answ['data'],
								sortCol = answ['sortCol'],
								sortType = answ['sortType'];
							self.flightListContent.append(flightList);
							self.flightListContent.slideDown(function(e){
								self.SupportDataTable(sortCol, sortType);
								self.ResizeFlightList(e);
							});
						} else {
							console.log(data['error']);
						}
					}
				}).fail(function(msg){
					console.log(msg);
				});
			/*});
		});*/
	});
};

FlightList.prototype.SupportDataTable = function(sortColumn, sortType) {
	var self = this,
		sortType = sortType.toLowerCase();
		
	var oTable = $('#flightTable').dataTable( {
		"bInfo": false,
		"bSort": true,
		"aoColumnDefs": [
		    { 'bSortable': false, 'aTargets': [0] },
		    { "sClass": "FlightTableCheckboxCenter", 'aTargets': [0] }
		],
		"order": [[ sortColumn, sortType]],
		"bFilter": false,
		"bLengthChange": false,
        "bAutoWidth": false,
        "bProcessing": true,
		"bServerSide": true,
		"aLengthMenu": false,
		"bPaginate": false,
        "sAjaxSource": FLIGHTS_VIEW_SRC,
        "fnServerData": function ( sSource, aoData, fnCallback) {     	
			var pV = {
				action: self.actions["segmentTable"],
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
				self.SupportTableContent();
			})
			.fail(function(a){
				console.log(a);
			});
		},
        "oLanguage": self.langStr.dataTable,
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

FlightList.prototype.SupportTableContent = function() { 
	var self = this;
	$(".ItemsCheck").on("change", function(e){

		var checked = $(".ItemsCheck:checked"),
			fileMenu = $('ul.FileMenuItems'),
			fileMenuButt = $("button#fileMenu"),
			folders = new Array(),
			flights = new Array();	
		
		$.each(checked, function(i, el){
			var el = $(el);
			if(el.data('type') == 'flight'){
				flights.push(el);
			} else if(el.data('type') == 'folder') {
				folders.push(el);
			}
		});
			
		$("div#view").css("display", "none");
		
		if((flights.length == 1) && (folders.length == 0)){
			fileMenu.empty();
			
			$("div#view").css("display", "block");
			
			fileMenu.append('<li id="process">' + self.langStr.processItem + '</li>');
			fileMenu.append('<li id="export">' + self.langStr.exportItem + '</li>');
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
			 
		} else if((flights.length == 0) && (folders.length == 1)){
			fileMenu.empty();
			fileMenu.append('<li id="open">' + self.langStr.openItem + '</li>');
			fileMenu.append('<li id="rename">' + self.langStr.renameItem + '</li>');
		
			fileMenu.append('<li id="export">' + self.langStr.exportItem + '</li>');
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
		} else if((flights.length > 1) && (folders.length == 0)){
			fileMenu.empty();
			
			fileMenu.append('<li id="export">' + self.langStr.exportItem + '</li>');
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
		} else if((flights.length == 0) && (folders.length > 1)){
			fileMenu.empty();
			
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
		} else if((flights.length >= 1) && (folders.length >= 1)){
			fileMenu.empty();
			
			fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
			fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
			
			fileMenuButt.button({ disabled: false }).click(function() {
				 var menu = $(this).next().show().position({
					 my: "left top",
					 at: "left bottom",
					 of: this
				 });
				 $(document).on("click",function(e) {
					 menu.hide();
				 });
				 return false;
			 }).next()
				 .buttonset()
				 .hide()
				 .menu();
		} else {
			fileMenu.empty();
			fileMenuButt.button({ disabled: true });
		}
		
		$("li#open").on('click', function(e){
			var inputItemsCheck = $(".ItemsCheck:checked"),
			folderId = inputItemsCheck.data('folderdestination'),
			contentPlace = $("#jstreeContent");
			self.ShowContent(folderId).done(function(answ){
				contentPlace.empty();
				if(answ['status'] == 'ok'){
					var content = answ['data'];
					contentPlace.append(content);
					self.SupportContent();
				} else {
					console.log(answ)
				}
			});

			fileMenuButt.button({ disabled: true });
		});
		
		$("li#rename").on('click', function(e){
			var inputItemsCheck = $(".ItemsCheck:checked"),
			id = inputItemsCheck.data("folderdestination"),
			parent = inputItemsCheck.parent(),
			row = parent.parent(),
			parentText = parent.text();
			parent.text("");

			parent.append(inputItemsCheck);
			parent.append("<input id='currentChangedNameFolder' size='50' value='"+parentText+"'/>");
			
			row.off("click");
			row.on("click", function(e){
				var nodeName = $(e.target)[0].tagName;
				if(nodeName == "DIV"){
					var currentChangedNameFolder = $("#currentChangedNameFolder").val();
					parent.text("");
					parent.append(inputItemsCheck);
					parent.append(currentChangedNameFolder);
			
					self.RenameFolder(id, currentChangedNameFolder).done(function(answ) {
						if(answ['status'] != 'ok'){
							console.log(answ['data']['error']);
						}
					});
				}
			});
		});
		
		$("li#removeSelection").on('click', function(e){
			
			$.each($("input.ItemsCheck:checked"), function(i, el){
				var el = $(el).prop('checked', false);
			});
			fileMenuButt.button({ disabled: true });
		});
		
		$("li#delete").on('click', function(e){
			var inputItemsCheck = $("input.ItemsCheck:checked");
			
			$.each(inputItemsCheck, function(i, el){
				var el = $(el),
					type = el.data('type'),
					id = undefined;
				
				if(type == 'folder'){
					id = el.data('folderdestination');
				} else if(type == 'flight'){
					id = el.data('flightid');
				}
				self.DeleteItem(type, id).done(function(answ) {
					if(answ['status'] == 'ok'){
						el.removeAttr("checked");
						var parent = el.parents("tr");
						parent.fadeOut(200);
					} else {
						console.log(answ['data']['error']);
					}
				});
			});
		});
		
		$("li#process").on('click', function(e){
			var inputItemsCheck = $("input.ItemsCheck:checked");
			
			$.each(inputItemsCheck, function(i, el){
				var el = $(el),
					type = el.data('type'),
					id = undefined;
				
				if(type == 'flight'){
					id = el.data('flightid');
					self.ProcessItem(id).done(function(answ) {
						if(answ['status'] == 'ok'){
							el.removeAttr("checked");
							var parent = el.parents("li");
							parent.fadeOut(200);
						} else {
							console.log(answ['data']['error']);
						}
					});
				}
			});
		});
	});
}
