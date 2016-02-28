var ACTION_BRUTYPE_VIEW = 'view',
	ACTION_BRUTYPE_ADD = 'add',
	ACTION_BRUTYPE_EDIT = 'edit',
	ACTION_BRUTYPE_DELETE = 'delete',
	
	BRUTYPE_PARAM_LIST = 'apParamList',
	BRUTYPE_PARAM_CREATE = 'apParamCreate',
	BRUTYPE_PARAM_UPDATE = 'apParamUpdate',
	BRUTYPE_PARAM_DELETE = 'apParamDelete',
	
	BRUTYPE_GRADI_LIST = 'gradiList',
	BRUTYPE_GRADI_CREATE = 'gradiCreate',
	BRUTYPE_GRADI_UPDATE = 'gradiUpdate',
	BRUTYPE_GRADI_DELETE = 'gradiDelete',
	
	BRUTYPE_SRC_LIST = 'srcList',
	BRUTYPE_SRC_UPDATE = 'srcUpdate',
			
	SCRIPT_ADDR_BRUTYPE_OPERATION = location.protocol + '//' + location.host + "/asyncBruTypeOperation.php",
	
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
	
	$.extend(true, $.hik.jtable.prototype.options.messages, {
        serverCommunicationError: lang.serverCommunicationError,
        loadingMessage: lang.loadingMessage,
        noDataAvailable: lang.noDataAvailable,
        addNewRecord: lang.addNewRecord,
        editRecord: lang.editRecord,
        areYouSure: lang.areYouSure,
        deleteConfirmation: lang.deleteConfirmation,
        save: lang.save,
        saving: lang.saving,
        cancel: lang.cancel,
        deleteText: lang.deleteText,
        deleting: lang.deleting,                                                                                               
        error: lang.error,
        close: lang.close,
        cannotLoadOptionsFor: lang.cannotLoadOptionsFor,
        pagingInfo: lang.pagingInfo,
        canNotDeletedRecords: lang.canNotDeletedRecords,
        deleteProggress: lang.deleteProggress,
        pageSizeChangeLabel: lang.pageSizeChangeLabel,
        gotoPageLabel: lang.gotoPageLabel
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
	
	var action = $("input#action").val(),
		bruTypeId = $("input#bruTypeId").val();
	//============================================
	//APTABLE
	//============================================
		
	if(action == ACTION_BRUTYPE_ADD) {
		
	} else if (action == ACTION_BRUTYPE_VIEW) {
		PutNonEditableCycloTable();
	} else if (action == ACTION_BRUTYPE_EDIT) {
		PutEditableCycloTable();
	} else {
		console.log("Undefined action. " + action);
	}
	
	function PutEditableCycloTable() {	
		$('div#apParamsTableContainer').jtable({
	        title: lang.apTable,
	        paging: true,
	        pageSize: 50,
	        sorting: true,
	        defaultSorting: 'channel ASC',
	        columnResizable: true, //disable column resizing
	        openChildAsAccordion: true,
	        actions: {
	            listAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_PARAM_LIST + 
	            	"&bruTypeId=" + bruTypeId,
	            createAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_PARAM_CREATE + 
	        		"&bruTypeId=" + bruTypeId,
	            updateAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_PARAM_UPDATE + 
	        		"&bruTypeId=" + bruTypeId,
	            deleteAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_PARAM_DELETE + 
	        		"&bruTypeId=" + bruTypeId
	        },
	        fields: {
	            id: {
	                key: true,
	                create: false,
	                edit: false,
	                list: false
	            },
	            //CHILD TABLE DEFINITION FOR Gradi
	            Gradi: {
	                title: '',
	                width: '20px',
	                sorting: false,
	                edit: false,
	                create: false,
	                display: function (paramData) {
	                	var $img;
	                    //paramData.record.type == 1 - gradi param type
	                	if(paramData.record.type == 1) {
	                		var $img = $('<img src="/stylesheets/jTableThemes/content/gradi.png" title="' + lang.editGradiTable + '" />');
	                    //Open child table when user clicks the image
	                    $img.click(function () {
	                        $('div#apParamsTableContainer').jtable('openChildTable',
	                                $img.closest('tr'),
	                                {
	                                    title: lang.gradiTable + ' - ' + paramData.record.code,
	                                    actions: {
	                                        listAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_GRADI_LIST + 
	                                    		"&bruTypeId=" + bruTypeId + 
	                                    		"&paramId=" + paramData.record.id,
	                                    	deleteAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_GRADI_DELETE + 
	                                    		"&bruTypeId=" + bruTypeId + 
	                                    		"&paramId=" + paramData.record.id,
	                                    	updateAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_GRADI_UPDATE + 
	                                    		"&bruTypeId=" + bruTypeId + 
	                                    		"&paramId=" + paramData.record.id,
	                                    	createAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_GRADI_CREATE + 
	                                    		"&bruTypeId=" + bruTypeId + 
	                                    		"&paramId=" + paramData.record.id
	                                    },
	                                    fields: {
	                                        gradiId: {
	                                            key: true,
	                                            create: false,
	                                            edit: false,
	                                            list: false
	                                        },
	                                        gradiCode: {
	                                            title: lang.gradiCode,
	                                            width: '10%'
	                                        },
	                                        gradiPh: {
	                                            title: lang.gradiPh,
	                                            width: '90%'
	                                        }
	                                    }
	                                }, function (data) { //opened handler
	                                    data.childTable.jtable('load');
	                                });
	                    });
	                    //Return image to show on the person row
	                	} else {
	                		var $img = $('<img src="/stylesheets/jTableThemes/content/gradiDisable.png" />');
	                	}
	                    return $img;
	                }
	            },
	            //CHILD TABLE DEFINITION FOR Src
	            Src: {
	                title: '',
	                width: '20px',
	                sorting: false,
	                edit: false,
	                create: false,
	                display: function (paramData) {
	                	
	                	//paramData.record.type == 2 - calc param type
	                	if(paramData.record.type == 2) {
	                		var $img = $('<img src="/stylesheets/jTableThemes/content/src.png" title="' + lang.editSrc + '" />');
	                    //Open child table when user clicks the image
	                    $img.click(function () {
	                        $('div#apParamsTableContainer').jtable('openChildTable',
	                                $img.closest('tr'), //Parent row
	                                {
	                                title: lang.srcTable + ' - ' + paramData.record.code,
	                                actions: {
	                                    listAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_SRC_LIST + 
	                                		"&bruTypeId=" + bruTypeId + 
	                                		"&paramId=" + paramData.record.id,
	                                	updateAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_SRC_UPDATE + 
	                                		"&bruTypeId=" + bruTypeId + 
	                                		"&paramId=" + paramData.record.id,
	                                },
	                                fields: {
	                                    srcId: {
	                                        key: true,
	                                        create: false,
	                                        edit: false,
	                                        list: false
	                                    },
	                                    alg: {
	                                        title: lang.srcVal,
	                                        type: 'textarea',
	                                        width: '100%'
	                                    }
	                                }
	                            }, function (data) { //opened handler
	                                data.childTable.jtable('load');
	                            });
	                    });
	                	} else {
	                		var $img = $('<img src="/stylesheets/jTableThemes/content/srcDisable.png" />');
	                	}
	                    //Return image to show on the person row
	                    return $img;
	                }
	            },
	            code: {
	                title: lang.paramCode,
	                width: '9%'
	            },
	            name: {
	                title: lang.paramName,
	                type: 'textarea',
	                width: '9%'
	            },
	            dim: {
	                title: lang.dim,
	                width: '9%'
	            },
	            type: {
	                title: lang.paramType,
	                width: '9%',
	                options: {
	                   '1' : '1',
	                   '2' : '2',
	                   '3' : '3',
	                   '4' : '4',
	                   '5' : '5',
	                   '6' : '6',
	                   '7' : '7',
	                   '8' : '8',
	                   '9' : '9',
	                   '10' : '10',
	                   '21' : '21',
	                   '22' : '22' },
	            },
	            channel: {
	                title: lang.channels,
	                type: 'textarea',
	                width: '9%',
	            },
	            k: {
	                title: lang.koef,
	                width: '9%',
	            },
	            minus: {
	                title: lang.minus,
	                width: '9%'
	            },
	            mask: {
	                title: lang.mask,
	                width: '9%'
	            },
	            shift: {
	                title: lang.shift,
	                width: '9%'
	            },
	            prefix: {
	                title: lang.prefix,
	                width: '30px'
	            },
	            minValue: {
	                title: lang.minVal,
	                width: '30px'
	            },
	            maxValue: {
	                title: lang.maxVal,
	                width: '30px'
	            },
	        }
	    });
			
		$('div#apParamsTableContainer').jtable('load');
	}
	
	function PutNonEditableCycloTable() {	
		$('div#apParamsTableContainer').jtable({
	        title: lang.apTable,
	        paging: true,
	        pageSize: 50,
	        sorting: true,
	        defaultSorting: 'channel ASC',
	        columnResizable: true, //disable column resizing
	        openChildAsAccordion: true,
	        actions: {
	            listAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_PARAM_LIST + 
	            	"&bruTypeId=" + bruTypeId
	        },
	        fields: {
	            id: {
	                key: true,
	                create: false,
	                edit: false,
	                list: false
	            },
	            //CHILD TABLE DEFINITION FOR Gradi
	            Gradi: {
	                title: '',
	                width: '20px',
	                sorting: false,
	                edit: false,
	                create: false,
	                display: function (paramData) {
	                	var $img;
	                    //paramData.record.type == 1 - gradi param type
	                	if(paramData.record.type == 1) {
	                		var $img = $('<img src="/stylesheets/jTableThemes/content/gradi.png" title="' + lang.editGradiTable + '" />');
	                    //Open child table when user clicks the image
	                    $img.click(function () {
	                        $('div#apParamsTableContainer').jtable('openChildTable',
	                                $img.closest('tr'),
	                                {
	                                    title: lang.gradiTable + ' - ' + paramData.record.code,
	                                    actions: {
	                                        listAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_GRADI_LIST + 
	                                    		"&bruTypeId=" + bruTypeId + 
	                                    		"&paramId=" + paramData.record.id
	                                    },
	                                    fields: {
	                                        gradiId: {
	                                            key: true,
	                                            create: false,
	                                            edit: false,
	                                            list: false
	                                        },
	                                        gradiCode: {
	                                            title: lang.gradiCode,
	                                            width: '10%'
	                                        },
	                                        gradiPh: {
	                                            title: lang.gradiPh,
	                                            width: '90%'
	                                        }
	                                    }
	                                }, function (data) { //opened handler
	                                    data.childTable.jtable('load');
	                                });
	                    });
	                    //Return image to show on the person row
	                	} else {
	                		var $img = $('<img src="/stylesheets/jTableThemes/content/gradiDisable.png" />');
	                	}
	                    return $img;
	                }
	            },
	            //CHILD TABLE DEFINITION FOR Src
	            Src: {
	                title: '',
	                width: '20px',
	                sorting: false,
	                edit: false,
	                create: false,
	                display: function (paramData) {
	                	
	                	//paramData.record.type == 2 - calc param type
	                	if(paramData.record.type == 2) {
	                		var $img = $('<img src="/stylesheets/jTableThemes/content/src.png" title="' + lang.editSrc + '" />');
	                    //Open child table when user clicks the image
	                    $img.click(function () {
	                        $('div#apParamsTableContainer').jtable('openChildTable',
	                                $img.closest('tr'), //Parent row
	                                {
	                                title: lang.srcTable + ' - ' + paramData.record.code,
	                                actions: {
	                                    listAction: 'asyncApParamsBruTypeManage.php?action=' + BRUTYPE_SRC_LIST + 
	                                		"&bruTypeId=" + bruTypeId + 
	                                		"&paramId=" + paramData.record.id
	                                },
	                                fields: {
	                                    srcId: {
	                                        key: true,
	                                        create: false,
	                                        edit: false,
	                                        list: false
	                                    },
	                                    alg: {
	                                        title: lang.srcVal,
	                                        type: 'textarea',
	                                        width: '100%'
	                                    }
	                                }
	                            }, function (data) { //opened handler
	                                data.childTable.jtable('load');
	                            });
	                    });
	                	} else {
	                		var $img = $('<img src="/stylesheets/jTableThemes/content/srcDisable.png" />');
	                	}
	                    //Return image to show on the person row
	                    return $img;
	                }
	            },
	            code: {
	                title: lang.paramCode,
	                width: '9%'
	            },
	            name: {
	                title: lang.paramName,
	                type: 'textarea',
	                width: '9%'
	            },
	            dim: {
	                title: lang.dim,
	                width: '9%'
	            },
	            type: {
	                title: lang.paramType,
	                width: '9%',
	                options: {
	                   '1' : '1',
	                   '2' : '2',
	                   '3' : '3',
	                   '4' : '4',
	                   '5' : '5',
	                   '6' : '6',
	                   '7' : '7',
	                   '8' : '8',
	                   '9' : '9',
	                   '10' : '10',
	                   '21' : '21',
	                   '22' : '22' },
	            },
	            channel: {
	                title: lang.channels,
	                type: 'textarea',
	                width: '9%',
	            },
	            k: {
	                title: lang.koef,
	                width: '9%',
	            },
	            minus: {
	                title: lang.minus,
	                width: '9%'
	            },
	            mask: {
	                title: lang.mask,
	                width: '9%'
	            },
	            shift: {
	                title: lang.shift,
	                width: '9%'
	            },
	            prefix: {
	                title: lang.prefix,
	                width: '30px'
	            },
	            minValue: {
	                title: lang.minVal,
	                width: '30px'
	            },
	            maxValue: {
	                title: lang.maxVal,
	                width: '30px'
	            },
	        }
	    });
			
		$('div#apParamsTableContainer').jtable('load');
	}
//	 var textareaStyle = "width:100%;" +
//		"height:100%;" +
//		"box-sizing: border-box;" +      /* For IE and modern versions of Chrome */
//		"-moz-box-sizing: border-box;" +  /* For Firefox                          */
//		"-webkit-box-sizing: border-box;"; /* For Safari                           */
//	 
//	var t = $('table#apParamsTable').DataTable({
//				"bInfo": true,
//				"bSort": false,
//				"bLengthChange": true,
//		        "bAutoWidth": true,
//				"aLengthMenu": [[20, 50, 100], 
//				                [20, 50, 100]],
//				"sPaginationType": "full_numbers",
//		        "sScrollX": "100%",
//
//		        "oLanguage": {
//		        	"sUrl": location.protocol + '//' + location.host + "/lang/RU.lang"
//		        }
//			});
//	
//	var selectTypeStr = "<select id='typeSelector'>" + 
//		"<option>1</option>" + 
//		"<option>2</option>" + 
//		"<option selected='selected'>3</option>" + 
//		"<option>4</option>" + 
//		"<option>5</option>" + 
//		"<option>6</option>" + 
//		"<option>7</option>" + 
//		"<option>8</option>" + 
//		"<option>9</option>" + 
//		"<option>10</option>" + 
//		"<option>21</option>" + 
//		"<option>22</option>" + 
//		"</select>",
//		//selectType = $("select#typeSelector"),
//		aditionalRows = Array();
//	
//	aditionalRows[0] = ShowGradi();
//	//t.row($("tr#firstDataRow")).child(aditionalRows[0]).show();
//	
//	$('button#addApParamBut').on( 'click', function () {
//		var count = t.data().length;
//		
//        var added = t.row.add( [
//            "<div class='ui-icon ui-state-active ui-icon-minus'></div>",
//			"","","","","","","",selectTypeStr,"","","",""
//        ]);
//        
//        t.draw();
//        
//        console.log(added);
//        /*aditionalRows[0] = ShowGradi();
//        t.row($("tr#firstDataRow")).child(aditionalRows[0]).show();*/
//        
//        $("select#typeSelector").on("change", function(e){
//        	selectTypeChange(e);
//        });
//    });
//	
//	 $("select#typeSelector").on("change", function(e){
//     	selectTypeChange(e);
//     });
//		
//	function selectTypeChange(e) {
//		var el = $(e.target),
//			curContent = el.find(":selected").text(),
//			row = t.row(el.closest('tr'));
//    	
//    	if(curContent == "1") { 
//        	row.child(ShowGradi).show();
//        	//
//    	    $("td.gradiCell").on('click', function (e) {
//    	    	changeContainerForEditable(e.target)
//    		});
//    		
//    	} else if(curContent == "2") {
//    		
//    		row.child.hide();
//    		row.child(ShowSrc).show();
//    		
//    		 $("td.srcCell").on( 'click', function (e) {
//    			 changeContainerForEditable(e.target);
//			});
//    	} else {
//    		row.child.hide();
//    	}
//	}
//	
//	//ui-state-highlight
//	
//	$('table#apParamsTable tbody').on( 'click', 'td', function (e) {
//	    var rowNum = t.cell(this).index().row,
//	    	colNum = t.cell(this).index().columnVisible;
//
//	    //if first row just remove this row
//	    if(colNum == 0) {
//	    	t.row($(e.target).parents('tr'))
//		        .remove();
//		    t.draw();
//		/*} else if(colNum == 1) {
//        var el = $(e.target),
//        	tr = $(this).closest('tr'),
//        	row = t.row(tr);
//        
//        if(el.data("ena") == true) {
//	        if(el.data("shown") == true){
//	        	el.data("shown", false);
//	        	$(e.target).attr('class', 'ui-icon ui-state-active ui-icon-triangle-1-s');
//	        	row.child.hide();
//	        } else {
//	        	el.data("shown", true);
//	        	$(e.target).attr('class', 'ui-icon ui-state-active ui-icon-triangle-1-n');
//	        	row.child(0).show();
//	        }*/
//		//if 8 it is option? if underfined in is gradi or src
//	    } else if((colNum != 8) && (colNum != undefined)) { //not type and not subtable
//		    changeContainerForEditable(e.target)
//	    }
//	});
//		
//	/* Formatting function for row details - modify as you need */
//	function ShowSrc () {
//	    // `d` is the original data object for the row
//	    return '<table cellpadding="5" cellspacing="0" ' + 
//	    'width="95%" height="300px" border="1" style="margin-left:50px;">'+
//	        '<tr>'+
//	            '<td class="srcCell"></td>'+
//	        '</tr>'+
//	    '</table>';
//	}
//	
//	function ShowGradi () {
//	    // `d` is the original data object for the row
//	    return '<table cellpadding="5" cellspacing="0" ' + 
//	    	'width="400px" border="1" style="margin: 10px auto 10px">'+
//	        '<tr>'+
//	            '<td width="200px">' + lang.gradiCode + '</td>'+
//	            '<td width="200px">' + lang.gradiPh + '</td>'+
//	        '</tr>'+
//	        '<tr>'+
//	            '<td class="gradiCell">&nbsp;</td>'+
//	            '<td class="gradiCell">&nbsp;</td>'+
//	        '</tr>'+
//	    '</table>';
//	}
//	
//	function changeContainerForEditable(requester){
//		var curCell = $(requester),
//			curCellContent = curCell.val(),
//			previousCell = $("textarea#editingCell"),
//			previousCellContent = previousCell.val(),
//			previousPar = previousCell.parent();
//		
//		previousCell.remove();
//		previousPar.text(previousCellContent);
//
//		curCell.val("");
//		curCell.append("<textarea id='editingCell' style='" + textareaStyle + "'/>" + curCellContent + "</textarea>");
//	    
//	    $("textarea#editingCell").focus();
//	}
//	
//	function check changeContainerForEditable(requester){
//	//$("body > *").not("body > #elementtokeep").remove();
//	
//	
//	$("table#apParamsTable tbody").click(function(event) {
//		if($(event.target).prop("tagName") == "TD") {
//			$("table#apParamsTable tr.row_selected").each(function (){
//				$(this).removeClass('row_selected');
//			});
//			$(event.target.parentNode).addClass('row_selected');
//		}
//	});
	
	
});
