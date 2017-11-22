import uuidV4 from 'uuid/v4';

import 'flot-charts';
import 'flot-charts/jquery.flot.time';
import 'flot-charts/jquery.flot.selection';

import { I18n } from 'react-redux-i18n';

function FlightUploader(store)
{
    this.firstUploadedComplt = false;
    this.updateLegendTimeout = false;

    this.plotStack = Object();
    this.plotAxesStack = Object();
    this.plotDatasetStack = Object();
    this.plotSelectedFromRangeStack = Object();
    this.plotSelectedToRangeStack = Object();

    this.plotRequests = 0;
    this.plotRequestsClosed = 0;

    this.flightUploaderFactoryContainer = null;
    this.flightUploaderContent = null;
}

FlightUploader.prototype.FillFactoryContaider = function(
    factoryContainer, uploadingUid, fdrId, calibrationId
) {
    this.flightUploaderFactoryContainer = factoryContainer;
    this.flightUploaderFactoryContainer.append("<div id='flightUploaderContent' class='ContentFullWidth'></div>");
    this.flightUploaderContent = $("#flightUploaderContent");
    this.ResizeFlightUploader();
    this.GetFlightParams(0, uploadingUid, fdrId, calibrationId);
};

FlightUploader.prototype.ResizeFlightUploader = function(e) {
    var self = this;

    if (self.flightUploaderFactoryContainer != null){
        self.flightUploaderContent.css({
            'width': self.flightUploaderFactoryContainer.width() - 10,
            "height": $(window).height() - 35, //35 because padding and margin
        });
    }
}

FlightUploader.prototype.GetFlightParams = function(
        index,
        uploadingUid,
        fdrId,
        calibrationId = null
) {

    var self = this;
    let isAsync = true;

    if (self.flightUploaderContent != null) {
        //when file uploaded call fileProcessor to import it
        if((index == 0) || (self.firstUploadedComplt == false)) {
            isAsync = false;
        }

        $.ajax({
            type: "POST",
            data: {
                index: index,
                uploadingUid: uploadingUid,
                fdrId: fdrId,
                calibrationId: calibrationId
            },
            dataType: 'json',
            url: ENTRY_URL + "uploader/flightUploadingOptions",
            async: false
        }).fail(function(msg){
            console.log(msg);
        }).done(function(answ) {
            if(answ["status"] == "ok") {
                var flightUploadingProfile = answ["data"]
                self.flightUploaderContent.append(flightUploadingProfile);

                var parentContainer = $("div#fileFlightInfo" + index),
                    previewParamsRaw = parentContainer.data("previewparams"),
                    flightInfoColunmWidth = 450,
                    chartWidth = $(window).width() - flightInfoColunmWidth - 30,
                    previewParams = Array();

                if (previewParamsRaw.indexOf(";") > -1) {
                    previewParams = previewParamsRaw.split(";")
                } else {
                    previewParams[0] = previewParamsRaw;
                }

                self.PreviewChart(
                    parentContainer,
                    previewParams,
                    index,
                    uploadingUid,
                    fdrId,
                    chartWidth
                );

                self.SliceFlightButtSupport(parentContainer, previewParams);
                if (!self.firstUploadedComplt) {
                    self.firstUploadedComplt = true;
                }
            } else {
                console.log(answ["error"]);
            }
        });
    }
};

FlightUploader.prototype.GetSlicedFlightParams = function(
        index,
        file,
        uploadingUid,
        fdrId,
        parentIndex
) {

    var self = this,
        containerWidth = self.containerWidth,
        parentToAppentAfter = $("div#fileFlightInfo" + parentIndex);

    //when file uploaded call fileProcessor to import it
    $.ajax({
        type: "POST",
        data: {
            index: index,
            uploadingUid: uploadingUid,
            file: file,
            containerWidth: containerWidth,
            fdrId: fdrId
        },
        dataType: 'json',
        url: ENTRY_URL + "uploader/flightUploadingOptions",
        async: false
    }).fail(function(msg){
        console.log(msg);
    }).done(function(answ) {
        if(answ["status"] == "ok") {
            parentToAppentAfter.after(answ["data"]);

            var parentContainer = $("div#fileFlightInfo" + index),
                previewParamsRaw = parentContainer.data("previewparams"),
                flightInfoColunmWidth = containerWidth / 2.5,
                chartWidth = containerWidth - flightInfoColunmWidth - 30,
                previewParams = Array();

            if(previewParamsRaw.indexOf(";") > -1) {
                previewParams = previewParamsRaw.split(";")
            } else {
                previewParams[0] = previewParamsRaw;
            }

            self.PreviewChart(
                parentContainer,
                previewParams,
                index,
                uploadingUid,
                fdrId,
                chartWidth
            );

            self.SliceFlightButtSupport(parentContainer, previewParams);

        } else {
            console.log(answ["error"]);
        }
    });
};

//PREVIEW CHART
FlightUploader.prototype.PreviewChart = function (parent,
        previewParams,
        index,
        uploadingUid,
        fdrId,
        chartWidth){

    var self = this;

    self.ResizeFlightUploader();

    if((previewParams.length > 0) && (previewParams[0] != "")) {

        var gCont = parent.find("div#previewChartContainer" + index),
            placeholderSelector = "div#previewChartPlaceholder" + index,
            placeholder = $(placeholderSelector),
            loadingBox = $("div#loadingBox" + index);

        self.plotRequests++;

        //=============================================================

        //=============================================================
        //prepare placeholder and plot
        gCont.css({
            "width": chartWidth + 'px',
            "height": 300 + 'px',
            });
        placeholder.css({
            "width": (gCont.width() - 10) + 'px',
            "height": (gCont.height() - 10) + 'px',
            });

        loadingBox.position({
            my: "center",
            at: "center",
            of: gCont
        });
        loadingBox.fadeIn();

        //=============================================================

        //=============================================================
        //flot options
        var options    = {
            xaxis: {
                mode: "time",
                timezone: "utc",
            },
            yaxis:{
                ticks: 0,
                //tickLength: 1,
                position : "right",
                zoomRange: [0,0],
            },
            crosshair: {
                mode: "x",
            },
            grid: {
                aboveData: true,
                hoverable: true,
                clickable: true,
                tickColor: "rgba(255, 255, 255, 0)",
                borderWidth: 1,
                backgroundColor: "#fff",
                markingsLineWidth: 1,

            },
            legend: {
                position: "nw",
            },
            lines: {
                lineWidth: 1,
            },
            selection: {
                mode: "x"
            }
        };

        //=============================================================
        //flot data
        //=============================================================
        var plot = Object(),
            plotAxes = Object(),
            plotDataset = Object();

        $.ajax({
            type: "POST",
            data: {
                uploadingUid: uploadingUid,
                fdrId: fdrId,
            },
            dataType: 'json',
            url: ENTRY_URL + "uploader/flightUploaderPreview",
        }).done(function(apDataArray){
            $("div#loadingBox" + index);//.remove();
            var prmData = Array(),
                i = 0;
            for (var key in apDataArray)  {
                i++;
                var apDataFlotSeries = {
                    data: apDataArray[key],
                    label: key + " = 0.00",
                    yaxis: i,
                    shadowSize: 0,
                    lines: { lineWidth: 1, show: true }
                };
                prmData.push(apDataFlotSeries);
            }

            self.plotStack[index] = $.plot(placeholderSelector, prmData, options);
            self.plotAxesStack[index] = self.plotStack[index].getAxes();
            self.plotDatasetStack[index] = self.plotStack[index].getData();

            self.plotRequestsClosed++;

        }).fail(function(mess){
            console.log(mess);
        });

        var updateLegendTimeout = false,
            curPos = Array();

        $("div.PreviewChartPlaceholder").on('plothover', function (event, pos, item) {
            var legendParent = $(event.target),
                legendParentId = legendParent.attr("id"),
                curIndex = legendParent.data("index");

            //if chart already ploted
            if(self.plotDatasetStack[curIndex] != null){
                if (!self.updateLegendTimeout) {
                    self.updateLegendTimeout = true;
                    setTimeout(function() {

                        var values = self.GetValue(previewParams,
                                self.plotDatasetStack[curIndex],
                                pos.x);

                        self.UpdateLegend(previewParams,
                                legendParentId,
                                self.plotDatasetStack[curIndex],
                                self.plotAxesStack[curIndex],
                                pos,
                                values);
                    }, 200);
                }

                curPos = pos;
            }
        });

        //====================================================
        //flot selection
        //====================================================
        $("div.PreviewChartPlaceholder").on("plotselected", function (event, ranges) {
            var parent = $(event.target),
            curIndex = parent.data("index");

            self.plotSelectedFromRangeStack[curIndex] = ranges.xaxis.from.toFixed(0);
            self.plotSelectedToRangeStack[curIndex]  = ranges.xaxis.to.toFixed(0);
        });
    }
}

FlightUploader.prototype.UpdateLegend = function(previewParams,
        placeholderSelector, dataset, plotAxes, pos, valuesArr)
{
    this.updateLegendTimeout = false;
    //update each time legends because it can be lost after zoom or pan
    var legndLabls = $("#" + placeholderSelector + " .legendLabel");

    if (pos.x < plotAxes.xaxis.min || pos.x > plotAxes.xaxis.max ||
        pos.y < plotAxes.yaxis.min || pos.y > plotAxes.yaxis.max) {
        return;
    }
    //update legend only for ap
    for (var i = 0; i < previewParams.length; ++i) {
        var series = dataset[i],
            y = valuesArr[i],
            s = series.label.substring(0, series.label.indexOf('='));
        legndLabls.eq(i).text(s + " = " + Number(y).toFixed(2));
    }
    this.updateLegendTimeout = false;
};

//Get value by x coord by interpolating
FlightUploader.prototype.GetValue = function(previewParams, dataset, x) {
    var yArr = Array();

    for (var i = 0; i < previewParams.length; i++) {
        var series = dataset[i];

        // Find the nearest points, x-wise
        for (var j = 0; j < series.data.length; j++) {
            if (series.data[j][0] > x) {
                break;
            };
        }

        // Now Interpolate
        var y,
            posX,
            p1 = series.data[j - 1],
            p2 = series.data[j];

        if ((p1 == null) && (p2 != null)) {
            y = Number(p2[1]);
        } else if ((p1 != null) && (p2 == null)) {
            y = Number(p1[1]);
        } else if ((p1 != null) && (p2 != null)) {
            p1[0] = Number(p1[0]);
            p1[1] = Number(p1[1]);
            p2[0] = Number(p2[0]);
            p2[1] = Number(p2[1]);
            posX = Number(x);
            y = p1[1] + (p2[1] - p1[1]) *
                (posX - p1[0]) / (p2[0] - p1[0]);
        } else {
            y = 0;
        }

        yArr.push(Number(y).toFixed(2));
    }
    return yArr;
};


FlightUploader.prototype.SliceFlightButtSupport = function(parent, previewParams) {
    var self = this;

    if ((previewParams.length > 0) && (previewParams[0] != "")) {
        var appendedButt = parent.find("button.SliceFlightButt, button.SliceCyclicFlightButt");

        if(appendedButt.attr("role") == undefined){
            var button = appendedButt.button().first().css({
                'padding-top': '0px !important'
            });
        }

        appendedButt.click(function(e) {
            event.preventDefault();

            //if all charts ploted
            if(self.plotRequests == self.plotRequestsClosed){
                var el = $(this),
                    curIndex = el.data("index"),
                    fileName = el.data("file"),
                    fdrId = el.data("fdr-id"),
                    uploadingUid = el.data("uploading-uid"),
                    newIndex = $("div.PreviewChartPlaceholder").length,
                    action = "flightCutFile";

                if((self.plotSelectedFromRangeStack[curIndex] != undefined) &&
                        (self.plotSelectedToRangeStack[curIndex] != undefined)){

                    $("input#ignoreDueUploading" + curIndex).prop('checked', true);

                    if (el.hasClass('SliceFlightButt')){
                        action = "flightCutFile";
                    } else if(el.hasClass('SliceCyclicFlightButt')){
                        action = "flightCyclicSliceFile";
                    }

                    $.ajax({
                        type: "POST",
                        data: {
                            uploadingUid: uploadingUid,
                            newUid: uuidV4().substring(0, 18).replace(/-/g, ''),
                            fdrId: fdrId,
                            file: fileName,

                            startCopyTime: self.plotAxesStack[curIndex].xaxis.min,
                            endCopyTime: self.plotAxesStack[curIndex].xaxis.max,
                            startSliceTime: self.plotSelectedFromRangeStack[curIndex],
                            endSliceTime:  self.plotSelectedToRangeStack[curIndex]
                        },
                        dataType: 'json',
                        url: ENTRY_URL + 'uploader/'+action,
                        async: true
                    }).done(function(answ){
                        if(answ["status"] == 'ok') {
                            var newFileName = answ["data"];
                            let newUid = answ["newUid"];

                            self.GetSlicedFlightParams(
                                newIndex,
                                newFileName,
                                newUid,
                                fdrId,
                                curIndex
                            );
                        } else {
                            console.log(answ["error"]);
                        }
                    }).fail(function(mess){
                        console.log(mess);
                    });
                }
            }
        });
    }
};

FlightUploader.prototype.InitiateFlightProccessing = function(data) {
    var self = this,
        uploadingUid = data.uploadingUid;

    $.ajax({
        url: ENTRY_URL + 'uploader/flightProcces',
        type: "POST",
        data: data,
        dataType: 'json',
    }).done(function(answ){
        $(document).trigger("endProccessing", [uploadingUid, answ.item]);
    }).fail(function(mess){
        console.log(mess);
    });

    $(document).trigger("startProccessing", [uploadingUid]);
}


///
//Import
///
FlightUploader.prototype.Import = function(form, dfd) {
    var self = this;

    $.ajax({
        type: 'POST',
        data: form,
        dataType: 'json',
        processData: false,
        contentType: false,
        url: ENTRY_URL+'uploader/itemImport'
    }).done(function(answ){
        if (answ["status"] == 'ok') {
            dfd.resolve();
        } else {
            console.log(answ["error"]);
        }
    }).fail(function(mess){
        console.log(mess);
        dfd.reject();
    });
}

FlightUploader.prototype.uploadPreviewed = function() {
    let self = this;
    let flightsContainers = $("div.MainContainerContentRows");
    let count = flightsContainers.length;
    let index = 0;
    return new Promise((resolve, reject) => {
        $.each(flightsContainers, function(counter, el){
            var $el = $(el),
                fileName = $el.data("filename"),
                uploadingUid = $el.data("uploading-uid"),
                fdrId = $el.data("fdr-id"),
                calibrationId = $el.data("calibration-id"),
                index = $el.data("index"),
                ignoreDueUploading = $el.find("#ignoreDueUploading" + index),
                flightInfo = [],
                flightAditionalInfo = [],
                flightInfoCells = $el.find("input.FlightUploadingInputs"),
                flightAditionalInfoCells = $el.find("input.FlightUploadingInputsAditionalInfo");

            if((ignoreDueUploading.prop('checked') == false) &&
                    (!ignoreDueUploading.attr('checked'))) {
                $.each(flightInfoCells, function(j, subEl){
                    var $subEl = $(subEl);
                    if($subEl.attr('type') == 'checkbox'){
                        if($subEl.prop('checked')){
                            flightInfo.push($subEl.attr('id'));
                            flightInfo.push(1);
                        } else {
                            flightInfo.push($subEl.attr('id'));
                            flightInfo.push(0);
                        }
                    } else {
                        flightInfo.push($subEl.attr('id'));
                        flightInfo.push($subEl.val());
                    }
                });

                $.each(flightAditionalInfoCells, function(j, subEl){
                    var $subEl = $(subEl);
                    flightAditionalInfo.push($subEl.attr('id'));
                    flightAditionalInfo.push($subEl.val());
                });

                //if no aditional info set it to zero
                if (flightAditionalInfo.length == 0){
                    flightAditionalInfo = 0;
                }

                var check = $el.find("input#execProc").prop('checked');

                var data = {
                    fdrId: fdrId,
                    uploadingUid: uploadingUid,
                    calibrationId: calibrationId,
                    fileName: fileName,
                    flightInfo: flightInfo,
                    flightAditionalInfo: flightAditionalInfo,
                    check: check
                };

                self.InitiateFlightProccessing(data);
                index++;

                if (index === count) {
                    resolve();
                }
            }
        });
    });
}


//=============================================================
function s4() {
  return Math.floor((1 + Math.random()) * 0x10000)
             .toString(16)
             .substring(1);
};
//=============================================================

export default FlightUploader;
