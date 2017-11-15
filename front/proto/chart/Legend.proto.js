//=============================================================
//┏┓╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋┏┓
//┃┃╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋┃┃
//┃┃╋╋┏━━┳━━┳━━┳━┓┏━┛┃
//┃┃╋┏┫┃━┫┏┓┃┃━┫┏┓┫┏┓┃
//┃┗━┛┃┃━┫┗┛┃┃━┫┃┃┃┗┛┃
//┗━━━┻━━┻━┓┣━━┻┛┗┻━━┛
//╋╋╋╋╋╋╋┏━┛┃
//╋╋╋╋╋╋╋┗━━┛
//=============================================================

function Legend(flightId, legendContainer, apParams, bpParams, associativeParamsArr,
    plotXaxis, plotYaxes, plotDataset, placeholder, contentChartContainer){

    this.flightId = flightId;
    this.dataset = plotDataset;
    this.ph = placeholder;
    this.ccCont = contentChartContainer;
    this.xax = plotXaxis;
    this.yaxs = plotYaxes;
    this.pos = new Object();
    this.apArr = apParams;
    this.bpArr = bpParams;
    this.apCount = 0;
    this.bpCount = 0;
    this.paramCount = 0;
    if(this.apArr != null){
        this.apCount = this.apArr.length;
        this.paramCount += this.apCount;
    }

    if(this.bpArr != null){
        this.bpCount = this.bpArr.length;
        this.paramCount += this.bpCount;
    }
    this.associativeParamsArr = associativeParamsArr;
    this.legendTitlesArr = new Array();
    this.legndCont = legendContainer;
    this.legendTitlesNotReceived = true;
    this.legendTitlesNotSet = true;
    this.updateLegendTimeout = null;
    this.lastMovedPosX = null;
    this.verticalTextInput = false;

    this.paramInfo = new Array();
    this.dfr = $.Deferred();

    this.visirTimeBox = $('<div></div>')
        .attr('id', 'visirTimeBox')
        .addClass('VisirTimeBox');

    this.ccCont.append(this.visirTimeBox);

    $("<div></div>")
    .attr("id","leadParamValBox")
    .addClass("LeadParamValBox")
    .appendTo($('body'));
    this.leadParamValBox = $("#leadParamValBox");

    this.displayNeed = false;
    this.vizirBarContainer = Object();
    this.vizirFreezePos = 0;
    this.crosshairLocked = false;
    this.showSeriesLabelsNeed = false;
    this.seriesLabelsValues = new Array();
    this.seriesLabelsTime = 0;
    this.vLineColor = '#A9A9A9';

    this.barContainersArr = new Array();
    this.barMainContainersArr = new Array();
    this.linesContainersArr = new Array();
    this.textsContainersArr = [];
    this.horizontsContainersArr = new Array();
    this.horizontsValueContainersArr = new Array();
    this.seriesNamContainersArr = new Array();
    this.seriesLeftLabelsContainersArr = new Array();
}

Legend.prototype.UpdateLegend = function(posx, valuesArr,
    binariesArr)
{
    //update each time legends because it can be lost after zoom or pan
    this.updateLegendTimeout = null;
    var legndLabls = this.legndCont.find(".legendLabel");
    let y = 0;
    let s = 0;

    if (posx < this.xax.min || posx > this.xax.max) {
        return;
    }
    //update legend only for ap
    for (var i = 0; i < this.apCount; ++i) {
        var series = this.dataset[i];
        y = valuesArr[i];
        s = series.label.substring(0, series.label.indexOf('='));
        legndLabls.eq(i).text(s + " = " + Number(y).toFixed(2));
    }

    //update legend bp
    var j = 0;
    for (var i = this.apCount; i < this.paramCount; ++i) {
        var series = this.dataset[i];
        if(binariesArr[j] == true){
            legndLabls.eq(i).css({
                "color" : "red",
            });
            s = series.label.substring(0, series.label.indexOf('='));
            legndLabls.eq(i).text(s + " = " + "T");
        } else {
            legndLabls.eq(i).css({
                "color" : "#525552",
            });
            s = series.label.substring(0, series.label.indexOf('='));
            legndLabls.eq(i).text(s + " = " + "F");
        }
        j++;
    }

    //if legend table was rebuils afret zoom or resize append titles
    if(this.legendTitlesNotSet) {
        for (var i = 0; i < (this.paramCount); ++i) {
            var $this = legndLabls.eq(i);
            $this.attr("title", this.paramInfo[i]);
        }
        this.legendTitlesNotSet = false;
    };
};
//=============================================================

//=============================================================
//highlight overed series
Legend.prototype.HighlightLegend = function(seriesIndex)
{
    let legndLabls = this.legndCont.find(".legendLabel");
    if(seriesIndex >= 0){
        for(var ii = 0; ii < legndLabls.length; ii++) {
            legndLabls.eq(ii).css({
                "background-color" : "transparent",
            });
        }
        legndLabls.eq(seriesIndex).css({
            "background-color" : "rgb(10,10,220, 0.15)",
        });
    } else {
        for(var ii = 0; ii < legndLabls.length; ii++) {
            legndLabls.eq(ii).css({
                "background-color" : "transparent",
            });
        }
    }

};
//=============================================================

//=============================================================
//updating legends by vizir and appendint text attr when first call for tool tips
Legend.prototype.ReceiveLegend = function(){
    var legndLabls = this.legndCont.find(".legendLabel"),
        self = this;
    if (this.legendTitlesNotReceived) {
        var paramCodes = self.apArr.concat(self.bpArr);

        $.ajax({
            data: {
                flightId: self.flightId,
                paramCodes: paramCodes
            },
            type: 'POST',
            url: ENTRY_URL + 'chart/getLegend',
            dataType: 'json',
            success: function(inData){
                self.dfr.resolve;
            },
        }).done(function(inData){
            for (var i = 0; i < self.paramCount; i++) {
                var $this = legndLabls.eq(i);
                //console.log(inData[i] + " " + i);
                self.paramInfo.push(inData[i]);
                $this.attr("title", inData[i]);
                self.legendTitlesArr.push(inData[i]);
            };
        }).fail(function(answ){
            console.log(answ);
        });

         this.legendTitlesNotReceived = false;
    };
};
//=============================================================

//=============================================================
//show visir time in div above it
Legend.prototype.ShowVisirTime = function(){
    if (this.pos.x > this.xax.min && this.pos.x < this.xax.max) {
        this.visirTimeBox.text(this.toHHMMSS(this.pos.x));
        this.visirTimeBox.css({'left': this.pos.pageX});
    }
};
//=============================================================

//=============================================================
//show lead param val in div above point
Legend.prototype.ShowLeadParamVal = function(val, label){
    if (this.pos.x > this.xax.min && this.pos.x < this.xax.max) {
        this.leadParamValBox.text(label + " = " + val);
        this.leadParamValBox.css({
            'left': this.pos.pageX + 10,
            'top': this.pos.pageY - 25,
            });
    }
};
//=============================================================

//=============================================================
//
Legend.prototype.AppendSectionBar = function(manualPosX, hasText){
    var self = this;
    var posx = this.pos.x;
    if(manualPosX) {
        posx = manualPosX;
    }
    this.lastMovedPosX = posx;
    if (posx > this.xax.min && posx < this.xax.max) {
        var startId = this.barContainersArr.length,
            legndLabls = this.legndCont.find(".legendLabel"),
            labelText = legndLabls.eq(0).text(),
            value = labelText.substring(labelText.indexOf('=') + 1, labelText.length);
        if(value != null){
            for (var i = 0; i < this.apCount; i++) {
                var labelText = legndLabls.eq(i).text(),
                    id = "barLabel" + (this.barContainersArr.length + 1),
                    refParam = this.apArr[i],
                    time = posx,
                    value = labelText.substring(labelText.indexOf('=') + 2, labelText.length),
                    yAxNum = i,
                    color = this.associativeParamsArr[refParam][1];

                var s = this.CreateBarContainer(id, refParam, time, value, value, yAxNum, color);
                this.barContainersArr.push(s);
            }
        }

        var j = 0;
        for (var i = this.apCount; i < this.paramCount; i++) {
            var labelText = legndLabls.eq(i).text(),
                id = 'barLabel' + (this.barContainersArr.length + 1),
                refParam = this.bpArr[j],
                time = posx,
                value = 1,
                content = labelText.substring(labelText.indexOf('=') + 2, labelText.length),
                yAxNum = i,
                color = this.associativeParamsArr[refParam][1];

            if(content == "T") {
                var s = this.CreateBarContainer(id, refParam, time, value, content, yAxNum, color);
                this.barContainersArr.push(s);
            }
            j++;
        }

        var line = this.CreateLineContainer(posx);
        this.linesContainersArr.push(line);

        var text = this.CreateTextContainer(posx, hasText);
        this.textsContainersArr.push(text);

        var barMainContainer = this.CreateBarMainContainer(posx,
            self.toHHMMSS(posx),
            startId,
            this.barContainersArr.length,
            this.linesContainersArr.length,
            this.textsContainersArr.length
        );
        this.barMainContainersArr.push(barMainContainer);

        return {
            line: line,
            text: text,
            barMainContainer: barMainContainer
        };
    } else {
        return null;
    }
};
//=============================================================

//=============================================================
//
Legend.prototype.MoveLastVertical = function(posx, values, binaries){
    var mainBar = this.barMainContainersArr[this.barMainContainersArr.length - 1];
    var startId = mainBar.data('startid');
    var endId = mainBar.data('endid');
    var lineId = mainBar.data('lineid');
    var textid = mainBar.data('textid');

    this.linesContainersArr[lineId - 1].data('time', posx);
    this.textsContainersArr[textid - 1].data('time', posx);

    var jj = 0;
    for(var i = startId; i < endId; i++)
    {
        if(jj < values.length) {
            var bar = this.barContainersArr[i];
            bar.data('value', values[jj]);
            bar.data('time', posx);
            bar.html(values[jj]);
        } else if((jj >= values.length) &&
                ((jj - values.length) < binaries.length)) {
            var bar = this.barContainersArr[i];
            bar.data('value', binaries[jj - values.length]);
            bar.data('time', posx);
            bar.html(values[jj]);

            if(binaries[jj - values.length] == 1) {
                bar.fadeIn(200);
            } else {
                bar.hide();
            }
        }

        jj++;
    };
    mainBar.data('time', posx);
    mainBar.html(this.toHHMMSS(posx));
    this.lastMovedPosX = posx;

    this.UpdateBarContainersPos();
};
//=============================================================

//=============================================================
//
Legend.prototype.CreateBarMainContainer = function(time, content, startId, endId, lineId, textId) {
     var self = this;
     var barMainLabel = $('<div></div>', {
        'id': 'barMainLabel' + (self.barMainContainersArr.length + 1),
        'class': 'BarMainLabel',
        'data-time': time,
        'data-startid': startId,
        'data-endid': endId,
        'data-lineid': lineId,
        'data-textid': textId,
        html: content
    })
    .appendTo(self.ccCont);

    //delete bar on mainLable click
    barMainLabel.dblclick(function(){
        self.RemoveSectionBar(barMainLabel);
    });

    return barMainLabel;

};
//=============================================================

//=============================================================
//
Legend.prototype.CreateBarContainer = function(id, refParam, time, value, content, yAxNum, color) {
    var self = this;
    var barLabel = $('<div></div>', {
        'id': id,
        'class': 'BarLabel',
        'data-refParam': refParam,
        'data-time': time,
        'data-yAx': yAxNum,
        'data-value': value,
        'data-hidden': 'false',
        'data-draggeddeltax': '0',
        'data-draggeddeltay': '0',
        html: content
        }).css({
            "color": "#" + color
        })
    .appendTo(self.ccCont);

    barLabel.dblclick(function(event){
        barLabel.css('display', 'none');
        barLabel.data('hidden', 'true');
    });

    var isDragging = false;
    var startCoordinate = null;
    var offsetCoordinate = { x: 0, y: 0 };
    var prevCoordinate = { x: 0, y: 0 };
    var deltaX = null;
    var deltaY = null;
    barLabel.mousedown(function(e) {
        isDragging = true;
        offsetCoordinate = {
                x: 0,
                 y: 0
        };
        prevCoordinate = {
                x: e.pageX,
                y: e.pageY
        };
    })
    .mouseup(function(e) {
        if(isDragging) {
            isDragging = false;

            var prevDraggedDeltaX = parseInt(barLabel.data('draggeddeltax'));
            var prevDraggedDeltaY = parseInt(barLabel.data('draggeddeltay'));

            barLabel.data('draggeddeltax', prevDraggedDeltaX+offsetCoordinate.x);
            barLabel.data('draggeddeltay', prevDraggedDeltaY+offsetCoordinate.y);
        }
    })
    .mouseleave(function(e) {
        if(isDragging) {
            isDragging = false;

            var prevDraggedDeltaX = parseInt(barLabel.data('draggeddeltax'));
            var prevDraggedDeltaY = parseInt(barLabel.data('draggeddeltay'));

            barLabel.data('draggeddeltax', prevDraggedDeltaX+offsetCoordinate.x);
            barLabel.data('draggeddeltay', prevDraggedDeltaY+offsetCoordinate.y);
        }
    })
    .mousemove(function(e) {
        if(isDragging) {
            deltaX = prevCoordinate.x - e.pageX;
            deltaY = prevCoordinate.y - e.pageY;

            offsetCoordinate.x += deltaX;
            offsetCoordinate.y += deltaY;

            barLabel.css({
               left: barLabel.position().left - deltaX,
               top: barLabel.position().top - deltaY
            });

            prevCoordinate = {
                    x: e.pageX,
                    y: e.pageY
            };
        }
    });

    return barLabel;
};
//=============================================================

//=============================================================
//
Legend.prototype.CreateLineContainer = function(time) {
    var self = this;
    return $('<svg></svg>', {
        id: 'svgLines' + (self.linesContainersArr.length + 1),
        'data-time': time })
    .css({
        "top": '38px',
        "width": '1px',
        "height": self.ph.height() - 30,
        "position": 'absolute',
        "background-color": self.vLineColor})
    .appendTo(self.ccCont);
};

//=============================================================

//=============================================================
//
Legend.prototype.CreateTextContainer = function(time, hasText) {
    var self = this;
    if(!hasText) {
        var text = $('<div></div>', {
                'id': 'text' + (self.textsContainersArr.length + 1),
                'data-time': time })
            .css({
                "display": 'none'
            });
        return text;
    }

    var text = $('<div></div>', {
        'id': 'text' + (self.textsContainersArr.length + 1),
        'class': 'vertical-text-container',
        'data-time': time })
    .css({ "height": self.ph.height() - 30 })
    .append($("<input/>")
        .addClass('verticalText')
        .css({
            "text-align": "center",
            "width": '24px',
            "height": self.ph.height() - 40
        })
    )
    .appendTo(self.ccCont);

    self.verticalTextInput = true;
    $(".verticalText")
        .focus()
        .on('click focusout keypress', function(event) {
             if((event.which === 13) ||
                     (event.type === "click") ||
                     (event.type === "focusout")){
                var target = $(this).eq(0);
                var val = target.val();
                target.parent().html(val);
                self.verticalTextInput = false;
             }
        });

     return text;
};
//=============================================================

//=============================================================
//
Legend.prototype.RemoveSectionBar = function(mainBar) {
    var startId = mainBar.data('startid');
    var endId = mainBar.data('endid');
    var lineId = mainBar.data('lineid');
    var textid = mainBar.data('textid');

    this.linesContainersArr[lineId - 1].remove();
    this.textsContainersArr[textid - 1].remove();
    for(var i = startId; i < endId; i++)
    {
        this.barContainersArr[i].remove();
    };
    return mainBar.remove();
};
//=============================================================

//=============================================================
//updating bars on plotpan or plotzoom
Legend.prototype.UpdateBarContainersPos = function(){
    var that = this;
    var xMin = this.xax.min.toFixed(0);
    var xMax = this.xax.max.toFixed(0);

    for(var i = 0; i < this.barContainersArr.length; i++){
        var barCont = this.barContainersArr[i];
        var barTime = barCont.data('time');
        var barValue = barCont.data('value');
        var yAxNum = barCont.data('yax');
        var yAxis = this.yaxs[yAxNum];
        var yMin = this.yaxs[yAxNum].min;
        var yMax = this.yaxs[yAxNum].max;
        var excCoordX = this.xax.p2c(barTime);
        var excCoordY = yAxis.p2c(barValue);

        var deltaX = parseInt(barCont.data('draggeddeltax'));
        var deltaY = parseInt(barCont.data('draggeddeltay'));
        var hidden = barCont.data('hidden');

        if((hidden !== 'true') && (barValue != 'false')) {
            if(((barTime > xMin) && (barTime < xMax)) &&
               ((barValue > yMin) && (barValue < yMax))){
                barCont.css({
                    top: excCoordY - deltaY + 20,
                    left: excCoordX - deltaX + 5,
                })
                .fadeIn(200);
            } else {
                barCont.fadeOut(200);
            };
        }
    };

    var comments = $('.horizontal-text-container');
    $.each(comments, function( key, value ) {
        var $el = $(value);
        var barTime = $el.data('time');
        var barValue = $el.data('val');
        var yAxis = that.yaxs[0];
        var yMin = that.yaxs[0].min;
        var yMax = that.yaxs[0].max;

        var excCoordX = that.xax.p2c(barTime);
        var excCoordY = yAxis.p2c(barValue);

        if(((barTime > xMin) && (barTime < xMax)) &&
           ((barValue > yMin) && (barValue < yMax))){
            $el.css({
                top: excCoordY + 25,
                left: excCoordX,
            })
            $el.fadeIn(200);
        } else {
            $el.fadeOut(200);
        };
    });

    for(var i = 0; i < this.barMainContainersArr.length; i++){
        var barCont = this.barMainContainersArr[i];
        var barTime = barCont.data('time');
        var excCoordX = this.xax.p2c(barTime);

        if((barTime > xMin) && (barTime < xMax)){
            barCont.css({
                left: excCoordX + 5 } ).
            fadeIn(200);
        } else {
            barCont.fadeOut(200);
        };
    };

    for(var i = 0; i < this.textsContainersArr.length; i++){
        var barCont = this.textsContainersArr[i];
        var barTime = barCont.data('time');
        var excCoordX = this.xax.p2c(barTime);

        if((barTime > xMin) && (barTime < xMax)){
            barCont.css({
                "left": excCoordX + 5,
                "height": this.ph.height() - 30
            })
            .fadeIn(200);
        } else {
            barCont.fadeOut(200);
        };
    };

    for(var i = 0; i < this.linesContainersArr.length; i++){
        var barCont = this.linesContainersArr[i];
        var barTime = barCont.data('time');
        var excCoordX = this.xax.p2c(barTime);

        if((barTime > xMin) && (barTime < xMax)){
            barCont.css({
                "left": excCoordX + 6,
                "height": this.ph.height() - 30
            })
            .fadeIn(200);
        } else {
            barCont.fadeOut(200);
        };
    };

    for(var i = 0; i < this.horizontsContainersArr.length; i++){
        var phPos = this.ph.offset();
        var horLine = this.horizontsContainersArr[i];
        var horLineVal = horLine.data('value');
        var yAxNum = horLine.data('yax');
        var yMin = this.yaxs[yAxNum].min;
        var yMax = this.yaxs[yAxNum].max;
        var yAxis = this.yaxs[yAxNum];
        var excCoordY = yAxis.p2c(horLineVal) + phPos.top;

        if((horLineVal > yMin) && (horLineVal < yMax)){
            horLine.css({
                top: excCoordY + 7//just missing pix for corect disp
            }).
            fadeIn(200);
        } else {
            horLine.fadeOut(200);
        };
    };

    for(var i = 0; i < this.horizontsValueContainersArr.length; i++){
        var phPos = this.ph.offset();
        var horValueCont = this.horizontsValueContainersArr[i];
        var horValue = horValueCont.html();
        var yAxNum = horValueCont.data('yax');
        var yMin = this.yaxs[yAxNum].min;
        var yMax = this.yaxs[yAxNum].max;
        var yAxis = this.yaxs[yAxNum];
        var excCoordY = yAxis.p2c(horValue) + phPos.top;

        if((horValue > yMin) && (horValue < yMax)){
            horValueCont.css({
                top: excCoordY + 7//just missing pix for corect disp
            }).
            fadeIn(200);
        } else {
            horValueCont.fadeOut(200);
        };
    };

};
//=============================================================

//=============================================================
//resize lines to use method on graph container resizing
Legend.prototype.ResizeLines = function(){
    var self = this;
    for(var i = 0; i < self.linesContainersArr.length; i++)
    {
        var barCont = self.linesContainersArr[i];
        barCont.css("height", self.ph.height() - 30);
    }
};

//=============================================================

//=============================================================
//
Legend.prototype.CreateHorizont = function(yAxNum) {
    var self = this,
        e = self.CreateHorizontContainer(yAxNum),
        h = self.CreateHorizontValueContainer(yAxNum);
    self.horizontsContainersArr.push(e);
    self.horizontsValueContainersArr.push(h);

    //delete bar on mainLable click
    h.dblclick(function(){
        self.RemoveHorizont(h);
    });
};
//=============================================================

//=============================================================
//
Legend.prototype.CreateHorizontContainer = function(yAxNum) {
    var self = this;
    return $('<svg></svg>', {
        id: 'svgHorLine' + (self.horizontsContainersArr.length + 1),
        "data-yAx": yAxNum,
        "data-value": self.pos["y"+(yAxNum + 1)]})
        .css({
            "top": self.pos.pageY,
            "left": '14px',
            "width": self.ph.width() + self.ph.offset().left - 28,//left is negative num. 28 just missing pix num
            "height": '1px',
            "position": 'absolute',
            "background-color": 'darkgrey'})
        .appendTo(self.ccCont);
};
//=============================================================

//=============================================================
//
Legend.prototype.CreateHorizontValueContainer = function(yAxNum) {
    var self = this;
    return $('<div></div>', {
        id: 'horValue' + (self.horizontsValueContainersArr + 1),
        class : 'horValueLabel',
        "data-yAx": yAxNum,
        "data-horId": self.horizontsContainersArr.length,
        html: Number(self.pos["y"+(yAxNum+1)]).toFixed(2) })
        .css({
            "position": 'absolute',
            "display": 'block',
            "border": '1px solid #999',
            "padding": '2px',
            "top": self.pos.pageY,
            "left": '1px',
            "border-radius": '3px',
            "color": 'black',
            "background-color" : 'white',
            "font-size": '12px',
            "opacity": '0.6' })
        .appendTo(self.ccCont);
};
//=============================================================

//=============================================================
//
Legend.prototype.SuportHorizontAfterCreation = function() {
    var self = this,
    horLine = self.horizontsContainersArr[self.horizontsContainersArr.length - 1],
    horValue = self.horizontsValueContainersArr[self.horizontsValueContainersArr.length - 1],
    yAxNum = horLine.data('yax'),
    value = self.pos["y"+(yAxNum + 1)];
    horLine.attr({
        "data-value": value
    }).css({
        top: self.pos.pageY
    });

    horValue.html(Number(value).toFixed(2))
    .css({
        top: self.pos.pageY
    });
};
//=============================================================

//=============================================================
//
Legend.prototype.RemoveHorizont = function(horizont) {
    var horId = horizont.data('horid');
    this.horizontsContainersArr[horId].remove();
    return horizont.remove();
};
//=============================================================

//=============================================================
//
Legend.prototype.ShowSeriesNames = function() {
    var displayNamesState = this.displayNeed ? 'block' : 'none';
    let legndLabls = this.legndCont.find(".legendLabel");
    //if cursor in placeholder
    if(this.pos.x > this.xax.min && this.pos.x < this.xax.max) {
    //if series name bar not build, and titles already received then do it
        if((!(this.seriesNamContainersArr.length > 0)) &&
            (this.legendTitlesArr.length > 0)) {

            for (var i = 0; i < this.apCount; i++) {
                var id = 'seriesLabel' + (this.seriesNamContainersArr.length + 1),
                    refParam = this.apArr[i],
                    time = "-",
                    yAxNum = i,
                    value = "-",
                    content = refParam + ", " + this.legendTitlesArr[i],
                    color = this.associativeParamsArr[refParam][1];
                var el = this.CreateBarContainer(id, refParam, time, value, content, yAxNum, color);
                this.seriesNamContainersArr.push(el);
            }


            var j = 0;
            for (var i = this.apCount; i < this.paramCount; i++) {
                var id = 'seriesLabel' + (this.seriesNamContainersArr.length + 1),
                    refParam = this.bpArr[j],
                    time = "-",
                    value = "1",
                    content = refParam + ", " + this.legendTitlesArr[i],
                    yAxNum = i,
                    color = this.associativeParamsArr[refParam][1];
                    var el = this.CreateBarContainer(id, refParam, time, value, content, yAxNum, color);
                    this.seriesNamContainersArr.push(el);
                j++;
            }
        }

        for (var i = 0; i < this.apCount; i++) {
            var seriesNamCont = this.seriesNamContainersArr[i],
                labelText = legndLabls.eq(i).text(),
                time = this.pos.x,
                yAxNum = i,
                value = labelText.substring(labelText.indexOf('=') + 2, labelText.length),
                yAxis = this.yaxs[yAxNum],
                yMin = this.yaxs[yAxNum].min,
                yMax = this.yaxs[yAxNum].max,
                excCoordY = yAxis.p2c(value),
                excCoordX = this.xax.p2c(time);
            if((value > yMin) && (value < yMax)){
                seriesNamCont.css({
                    'display': displayNamesState,
                    top: excCoordY + 20,
                    left: excCoordX + 25
                });
            } else {
                seriesNamCont.fadeOut(200);
            }
        }

        var j = 0;
        for (var i = this.apCount; i < this.paramCount; i++) {
            var seriesNamCont = this.seriesNamContainersArr[i],
                time = this.pos.x,
                yAxNum = i,
                value = 1; //always for bp
                yAxis = this.yaxs[yAxNum],
                yMin = this.yaxs[yAxNum].min,
                yMax = this.yaxs[yAxNum].max,
                excCoordX = this.xax.p2c(time),
                excCoordY = yAxis.p2c(value);
            if((value > yMin) && (value < yMax)){
                seriesNamCont.css({
                    'display': displayNamesState,
                    top: excCoordY + 20,
                    left: excCoordX + 25
                });
            } else {
                seriesNamCont.fadeOut(200);
            }

            j++;
        }
    }
};

//=============================================================

//=============================================================
//
Legend.prototype.ShowSeriesLabels = function(paramValues) {
    var displayNamesState = this.showSeriesLabelsNeed ? 'block' : 'none';
    let legndLabls = this.legndCont.find(".legendLabel");

    //if series name bar not build, and titles already received then do it
    if((!(this.seriesLeftLabelsContainersArr.length > 0)) &&
        (this.seriesLabelsValues.length > 0) &&
        (this.legendTitlesArr.length > 0)) {

        for (var i = 0; i < this.apCount; i++) {
            var id = 'seriesLeftLabel' + (this.seriesLeftLabelsContainersArr.length + 1),
                refParam = this.apArr[i],
                time = "-",
                yAxNum = i,
                value = "-",
                content = refParam + ", " + this.legendTitlesArr[i],
                color = this.associativeParamsArr[refParam][1];
            var el = this.CreateBarContainer(id, refParam, time, value, content, yAxNum, color);
            this.seriesLeftLabelsContainersArr.push(el);
        }

        var j = 0;
        for (var i = this.apCount; i < this.paramCount; i++) {
            var id = 'seriesLeftLabel' + (this.seriesLeftLabelsContainersArr.length + 1),
                refParam = this.bpArr[j],
                time = "-",
                value = "1",
                content = refParam + ", " + this.legendTitlesArr[i],
                yAxNum = i,
                color = this.associativeParamsArr[refParam][1];
                var el = this.CreateBarContainer(id, refParam, time, value, content, yAxNum, color);
                this.seriesLeftLabelsContainersArr.push(el);
            j++;
        }
    }

    for (var i = 0; i < this.apCount; i++) {
        var seriesNamCont = this.seriesLeftLabelsContainersArr[i],
            labelText = legndLabls.eq(i).text(),
            time = this.seriesLabelsTime,
            yAxNum = i,
            value = this.seriesLabelsValues[i],
            yAxis = this.yaxs[yAxNum],
            yMin = this.yaxs[yAxNum].min,
            yMax = this.yaxs[yAxNum].max,
            xMin = this.xax.min,
            xMax = this.xax.max,
            excCoordY = yAxis.p2c(value),
            excCoordX = this.xax.p2c(time);
        if((value > yMin) && (value < yMax) &&
            (this.seriesLabelsTime > xMin) && (this.seriesLabelsTime < xMax)){
            seriesNamCont.css({
                'display': displayNamesState,
                top: excCoordY + 20,
                left: excCoordX + 5
            });
        } else {
            seriesNamCont.fadeOut(200);
        }
    }

    var j = 0;
    for (var i = this.apCount; i < this.paramCount; i++) {
        var seriesNamCont = this.seriesLeftLabelsContainersArr[i],
            time = this.seriesLabelsTime,
            yAxNum = i,
            value = 1; //always for bp
            yAxis = this.yaxs[yAxNum],
            yMin = this.yaxs[yAxNum].min,
            yMax = this.yaxs[yAxNum].max,
            xMin = this.xax.min,
            xMax = this.xax.max,
            excCoordX = this.xax.p2c(time),
            excCoordY = yAxis.p2c(value);
        if((value > yMin) && (value < yMax) &&
            (this.seriesLabelsTime > xMin) && (this.seriesLabelsTime < xMax)){
            seriesNamCont.css({
                'display': displayNamesState,
                top: excCoordY + 20,
                left: excCoordX + 5
            });
        } else {
            seriesNamCont.fadeOut(200);
        }

        j++;
    }

};

Legend.prototype.addComment = function(time, y) {
    var excCoordX = this.xax.p2c(time);
    var excCoordY = this.yaxs[0].p2c(y);
    var that = this;

    var $text = $('<div></div>', {
        'id': 'text',
        'class': 'horizontal-text-container horizontal-text-container--is-active',
        'contenteditable': 'true',
        'data-time': time,
        'data-val': y})
    .css({
        'left': excCoordX,
        'top': excCoordY + 25,
    })
    .appendTo(this.ccCont);

    $text.focus();

    $text.dblclick(function() {
        $(this).remove();
    });

    function deactiveComment(event) {
        if((event.target
                && !$(event.target).hasClass('horizontal-text-container')
            )
            || (event.which == 13)
        ) {
            $.each($('.horizontal-text-container'), function(key, value) {
                var $el = $(value);
                $el.removeClass('horizontal-text-container--is-active')
                .attr('contenteditable', 'false');

            });
            $(document).off('click', deactiveComment);
            $(document).off('keypress', deactiveComment);

            that.verticalTextInput = false;
        }
    };

    $(document).click(deactiveComment);
    $(document).keypress(deactiveComment);

    this.verticalTextInput = true;

    return $text;
}

//=============================================================

//=============================================================
//UNIX_timestamp to hour+':'+min+':'+sec
Legend.prototype.toHHMMSS = function(UNIX_timestamp){
     var a = new Date(UNIX_timestamp);
     //var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
     /*var year = a.getFullYear();
     var month = months[a.getMonth()];
     var date = a.getDate();*/
     var hour = a.getHours(),
          min = a.getMinutes(),
          sec = a.getSeconds();

     if(hour.toString().length < 2){
         hour = '0' + hour;
     }

     if(min.toString().length < 2){
         min = '0' + min;
     }

     if(sec.toString().length < 2){
         sec = '0' + sec;
     }

     var time = /*date+','+month+' '+year+' '+*/hour+':'+min+':'+sec ;
     return time;
}

export default Legend;
