'use strict';

require('jquery');
require('jquery-mousewheel');
require('jquery-ui');
require('jquery-ui/ui/widgets/dialog');
require('jquery-ui/ui/widgets/button');
require('jquery-ui/ui/widgets/menu');
require('jquery-ui/ui/widgets/slider');
require('colorpicker-amin');
require('chosen-npm');
require('blueimp-file-upload');
require('jstree');
require('flot-charts');
require('flot-charts/jquery.flot.time');
require('flot-charts/jquery.flot.symbol');
require('flot-charts/jquery.flot.navigate');
require('flot-charts/jquery.flot.resize');
require('datatables');

require('jquery-ui/themes/base/all.css');
require('jstree/dist/themes/default/style.min.css');
require('blueimp-file-upload/css/jquery.fileupload.css');
require('blueimp-file-upload/css/jquery.fileupload-ui.css');
require('colorpicker-amin/jquery.colorpicker.css');
require('chosen-npm/public/chosen.css');

require('stylesheets/pages/bruTypeTemplates.css');
require('stylesheets/pages/viewOptionsParams.css');
require('stylesheets/pages/viewOptionsEvents.css');
require('stylesheets/pages/chart.css');
require('stylesheets/pages/user.css');
require('stylesheets/pages/flight.css');
require('stylesheets/pages/searchFlight.css');
require('stylesheets/pages/login.css');
require('stylesheets/pages/calibration.css');
require('stylesheets/style.css');

var Language = require("Language");
var WindowFactory = require("WindowFactory");
var FlightList = require("FlightList");
var FlightUploader = require("FlightUploader");
var FlightProccessingStatus = require("FlightProccessingStatus");
var FlightViewOptions = require("FlightViewOptions");
var Fdr = require("Fdr");
var Chart = require("Chart");
var User = require("User");
var SearchFlight = require("SearchFlight");
var Calibration = require("Calibration");

var LEGEND_CONTAINER_OUTER = 175,
    PARAM_TYPE_AP = "ap",
    PARAM_TYPE_BP = "bp";

jQuery(function($) {
    $(document).ready(function() {

        var $document = $(document),
            $window = $(window),
            userLang = $('html').attr("lang"),
            eventHandler = $('#eventHandler');

        var LA = new Language(userLang),
            C = null;

        LA.GetLanguage().done(function(data) {
            var langStr = data;
            C = new Chart($window, $document, langStr, eventHandler, true);

            var flightId = $("#flightId").text(),
                tplName = $("#tplName").text(),
                stepLength = $("#stepLength").text(),
                startCopyTime = $("#startCopyTime").text(),
                startFrame = $("#startFrame").text(),
                endFrame = $("#endFrame").text(),
                apParams = $("#apParams").text().split(","),
                bpParams = $("#bpParams").text().split(",");

            var showcase = $window;

            if (C != null) {
                C.SetChartData(flightId, tplName,
                    stepLength, startCopyTime, startFrame, endFrame,
                    apParams, bpParams);

                C.chartFactoryContainer = showcase;

                C.chartWorkspace = $('div#chartWorkspace');
                C.chartContent = $('div#graphContainer');

                C.loadingBox = $("div#loadingBox").css("top", $window.height() / 2 - 40);
                C.legend = $('div#legend');
                C.placeholder = $('div#placeholder');

                C.placeholder.on("mouseover", function(e) {
                    C.mouseInChat = true;
                });

                C.placeholder.on("mouseout", function(e) {
                    C.mouseInChat = false;
                });

                setInitialChartSize.apply(C);
                C.LoadFlotChart();

                C.chartWorkspace.resizable().resize(function() {
                    var interval = setInterval(function() {
                        ResizeChart.apply(C);
                        C.plot.pan(0);
                        clearInterval(interval);
                    }, 1000);
                });
            }
        });

        function setInitialChartSize() {
            this.chartWorkspace.css({
                "top": 0,
                "left": 0,
                "height": this.window.height() - 25,
                "width": this.window.width() - 25
            });
            ResizeChart.apply(this);
        }

        function ResizeChart() {
            this.chartContent.css({
                "top": 0,
                "left": 0,
                "width": this.chartWorkspace.width(),
                "height": this.chartWorkspace.height()
            });

            if ((this.chartContent !== null)
                && (this.placeholder !== null)
                && (this.legend !== null)
                && (this.apParams !== null)
                && (this.bpParams !== null)
            ) {

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

                this.placeholder.css("width", (this.chartContent.width() - (this.legend.width() + 30) +
                    (this.apParams.length + this.bpParams.length) * 18) + "px");

                if ((this.apParams.length == 1) && (this.bpParams.length === 0)) {
                    this.placeholder.css("margin-left", "-7px");
                } else {
                    this.placeholder.css("margin-left", "-" +
                        ((this.apParams.length + this.bpParams.length - 1) * 18) + "px");
                }
            }
        }

    });
});
