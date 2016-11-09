// JavaScript Document
var CHART_FILE = 'chart.php',
	TABLE_FILE = 'table.php',
	CHART_PARAM_INFO_SET_PARAM_COLOR = 'setColor',
	PRINT_COLOR_EVENTS = 'printColorEvents',
	PRINT_BLACK_EVENTS = 'printBlackEvents',
	scriptPrint = location.protocol + '//' + location.host + '/' + 'asyncPrint.php',
	scriptUpdateException = location.protocol + '//' + location.host + '/' + 'asyncFlightExc.php',
	scriptUpdateParamColor = location.protocol + '//' + location.host + '/' + 'asyncParamInfo.php',
	LANG_FILE =  location.protocol + '//' + location.host + "/lang/" + "RU.lang",
	LANG_FILE_DEFAULT =  location.protocol + '//' + location.host + "/lang/" + "Default.lang";

//when user choose params from params list system creates template for it with 
//hardly coded name, that rewrites each time
var PARAMS_TPL_NAME = 'last';

$(document).ready(function(){	
	var lang = Object();
	
    $( "#accordion" ).accordion({
    	heightStyle: "content"
    });
	
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
	
    $.colorpicker.regional['current'] = {
		ok:				lang.colorpickerOk,
		cancel:			lang.colorpickerCancel,
		none:			lang.colorpickerNone,
		button:			lang.colorpickerButton,
		title:			lang.colorpickerTitle,
		transparent:	lang.colorpickerTransparent
    };
    
    var bruType = $("input#bruType").val();
    
    $('input.colorpicker-popup').on("click", function(e){
    	var $this = $(this);
    	if($this.data("colorpicker") == false) {
    		$this.colorpicker({
            	regional: 'current',
        		ok: function(event, color) {
        			var pV = {
        				action: CHART_PARAM_INFO_SET_PARAM_COLOR,
        				bruType : bruType,	
    					paramCode : $this.data("paramcode"),
    					color: color.formatted
    				};

    				$this.css({
    					'background-color': '#' + color.formatted,
    					'color': '#' + color.formatted
    				});
    				
    				$.ajax({
    					dataType : "json",
    					type: "POST",
    					url : scriptUpdateParamColor,
    					data : pV,
    				});
				}
            })
    	
    		$this.data("colorpicker", 'true');    		
    		$this.colorpicker('open');
    	}
    });
    
	
	var dialog = $("div#dialog").dialog({
		resizable: false,
		modal: true,
		autoOpen: false,
		draggable: true,
		position: 'top',
		buttons: [{
            text: lang.continueLabel,
            click: function() {
              $( this ).dialog( "close" );
        }}],
		width: 400
	}),
	dialogText = $("div#dialog p");
	
	var showTplForm = $("form#showTpl"), 
		showParamsForm = $("form#showParams"),
		showTplOnChartBut = $("button#showChartFromTplBut"),
		showTplOnTableBut = $("button#showTableFromTplBut"),
		showParamsOnChartBut = $("button#showParamsOnChartBut"),
		showParamsOnTableBut = $("button#showParamsOnTableBut"),
		createBut = $("button#createTplBut"),
		editBut = $("button#editTplBut"),
		defaultBut = $("button#defaultTplBut"),
		delBut = $("button#delTplBut"),
		
		tplOption = $("option#tplOption"),
		tplOptionToEdit = $("option#tplOptionToEdit"),
		apChToCreate = $("input#apCheckboxGroupToCreate"),
		bpChToCreate = $("input#bpCheckboxGroupToCreate"),
		apChToEdit = $("input#apCheckboxGroupToEdit"),
		bpChToEdit = $("input#bpCheckboxGroupToEdit"),
		
		apChToShow = $("input#apCheckboxGroup"),
		bpChToShow = $("input#bpCheckboxGroup"),
		tplNames = $("input#tplNames"),
		//tplNamesShowParams = $("input#tplNamesShowParams"), //this 
		
		startFrameFromTpl = $("input#startFrameFromTpl"),
		endFrameFromTpl = $("input#endFrameFromTpl"),
		/*startFrame = $("input#startFrameToShow"),
		endFrame = $("input#endFrameFromToShow"),*/
		
		framesCount = $("input#framesCount").attr('value'),
		stepLength = $("input#stepLength").attr('value'),
		stepsCount = framesCount * stepLength,
		
		sliderContainer = $("div#slider-range"),
		amount = $("input#amount"),
		flightId = $("input#flightId").val(),
		username = $("input#username").val(),
		formToChartByException = $("form#toChartByException"),
		excFlightId = $("input#excFlightId"),
		excTpls = $("input#excTpls"),
		excStartFrame = $("input#excStartFrame"),
		excEndFrame = $("input#excEndFrame"),
		
		printColorBut = $("button#printColor"),
		printBlackBut = $("button#printBlack"),
		printAction = $("input#action"),
		printEventsForm = $("form#printEvents");
			
	//creating instances
	var tpl = new ParamSetTemplate();
		
	//=============================================================
	
	//=============================================================
	//flight exceptions table services	
	
	//=============================================================
	//false alarm
	$("input#reliability").on("click", function(e){
		var $this = $(this),
			falseAlarmState = 0;
		
		if($this.prop("checked")){
			falseAlarmState = 0;
		} else {
			falseAlarmState = 1;
		}
		
		var pV = {
			flightId : flightId,	
			excId : $this.data('excid'),
			falseAlarmState: falseAlarmState
		};

		$.ajax({
			dataType : "json",
			type: "POST",
			url : scriptUpdateException,
			data : pV,
		});
	});
	//=============================================================
	
	//=============================================================
	//user comment edit
	$("td#userComment").on("click", function(e){
		var $this = $(this),
			userComment = String(),
			userCommentInputId = "userCommentInput" + $this.data('excid');
		
		if($this.children().length < 1) {
			userComment = $this.text();
			$this.text("");
			
			var input = "<input id='"+userCommentInputId+"'style='width:100%' value='"+userComment+"'/>"
			$this.append(input);
			var curSelector = $("input#" + userCommentInputId);
			curSelector.focus();

			//user comment save
			curSelector.focusout(function(e){
				var $this = $(this),
					userComment = $this.val(),
					parent = $(this).parent();

				$this.remove();
				parent.text(userComment);	

				var pV = {
					flightId : flightId,	
					excId : parent.data('excid'),
					userComment: userComment
				};
	
				$.ajax({
					dataType : "json",
					type: "POST",
					url : scriptUpdateException,
					data : pV,
				});
			});
		}
	});
	//=============================================================
	
	//=============================================================
	//on chart by dblclick
	$("tr.ExceptionTableRow").on("dblclick", function(e) {
		var $this = $(this),
			excRefParam = $this.data("refparam"),
			curStartFrame = $this.data("startframe"),
			curEndFrame = $this.data("endframe"),
			defaultTpl = $("select#tplList").find("[data-defaulttpl='true']"),
			paramsToShow = Array(),
			selectedParams = String();

		
		if(defaultTpl.length > 0) {
			var tplParamsArr = $(defaultTpl[0]).data("params").split(", ");
			paramsToShow = tplParamsArr;

			if($.inArray(excRefParam, paramsToShow) < 0) {
				paramsToShow.push(excRefParam);
			}
			
			for(var i = 0; i < paramsToShow.length; i++) {
				selectedParams += paramsToShow[i] + "-";
			}
	
			selectedParams = selectedParams.substr(0, selectedParams.length-1);
						
			tpl.CreateTemplate(PARAMS_TPL_NAME, selectedParams).done(function(mess){
				excFlightId.val(flightId);
				excTpls.val(PARAMS_TPL_NAME);
				//some frame boundary
				excStartFrame.val(curStartFrame - 5);
				excEndFrame.val(curEndFrame + 5);
				
				formToChartByException.submit(); 	
			});
		} else {
			selectedParams = excRefParam;
			tpl.CreateTemplate(PARAMS_TPL_NAME, selectedParams).done(function(){
				excFlightId.val(flightId);
				excTpls.val(PARAMS_TPL_NAME);
				//some frame boundary
				excStartFrame.val(curStartFrame - 5);
				excEndFrame.val(curEndFrame + 5);
				
				formToChartByException.submit(); 	
			});
		}
	});
	
	
	//=============================================================
	//range slider event
	sliderContainer.slider({
      range: true,
      min: 0,
      max: stepsCount,
      values: [0, stepsCount],
      slide: function(event, ui) {
        amount.val(ui.values[0].toString().toHHMMSS() + " - " +
		ui.values[1].toString().toHHMMSS());
		
		startFrameFromTpl.attr("value", 
			sliderContainer.slider("values", 0));
		endFrameFromTpl.attr("value", 
			sliderContainer.slider("values", 1));
			
		/*if(sliderContainer.slider("values", 0) >= 
			sliderContainer.slider("values", 1)){
			sliderContainer.slider("values", 0) -= 1;
			sliderContainer.slider("values", 1) = 
				sliderContainer.slider("values", 0) + 1;
		}*/
      }
    });
    //=============================================================
		
	//=============================================================
	//set initial full slider range
	amount.val(
		sliderContainer.slider("values", 0).toString().toHHMMSS() +
		" - " + 
		sliderContainer.slider("values", 1).toString().toHHMMSS()
	);
	
	startFrameFromTpl.attr("value", (sliderContainer.slider("values", 0) / stepLength));
	endFrameFromTpl.attr("value", (sliderContainer.slider("values", 1) / stepLength));
	//=============================================================

	//=============================================================
	//refreshing comment textarea
	tplOption.on("click", function(){
		var $this = $(this);
		tpl.ShowTempltInfo($this);
	});
	//=============================================================
	
	//=============================================================
	//prepare list ap and bp for viewer 
	showTplOnChartBut.on("click", function(e){
		e.preventDefault();
		showTplForm.attr("action", CHART_FILE);
		var selectedTpls = $("select#tplList :selected");			
		
		if(selectedTpls.length > 1){
			var selectedParams = new String(),
				uniqueSelectedParams = [];
			selectedTpls.each(function() {
				selectedParams += $(this).data('params') + ", ";
			});
			selectedParams = selectedParams.replace(/ /g,'');
			selectedParams = selectedParams.substr(0, selectedParams.length-1).trim();
			selectedParams = selectedParams.split(',');
			
			$.each(selectedParams, function(i, el){
			    if($.inArray(el, uniqueSelectedParams) === -1) uniqueSelectedParams.push(el);
			});
			
			selectedParams = uniqueSelectedParams.join('-');
			
			startFrameFromTpl.attr("value", (sliderContainer.slider("values", 0) / stepLength));
			endFrameFromTpl.attr("value", (sliderContainer.slider("values", 1) / stepLength));

			tpl.CreateTemplate(PARAMS_TPL_NAME, selectedParams).done(function(){
				tplNames.attr("value", PARAMS_TPL_NAME);
				showTplForm.submit();
			});

		} else {
			selectedTpls = $(selectedTpls[0]).data('name');
			tplNames.attr("value", selectedTpls);
			startFrameFromTpl.attr("value", (sliderContainer.slider("values", 0) / stepLength));
			endFrameFromTpl.attr("value", (sliderContainer.slider("values", 1) / stepLength));
			showTplForm.submit();
		}

	});
	//=============================================================
	
	//=============================================================
	//prepare list ap and bp for viewer
	showTplOnTableBut.on("click", function(e){
		e.preventDefault();
		showTplForm.attr("action", TABLE_FILE);
		var selectedTpls = new String();
		$("select#tplList :selected").each(function() {
			selectedTpls += $(this).data('name') + ",";
		});
		selectedTpls = selectedTpls.substr(0, selectedTpls.length-1).trim();
		tplNames.attr("value", selectedTpls);
		startFrameFromTpl.attr("value", (sliderContainer.slider("values", 0) / stepLength));
		endFrameFromTpl.attr("value", (sliderContainer.slider("values", 1) / stepLength));
		showTplForm.submit(); 
	});
	//=============================================================
	
	//=============================================================
	//prepare list ap and bp for viewer
	showParamsOnChartBut.on("click", function(e){
		e.preventDefault();
		showParamsForm.attr("action", CHART_FILE);
		var selectedParams = new String();
		$("input.apCheckboxGroup:checked").each(function(ind, obj) {
			selectedParams += $(obj).attr("value") + "-";
		});
		$("input.bpCheckboxGroup:checked").each(function(ind, obj) {
			selectedParams += $(obj).attr("value") + "-";
		});
		
		selectedParams = selectedParams.substr(0, selectedParams.length-1);

		tpl.CreateTemplate(PARAMS_TPL_NAME, selectedParams).done(function(){
			showParamsForm.submit(); 	
		});	
	});
	//=============================================================
	
	//=============================================================
	//prepare list ap and bp for viewer and cache them
	showParamsOnTableBut.on("click", function(e){
		e.preventDefault();
		showParamsForm.attr("action", TABLE_FILE);
		var selectedParams = new String();
		apChToShow.each(function() {
			var $this = $(this);
			if($this.prop("checked")){
				selectedParams += $this.attr("value") + "-";
			}
		});
		bpChToShow.each(function() {
			var $this = $(this);
			if($this.prop("checked")){
				selectedParams += $this.attr("value") + "-";
			}
		});
		selectedParams = selectedParams.substr(0, selectedParams.length-1);
		tpl.CreateTemplate(PARAMS_TPL_NAME, selectedParams).done(function(){
			showParamsForm.submit(); 	
		});	
	});
	//=============================================================
		
	//=============================================================
	//get selected params array and create template
	createBut.on("click", function(e){
		var selectedParams = new String(),
			templateName = $("input#tplName").val().replace(/\s+/g, '');
		apChToCreate.each(function() {
			var $this = $(this);
			if($this.prop("checked")){
				selectedParams += $this.attr("value") + "-";
			}
		});
		bpChToCreate.each(function() {
			var $this = $(this);
			if($this.prop("checked")){
				selectedParams += $this.attr("value") + "-";
			}
		});
		selectedParams = selectedParams.substr(0, selectedParams.length-1);
		tpl.CreateTemplate(templateName, selectedParams).done(function(){
			dialogText.text(lang.tplHasBeenAdded);
			$("div#dialog").dialog("open");			
		});		
	});
	//=============================================================
	
	//=============================================================
	//delete template
	delBut.on("click", function(e){
		var tplName = $("select#tplListToEdit :selected").data("name");
		
		tpl.DeleteTemplate(tplName).done(function(){
			dialogText.text(lang.tplHasBeenDeleted);
			dialog.dialog("open");	
		});
	});	
	//=============================================================
	
	//=============================================================
	//show in ap and bp checkboxes templated params
	tplOptionToEdit.on("click", function(e){
		var tplParams = $("select#tplListToEdit :selected").data("params");
		tplParamsArr = tplParams.split(", ");
		
		//uncheck if smth checked
		apChToEdit.each(function() {
			$(this).prop("checked", false);
		});
		bpChToEdit.each(function() {
			$(this).prop("checked", false);
		});
			
		for(var i = 0; i < tplParamsArr.length; i++)
		{
			$("form#editTpl input[value="+tplParamsArr[i]+"]").
				prop('checked', true);
		}	
	});	
	//=============================================================
	
	//=============================================================
	//edit template
	editBut.on("click", function(e){
		var selectedParams = new String(),
			tplName = $("select#tplListToEdit :selected").data("name");
			
		apChToEdit.each(function() {
			var $this = $(this);
			if($this.prop("checked")){
				selectedParams += $this.attr("value") + "-";
			}
		});
		bpChToEdit.each(function() {
			var $this = $(this);
			if($this.prop("checked")){
				selectedParams += $this.attr("value") + "-";
			}
		});
		selectedParams = selectedParams.substr(0, selectedParams.length-1);
		
		tpl.CreateTemplate(tplName, selectedParams).done(function(){
			dialogText.text(lang.tplHasBeenEdit);
			$("div#dialog").dialog("open");			
		});		
	});
	
	//=============================================================
	//set default template
	defaultBut.on("click", function(e){
		var tplName = $("select#tplListToEdit :selected").data("name");

		tpl.SetDefaultTemplate(tplName).done(function(){
			console.log(lang);
			dialogText.text(lang.tplHasBeenSetAsDefault);
			dialog.dialog("open");	
		});		
	});	
	
	//=============================================================
	//print events list
	printColorBut.on("click", function(e){
		printAction.val(PRINT_COLOR_EVENTS);
		printEventsForm.submit();
	});	
	
	//=============================================================
	//print events list
	 printBlackBut.on("click", function(e){
		printAction.val(PRINT_BLACK_EVENTS);
		printEventsForm.submit();
	});	
		
});

String.prototype.toHHMMSS = function () {
    var sec_num = parseInt(this, 10); // don't forget the second parm
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0" + hours;}
    if (minutes < 10) {minutes = "0" + minutes;}
    if (seconds < 10) {seconds = "0" + seconds;}
    var time    = hours+':'+minutes+':'+seconds;
    return time;
};

Array.prototype.remove = function(from, to) {
  var rest = this.slice((to || from) + 1 || this.length);
  this.length = from < 0 ? this.length + from : from;
  return this.push.apply(this, rest);
};