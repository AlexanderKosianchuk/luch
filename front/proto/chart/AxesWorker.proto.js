//=============================================================
//┏━━━┓╋╋╋╋╋╋╋╋┏┓┏┓┏┓╋╋╋╋┏┓
//┃┏━┓┃╋╋╋╋╋╋╋╋┃┃┃┃┃┃╋╋╋╋┃┃
//┃┃╋┃┣┓┏┳━━┳━━┫┃┃┃┃┣━━┳━┫┃┏┳━━┳━┓
//┃┗━┛┣╋╋┫┃━┫━━┫┗┛┗┛┃┏┓┃┏┫┗┛┫┃━┫┏┛
//┃┏━┓┣╋╋┫┃━╋━━┣┓┏┓┏┫┗┛┃┃┃┏┓┫┃━┫┃
//┗┛╋┗┻┛┗┻━━┻━━┛┗┛┗┛┗━━┻┛┗┛┗┻━━┻┛
//=============================================================


function AxesWorker(extStepLength, extStartCopyTime, plotAxes){
    this.stepLength = extStepLength;
    this.startCopyTime = extStartCopyTime;
    this.axes = plotAxes;
    this.startFrame = 0;
    this.endFrame = 0;
    this.redrawNeed = false;
    this.frameRange = 0;
    this.frameOffset = 0;
    this.distributionProc = 0;
    this.savingDistribution = true;
};
//=============================================================

//=============================================================
AxesWorker.prototype.Distribute = function(yAxArr, xAx, series, apCount){
    var corridorsNum = apCount;
    for(var i = 0; i < apCount; i++){
        var yMax = series[i].yaxis.datamax,
            yMin = series[i].yaxis.datamin,
            curCorridor = 0;

        if((i == 0) && (yMax > 1)){
            yMax += yMax * 0.15;//prevent first(top) param out ceiling chart boundary
        }

        if(yMax == yMin){
            yMax += 0.001;
        }

        if(yMax > 0){
            curCorridor = ((yMax - yMin) * 1.05);
        } else {
            curCorridor = -((yMin - yMax) * 1.05);
        }

        yAxArr[i].max = yMax + (i * curCorridor);
        yAxArr[i].min = yMin -
            ((corridorsNum - i) * curCorridor);
    }

    var bpCount = yAxArr.length - apCount;

    if(bpCount > 0) {
        var busyCorridor = ((apCount - 1) / apCount * 100),
            freeCorridor = 100 - busyCorridor,//100%
            curCorridor = freeCorridor / bpCount,
            j = 0;

        for(var i = apCount; i < yAxArr.length; i++){

            yAxArr[i].max = 100 - (curCorridor * j);
            yAxArr[i].min = 0 - (curCorridor * j);
            j++;
        };
    }

    xAx.xaxis.max = series[0].xaxis.datamax;
    xAx.xaxis.min = series[0].xaxis.datamin;
};
//=============================================================

//=============================================================
AxesWorker.prototype.DistributeByBinary = function(yAxArr, xAx, series, apCount){
    var bpCount = yAxArr.length - apCount,
        corridorsNum = yAxArr.length;
    for(var i = 0; i < corridorsNum; i++){
        var yMax = series[i].yaxis.datamax,
            yMin = series[i].yaxis.datamin,
            curCorridor = 0;

        if((i == 0) && (yMax > 1)){
            yMax += yMax * 0.01;//prevent first(top) param out ceiling chart boundary
        }

        if(yMax == yMin){
            yMax += 0.001;
        }

        if(i < apCount){
            if(yMax > 0){
                curCorridor = ((yMax - yMin) * 1.05);
            } else {
                curCorridor = -((yMin - yMax) * 1.05);
            }

            yAxArr[i].max = yMax + (i * curCorridor);
            yAxArr[i].min = yMin -
                ((corridorsNum - i) * curCorridor);
        } else {
            curCorridor = 2;

            yAxArr[i].max = yMax + (i * curCorridor);
            yAxArr[i].min = yMin -
                ((corridorsNum - i) * curCorridor);
        }
    }

    xAx.xaxis.max = series[0].xaxis.datamax;
    xAx.xaxis.min = series[0].xaxis.datamin;
};
//=============================================================

//=============================================================
AxesWorker.prototype.LoadDistribution = function(yAxArr, apParams, bpParams, flightId, tplName){
    var self = this,
        paramsArr = apParams.concat(bpParams);

    for (var i = 0; i < paramsArr.length; i++) {
        var pV = {
            flightId: flightId,
            code: paramsArr[i],
            tplName: tplName,
        };

        $.ajax({
            type: 'POST',
            data: pV,
            dataType: 'json',
            url: ENTRY_URL+'chart/getParamMinMax',
            async: false
        }).done(function(receivedMinMax){
            var minMax = receivedMinMax;

            yAxArr[i].max = minMax['max'];
            yAxArr[i].min = minMax['min'];

        }).fail(function(e){
            console.log(e);
        });
    }
};

//=============================================================

//=============================================================
AxesWorker.prototype.SaveDistribution = function(yAxArr, apParams, bpParams, flightId, tplName){
    var paramsArr = apParams.concat(bpParams),
        self = this;

    //all async saving complete
    if(self.distributionProc == 0) {
        self.distributionProc = paramsArr.length;

        for (var i = 0; i < paramsArr.length; i++) {
            $.ajax({
                type: "POST",
                data: {
                    flightId: flightId,
                    paramCode: paramsArr[i],
                    tplName: tplName,
                    max: yAxArr[i].max,
                    min: yAxArr[i].min,
                    username: this.user
                },
                url: ENTRY_URL + "?action=chart/setParamMinmaxAction",
            }).done(function(e){
                self.distributionProc--;
            }).fail(function(e){
                console.log(e);
            });
        }
    }
};

export default AxesWorker;
