function Table(tCont, tableholder, openTableForm, 
		flightId, apParams, bpParams,
		startFrame, endFrame, totalFramesCount, stepDivider) {
	this.cont = tCont;
	this.tHolder = tableholder;
	this.flightId = flightId;
	this.apArr = apParams;
	this.bpArr = bpParams;
	this.startRow = startFrame * stepDivider;
	this.endRow = endFrame * stepDivider;
	this.totalRowsCount = totalFramesCount * stepDivider;
	var framesCount = endFrame - startFrame,
		rowsCount = this.endRow - this.startRow;
	if(startFrame == 0 && totalFramesCount == framesCount)
	{
		rowsCount = 1000;
	}
	
	this.paramNames = this.apArr.concat(this.bpArr);
	this.paramStr = this.paramNames.join('-');
	this.openTableForm = openTableForm;
	this.scrollYSize = $(document).height() - this.cont.height() - 150;
	var self = this;	
	
	this.oTable = this.tHolder.dataTable( {
		"bInfo": true,
		"bSort": false,
		"bFilter": false,
		"bLengthChange": true,
        "bAutoWidth": true,
        "bProcessing": true,
		"bServerSide": true,
		"aLengthMenu": [[rowsCount, 2000, 5000, 10000, 20000, -1], 
		                [rowsCount, 2000, 5000, 10000, 20000, "Bce"]],
		"sPaginationType": "full_numbers",
        "sScrollY": self.scrollYSize,
        "sScrollX": "100%",
        "iDisplayStart": this.startRow,
		"iDisplayLength": rowsCount, 
        "sAjaxSource": location.protocol + '//' + location.host + "/asyncFigurePrint.php",
        "fnServerData": function ( sSource, aoData, fnCallback) {
        	self.cont.css({
        		'cursor': 'wait',
        	});
			$.ajax({
				"dataType": 'json', 
				"type": "POST", 
				"url": sSource, 
				"data": {
					"data": aoData, 
					"flightId": self.flightId,
					"paramsCode": self.paramStr,
				},
				"success": fnCallback
			}).done(function(){
				self.cont.css({
					'cursor': 'default',
				});					
        	});
		},
        "oLanguage": {
        	"sUrl": location.protocol + '//' + location.host + "/lang/RU.lang"
        },
	});
}

Table.prototype.ResizeTable = function(){
	this.oTable.css({'heigth': this.cont.height()});
};
//=====================================================

//=====================================================
//open table in new tab
Table.prototype.OpenTableInNewTab = function(){
	this.openTableForm.submit();
};