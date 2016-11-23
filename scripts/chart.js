jQuery(function($) { $(document).ready(function() {

    var $document = $(document),
        $window = $(window),
        userLang = $('html').attr("lang"),
        eventHandler = $('#eventHandler');

    var LA = new Language(userLang),
    C = null;

    LA.GetLanguage().done(function(data){
        var langStr = data;
        LA.GetServiceStrs().done(function(data){
            var srvcStrObj = data;

            C = new Chart($window, $document, langStr, srvcStrObj, eventHandler, true);

            var flightId = $("#flightId").text(),
                tplName = $("#tplName").text(),
                stepLength = $("#stepLength").text(),
                startCopyTime = $("#startCopyTime").text(),
                startFrame = $("#startFrame").text(),
                endFrame = $("#endFrame").text(),
                apParams = $("#apParams").text().split(","),
                bpParams = $("#bpParams").text().split(",");

            var showcase = $window;

            if(C != null) {
                C.SetChartData(flightId, tplName,
                        stepLength, startCopyTime, startFrame, endFrame,
                        apParams, bpParams);

                C.chartFactoryContainer = showcase;

                C.chartWorkspace = $('div#chartWorkspace');
                C.chartContent = $('div#graphContainer');

                C.loadingBox = $("div#loadingBox").css("top", $window.height() / 2 - 40);
                C.legend = $('div#legend');
                C.placeholder = $('div#placeholder');

                setInitialChartSize.apply(C);
                C.LoadFlotChart();

                C.chartWorkspace.resizable().resize(function(){
                    var interval = setInterval(function(){
                        ResizeChart.apply(C);
                        C.plot.pan(0);
                        clearInterval(interval);
                    }, 1000);
                });
            }
        });
    });

    function setInitialChartSize(){
        this.chartWorkspace.css({
            "top": 0,
            "left": 0,
            "height": this.window.height() - 25,
            "width": this.window.width() - 25
        });
        ResizeChart.apply(this);
    }

    function ResizeChart(){
        this.chartContent.css({
            "top": 0,
            "left": 0,
            "width" : this.chartWorkspace.width(),
            "height": this.chartWorkspace.height()
        });

        if((this.chartContent !== null) &&
                (this.placeholder !== null) &&
                (this.legend !== null) &&
                (this.apParams !== null) &&
                (this.bpParams !== null)){

            this.placeholder.css({
                "margin-top": '30px',
                "width": this.chartContent.width() - LEGEND_CONTAINER_OUTER + 'px',
                "height": this.chartContent.height() - 35 + 'px'
                });
            this.legend.css({
                "margin-top": '35px',
                "width": LEGEND_CONTAINER_OUTER + "px",
                "height": this.placeholder.height() - 25 + 'px'
            });

            this.placeholder.css("width",  (this.chartContent.width() - (this.legend.width() + 30) +
                (this.apParams.length + this.bpParams.length) * 18) + "px");

            if((this.apParams.length == 1) && (this.bpParams.length === 0)){
                this.placeholder.css("margin-left",  "-7px");
            } else {
                this.placeholder.css("margin-left",  "-" +
                    ((this.apParams.length + this.bpParams.length - 1) * 18) + "px");
            }
        }
    }

});});
