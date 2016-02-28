$(document).ready(function(){
	
var tableContainer = $("#tableContainer"),
	tableholder = $("#tableHolder"),
	flightId = $("input#flightId").attr('value'),
	startFrame = $("input#startFrame").attr('value'),
	endFrame = $("input#endFrame").attr('value'),
	stepDivider = $("input#stepDivider").attr('value'),
	totalFramesCount = $("input#totalFramesCount").attr('value'),
	apParamsEnc = $("textarea#apParams").text(),
	bpParamsEnc = $("textarea#bpParams").text(),
	openTableForm = null,
	apArr = $.parseJSON(apParamsEnc),
	bpArr = $.parseJSON(bpParamsEnc);

tableContainer.css({
	"display": 'block',
	"overflow": 'visible',
	"padding": '5px',
	"border": 'none',
	"background": '#fff',
	"box-shadow": 'none',
});

tableholder.css({
	"display": 'block',
	"top": '5px',
	"overflow": 'visible'
});	

var Tbl = new Table(tableContainer, 
		tableholder, openTableForm, flightId, apArr, bpArr,
		startFrame, endFrame, totalFramesCount, stepDivider);

//=============================================================
/* Add a click handler to the rows - this could be used as a callback */
$("#tableHolder tbody").click(function(event) {
	$("table#tableHolder tr").each(function (){
		$(this).removeClass('row_selected');
	});
	$(event.target.parentNode).addClass('row_selected');
});

});







