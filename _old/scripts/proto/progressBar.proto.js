var ProgressBar = function(progressbar, totalFileSize, scriptAddrProgressFile, processedFileNum, totalFilesCount, waitAnswerText){
	this.progressContainer = $("div#draggableProgressContainer"),
	this.progressLabel = $("div#progressLabel");
	this.progressbar = progressbar;
	this.trigger = "completeUploading";
	
	this.processedFileNum = processedFileNum;
	this.totalFilesCount = totalFilesCount;
	this.totalFileSize = totalFileSize;
	this.scriptAddrProgressFile = scriptAddrProgressFile;
	this.waitAnswerText = waitAnswerText;
	
	this.framesProc = 0;
	this.resultDuplication = 0;
	this.previousFramesProc = 0;
	this.serverMess = new String();
	this.execProc = true;

	this.progressContainer.draggable();
};
//=============================================================

//=============================================================					
ProgressBar.prototype.ShowProgress = function() {
	this.marginTop = Math.max(0, $(window).scrollTop() +
			this.progressContainer.outerHeight()).toFixed(0) + "px";
			
    this.marginLeft = Math.max(0, ($(window).width() / 2) - 
		(this.progressContainer.outerWidth() / 2) +
		$(window).scrollLeft()).toFixed(0) + "px";
		
	this.progressContainer.css({
		'margin-left': this.marginLeft,
		'margin-top': this.marginTop,
	});
	
	this.progressContainer.slideDown();
	this.progressbar.progressbar({
	  value: false
	});
};
//=============================================================

//=============================================================					
ProgressBar.prototype.ShowProgressFromServer = function() {
	this.marginTop = Math.max(0, $(window).scrollTop() +
			this.progressContainer.outerHeight()).toFixed(0) + "px";
			
    this.marginLeft = Math.max(0, ($(window).width() / 2) - 
		(this.progressContainer.outerWidth() / 2) +
		$(window).scrollLeft()).toFixed(0) + "px";
		
	this.progressContainer.css({
		'margin-left': this.marginLeft,
		'margin-top': this.marginTop,
	});
	
	this.framesProc = 0;
	
	this.progressContainer.slideDown();
	this.progressbar.progressbar({
	  value: Number(0)
	});
	
	this.UpdateStatus(this);	
};
//=============================================================

//=============================================================		
ProgressBar.prototype.UpdateStatus = function(){
	var self = this;
	
	$.ajax({
		url: self.scriptAddrProgressFile,
		dataType: 'json',
	}).done(function(serverAnswer) {
		self.framesProc = serverAnswer;
		
		if((serverAnswer != null) && (serverAnswer.toString().indexOf(" ") < 0)) {
			if(self.framesProc != "done")
			{	
				var persentage = (self.framesProc / self.totalFileSize) * 100;
				var pbValue = Math.floor(persentage);
		
				if(self.previousFramesProc < self.framesProc){
					
					self.previousFramesProc = self.framesProc;
					
					self.progressbar.progressbar({
						value:pbValue
					});
					
					if(self.totalFilesCount > 1) {
						self.progressLabel.text(Number(persentage).toFixed(2) + "%" + " (" + (self.processedFileNum + 1) + "/" + self.totalFilesCount + ")");
					} else {
						self.progressLabel.text(Number(persentage).toFixed(2) + "%");
					}
				}
				setTimeout(function(){ self.UpdateStatus(self); }, 2000);
			} else {
				self.progressbar.progressbar({
					value:100
				});
				self.progressbar.trigger(self.trigger);
			}
		} else if((serverAnswer != null) && (serverAnswer.toString().indexOf(" ") > 0)) {
			var mess = serverAnswer.toString().split(" ");
			if(mess[0] == "done")
			{
				self.progressbar.progressbar({
					value:100
				});
				self.progressLabel.text(mess[0]);
				self.progressLabel.data("receivedinfo", mess[1]);
				
				self.progressbar.trigger(self.trigger);
			} else {
				setTimeout(function(){ self.UpdateStatus(self); }, 200);
			}
		} else {
			setTimeout(function(){ self.UpdateStatus(self); }, 200);
		}
	}).fail(function(){
		setTimeout(function(){ self.UpdateStatus(self); }, 200);
	});
};
//=============================================================

//=============================================================					
ProgressBar.prototype.ShowProgressLabelsReceiving = function() {
	this.marginTop = Math.max(0, $(window).scrollTop() +
			this.progressContainer.outerHeight()).toFixed(0) + "px";
			
    this.marginLeft = Math.max(0, ($(window).width() / 2) - 
		(this.progressContainer.outerWidth() / 2) +
		$(window).scrollLeft()).toFixed(0) + "px";
		
	this.progressContainer.css({
		'margin-left': this.marginLeft,
		'margin-top': this.marginTop,
	});
	
	this.progressbar.progressbar({
		  value: false
	});
	this.serverMess = "process";
	this.progressLabel.text(self.serverMess);
	
	this.progressContainer.slideDown();
	this.UpdateLabel(this);
};
//=============================================================

//=============================================================					
ProgressBar.prototype.UpdateLabel = function() {
	var self = this;	
	$.ajax({
		url: self.scriptAddrProgressFile,
		dataType: 'json',
	}).done(function(serverAnswer){
		self.serverMess = serverAnswer;
		if(self.serverMess != "done") {
			self.progressLabel.text(self.serverMess);
			setTimeout(function(){ self.UpdateLabel(self); }, 2000);
		} else {
			self.progressbar.trigger(self.trigger);
		}
	}).fail(function(){
		setTimeout(function(){ self.UpdateLabel(self); }, 200);
	});
};

//=============================================================

//=============================================================					
/*ProgressBar.prototype.ShowProgressWaitAnswer = function() {
	this.marginTop = Math.max(0, $(window).scrollTop() +
			this.progressContainer.outerHeight()).toFixed(0) + "px";
			
    this.marginLeft = Math.max(0, ($(window).width() / 2) - 
		(this.progressContainer.outerWidth() / 2) +
		$(window).scrollLeft()).toFixed(0) + "px";
		
	this.progressContainer.css({
		'margin-left': this.marginLeft,
		'margin-top': this.marginTop,
	});
	
	this.progressbar.progressbar({
		  value: false
	});
	this.serverMess = "wait";
	this.progressLabel.text("wait");
	
	this.progressContainer.slideDown();
	this.UpdateAnswer(this);
};*/
//=============================================================

//=============================================================					
/*ProgressBar.prototype.UpdateAnswer = function() {
	var self = this;
	$.getJSON(self.scriptAddrProgressFile, function(serverAnswer){
		if(serverAnswer){
			self.serverMess = serverAnswer;
		}
	}).always(function(){
		self.progressLabel.text("wait");
		
		if(self.serverMess != "done") {
			setTimeout(function(){ self.UpdateAnswer(self); }, 2000);
		} else {
			self.progressbar.trigger("completeEngineCompare");
		}
	});
};*/
//=============================================================

//=============================================================	
ProgressBar.prototype.CompltProc = function(){ 
	var self = this;
	this.progressbar.progressbar({
		value:100
	});
	
	this.progressLabel.text(self.waitAnswerText);			
};
//=============================================================

//=============================================================
/*ProgressBar.prototype.RedirOnMain = function(){
	location.href = location.protocol + '//' + location.host + '/index.php';
};*/
//=============================================================

//=============================================================					
ProgressBar.prototype.HideProgress = function() {
	this.progressContainer.slideUp();
};
//=============================================================
