function FlightProccessingStatus(langStr) {
    this.uploadings = new Object();
    this.updateUploadsStatusNeed = false;
    this.indicator = null;
    this.uploadTopButt = null;
    this.langStr = langStr;
}

FlightProccessingStatus.prototype.SupportUploadingStatus = function() {
    var self = this;
    self.indicator = $("#currentUploadingTopButt");
    self.indicator.on("mouseenter", function(e){
        self.ShowProgressInfo();
    });

    self.indicator.on("mouseleave", function(e){
        self.HideProgressInfo();
    });
}

FlightProccessingStatus.prototype.SetUpload = function(fileName, bruType, statusFile) {
    this.uploadings[fileName] = {
        'bruType': bruType,
        'uploadingFile': fileName,
        'statusFile': statusFile,
        'completeStatus': 0,
        'status': 0,
        'container': Object(),
        'progressBar': Object()
    }

    this.SetIndicatorVal();
};

FlightProccessingStatus.prototype.RemoveUpload = function(fileName) {
    this.uploadings[fileName]['completeStatus'] = 1;
    this.SetIndicatorVal();
};

FlightProccessingStatus.prototype.SetIndicatorVal = function() {
    var uploadingsCount = 0

    $.each(this.uploadings, function(index, el){
        if(el['completeStatus'] < 1){
            uploadingsCount++
        }
    });

    if(this.indicator){
        if(uploadingsCount > 0){
            this.indicator.text(uploadingsCount);
        } else {
            this.indicator.text("");
            location.reload();
        }
    }
};

FlightProccessingStatus.prototype.ShowProgressInfo = function() {
    if(this.AssocArraySize(this.uploadings) > 0){
        this.updateUploadsStatusNeed = true;
        this.UpdateStatus();
        this.BuildContainers();
        this.UpdateValues();
    }
};

FlightProccessingStatus.prototype.HideProgressInfo = function() {
    var self = this,
        UploadingProgressInfo = $("div.UploadingProgressInfo");
    this.updateUploadsStatusNeed = false;

    $.each(self.uploadings, function(index, el){
        if(el['completeStatus'] == 1){
            delete self.uploadings[el['uploadingFile']];
        }
    });

    $.each(UploadingProgressInfo, function(index, el){
        $(el).fadeOut().remove();
    });


};

FlightProccessingStatus.prototype.BuildContainers = function() {
    var self = this,
        indicator = self.indicator;

    if(self.AssocArraySize(self.uploadings) > 0){
        var count = 0,
            priviousEl = indicator;

        $.each(self.uploadings, function(index, el){
            var uploadingFile = el['uploadingFile'].replace(/^.*[\\\/]/, ''),
                 uploadingFileBruType = el['bruType'],
                 uploadingStatus = el['status'];
            if(count == 0) {
                indicator.after("<div id='uploadingProgressInfo" + count + "' " +
                        "class='UploadingProgressInfo'>" +
                        "<a style='font-weight:bold;'>" + uploadingFile + "</a></br>" +
                        "<a>" + uploadingFileBruType + "</a></br>" +
                        "<a class='ProgressStatus'>" + uploadingStatus + "</a></br>" +
                        "<div style='height:15px;'></div>" +
                        "</div>");
                var newProgressInfo = $("div#uploadingProgressInfo" + count),
                    progressBarContainer = newProgressInfo.children().last(),
                    pb = progressBarContainer.progressbar({
                      value: false
                    });

                newProgressInfo.position({
                      my: 'left top',
                      at: 'left bottom',
                      of: priviousEl
                    }).fadeIn();

                el['container'] = newProgressInfo;
                el['progressBar'] = pb;
                priviousEl = newProgressInfo;

            } else {
                indicator.after("<div id='uploadingProgressInfo" + count + "' " +
                        "class='UploadingProgressInfo'>" +
                        "<a style='font-weight:bold;'>" + uploadingFile + "</a></br>" +
                        "<a>" + uploadingFileBruType + "</a></br>" +
                        "<a class='ProgressStatus'>" + uploadingStatus + "</a></br>" +
                        "<div style='height:15px;'></div>" +
                        "</div>");
                var newProgressInfo = $("div#uploadingProgressInfo" + count),
                    progressBarContainer = newProgressInfo.children().last(),
                    pb = progressBarContainer.progressbar({
                      value: false
                    });

                newProgressInfo.position({
                      my: 'left top',
                      at: 'left bottom',
                      of: priviousEl
                    }).fadeIn();

                el['container'] = newProgressInfo;
                el['progressBar'] = pb;
                priviousEl = newProgressInfo;

            }
            count++;
        });
    }
};

FlightProccessingStatus.prototype.UpdateValues = function() {
    var self = this,
        indicator = self.indicator;

    if(this.updateUploadsStatusNeed) {
        if(self.AssocArraySize(self.uploadings) > 0) {
            $.each(self.uploadings, function(index, el){
                var container = el['container'],
                    progressBar = el['progressBar'],
                    status = el['status'].toString(),
                    complt = el['completeStatus'];

                if(complt > 0){
                    container.find(".ProgressStatus").text(self.langStr["flightUploadComplt"]);
                    progressBar.progressbar({
                          value: 100
                    });
                } else {
                    //if % than it is progress val
                    if(status.indexOf("%") > -1){
                        container.find(".ProgressStatus").text(status);
                        progressBar.progressbar({
                              value: parseInt(status.substring(0, status.length -1))
                        });
                    } else {
                        container.find(".ProgressStatus").text(status);
                        progressBar.progressbar({
                              value: false
                        });
                    }
                }
            });

            setTimeout(function(){ self.UpdateValues(); }, 1000);
        }
    }
};

FlightProccessingStatus.prototype.UpdateStatus = function() {
    var self = this;

    if(this.updateUploadsStatusNeed){
        if(self.AssocArraySize(self.uploadings) > 0){
            $.each(self.uploadings, function(index, el){
                var complt = el['completeStatus'],
                    progressFile = window.location.protocol + "//" + window.location.host +
                        "/back/fileUploader/files/proccessStatus/" + el['statusFile'];
                if(complt != 1){
                    $.ajax({
                        url: progressFile,
                        dataType: 'json',
                    }).done(function(serverAnswer) {
                        el['completeStatus']=0;
                        if((serverAnswer != null) && (serverAnswer.toString() != "")) {
                            if(el['status'] != serverAnswer){
                                el['status'] = serverAnswer;
                            }
                            setTimeout(function(){ self.UpdateStatus(); }, 3000);
                        } else {
                            setTimeout(function(){ self.UpdateStatus(); }, 3000);
                        }
                    }).fail(function(){
                        el['completeStatus']--;
                        if(el['completeStatus'] >= -7) {
                            setTimeout(function(){ self.UpdateStatus(); }, 3000);
                        } else {
                            self.RemoveUpload(el['uploadingFile']);
                        }
                    });
                }
            });
        }
    }
};

FlightProccessingStatus.prototype.AssocArraySize = function(obj) {
    // http://stackoverflow.com/a/6700/11236
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

module.exports = FlightProccessingStatus;
