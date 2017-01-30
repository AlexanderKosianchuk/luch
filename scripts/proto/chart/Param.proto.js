//=============================================================
//┏━━━┓
//┃┏━┓┃
//┃┗━┛┣━━┳━┳━━┳┓┏┓
//┃┏━━┫┏┓┃┏┫┏┓┃┗┛┃
//┃┃╋╋┃┏┓┃┃┃┏┓┃┃┃┃
//┗┛╋╋┗┛┗┻┛┗┛┗┻┻┻┛
//=============================================================

function Param(flightId,
    startFrame, endFrame,
    apParams, bpParams, isPrintPage){

    this.flightId = flightId;
    this.apCount = 0;
    this.bpCount = 0;
    this.paramCount = 0;
    this.apArr = apParams;
    this.bpArr = bpParams;
    this.refParamArr = new Array();
    this.associativeParamsArr = new Array();
    this.data = Array();
    this.receivedParams = Array();

    this.startFrame = startFrame;
    this.endFrame = endFrame;

    this.isPrintPage = isPrintPage;

    if(this.apArr != null){
        this.apCount = this.apArr.length;
        this.refParamArr = this.refParamArr.concat(this.apArr);
        this.paramCount += this.apCount;
    }

    if(this.bpArr != null){
        this.bpCount = this.bpArr.length;
        this.refParamArr = this.refParamArr.concat(this.bpArr);
        this.paramCount += this.bpCount;
    }
};
//=============================================================

//=============================================================
Param.prototype.ReceiveParams = function(lineWidth){
    var self = this,
        dfd = new $.Deferred();
    self.receivedParams = Array();

    // Show a "working..." message every half-second
    setTimeout(function working() {
        if ( dfd.state() === "pending" ) {
            dfd.notify( "working... " );
            setTimeout( working, 500 );
        }
    }, 1 );

    //iterational receiving ap data arrays
    for (var i = 0; i < this.apCount; i++) {
        var apName = self.apArr[i];
        self.GetApParam(apName, i, lineWidth, dfd);
    }
    //=============================================================

    //=============================================================
    //iterational receiving bp data arrays
    for (var i = 0; i < this.bpCount; i++) {
        var bpName = self.bpArr[i];
        self.GetBpParam(bpName, i, lineWidth, dfd);
    }

    return dfd.promise();
};
//=============================================================

//=============================================================
Param.prototype.GetApParam = function(paramCode, i, lineWidth, dfd){
    var self = this,
        apDataArray = Array();

        pV = {
            action: 'getApParamValue',
            data:{
                flightId: self.flightId,
                paramApCode: paramCode,
                totalSeriesCount: self.apCount,
                startFrame: self.startFrame,
                endFrame: self.endFrame,
                isPrintPage: self.isPrintPage
            }
        };

    $.ajax({
        data: pV,
        type: "POST",
        dataType: "json",
        url: CHART_SRC,
        async: true
    }).done(function(receivedParamPoints){
        apDataArray = receivedParamPoints;
        var pV = {
                action: 'getParamInfo',
                data: {
                    flightId: self.flightId,
                    paramCode: paramCode
                }
        };

        $.ajax({
            type: "POST",
            data: pV,
            dataType: 'json',
            url: CHART_SRC,
            async: true
        }).done(function(receivedInfo){
            var color = receivedInfo['color'],
                nm = receivedInfo['name'];
            var apDataFlotSeries = {
                    data: apDataArray,
                    label: paramCode + " " + nm + " = 0.00",
                    yaxis: i + 1,
                    color: "#" + color,
                    shadowSize: 0,
                    lines: { lineWidth: lineWidth, show: true }
                };
            self.data[i] = apDataFlotSeries;
            if (self.associativeParamsArr[paramCode] === undefined) {
                self.associativeParamsArr[paramCode] = [i, color];
            }

            self.receivedParams.push(paramCode);
            if(self.receivedParams.length == (self.apCount + self.bpCount)) {
                dfd.resolve(paramCode);
            }

        }).fail(function(mess){
            dfd.reject(mess);
        });
    }).fail(function(mess){
        dfd.reject(mess);
    });

}
//=============================================================

//=============================================================
Param.prototype.GetBpParam = function(paramCode, i, lineWidth, dfd){
    var self = this,
        bpDataArray = Array(),
        color = String(),
        pV = {
            action: 'getBpParamValue',
            data:{
                flightId: self.flightId,
                paramBpCode: paramCode
            }
        };

    $.ajax({
        type: "POST",
        data: pV,
        dataType: 'json',
        url: CHART_SRC,
        async: true
    }).done(function(receivedParamPoints){
        bpDataArray = receivedParamPoints;

        var pV = {
                action: 'getParamInfo',
                data: {
                    flightId: self.flightId,
                    paramCode: paramCode
                }
        };

        $.ajax({
            type: "POST",
            data: pV,
            dataType: 'json',
            url: CHART_SRC,
            async: true
        }).done(function(receivedInfo){
            var color = receivedInfo['color'],
                nm = receivedInfo['name'];

            var bpDataFlotSeries = {
                    data: bpDataArray,
                    label: paramCode + " " + nm + " = F",
                    yaxis: self.apCount + i + 1,
                    color: "#" + color,
                    points: { symbol: "square", show: true, radius: lineWidth + 1, fillColor: "#" + color},
                    shadowSize: 0,
                    lines: { lineWidth: lineWidth, show: true, }
                };
            self.data[self.apCount + i] = bpDataFlotSeries;
            if (self.associativeParamsArr[paramCode] === undefined) {
                self.associativeParamsArr[paramCode] = [self.apCount + i, color];
            }

            self.receivedParams.push(paramCode);
            if(self.receivedParams.length == (self.apCount + self.bpCount)) {
                dfd.resolve(paramCode);
            }

        }).fail(function(mess){
            dfd.reject(mess);
        });
    }).fail(function(mess){
        dfd.reject(mess);
    });
}

//=============================================================

//=============================================================
//Get value by x coord by interpolating
Param.prototype.GetValue = function(dataset, x) {
    var yArr = Array();
    for (var i = 0; i < this.apCount; ++i) {
        var series = dataset[i];
        // Find the nearest points, x-wise
        for (var j = 0; j < series.data.length; ++j) {
            if (series.data[j][0] > x) {
                break;
            };
        }

        // Now Interpolate
        var y,
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
//=============================================================
//Get value by x coord by interpolating
Param.prototype.GetBinaries = function(dataset, x){
    var bpPrasentArray = new Array();
    for (var i = this.apCount; i < this.paramCount; ++i) {
        var series = dataset[i],
            bpPrasent = false,
            notFound = true;

        // Find the nearest points, x-wise
        for (var j = 0; j < series.data.length; ++j) {
            if(series.data[j] != null)
            {
                if (series.data[j][0] > x) {
                    notFound = false;
                    break;
                } else {
                    notFound = true;
                };
            };
        }

        if((j > 0) && (!notFound)){
            if(series.data[j - 1] != null){
                bpPrasent = true;
            };
        }

        bpPrasentArray.push(bpPrasent);
    }
    return bpPrasentArray;
};
//=============================================================
