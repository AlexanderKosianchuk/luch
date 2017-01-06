/*jslint browser: true*/
/*global $, jQuery*/
/*global Language, WindowFactory, FlightList, FlightUploader, FlightProccessingStatus*/
/*global FlightViewOptions, BruType, Chart, User, SearchFlight*/

jQuery(function ($) {
    'use strict';
    $(document).ready(function () {
        var $document = $(document),
            $window = $(window),
            userLang = $('html').attr("lang"),
            eventHandler = $('#eventHandler'),
            LA = new Language(userLang),
            W = new WindowFactory($window, $document),
            FP = null,
            FU = null,
            FO = null,
            B = null,
            C = null,
            U = null,
            FL = null,
            SF = null;

        LA.GetLanguage().done(function (data) {
            var langStr = data;
            LA.GetServiceStrs().done(function (data) {
                var srvcStrObj = data,
                    wsp = W.NewShowcase();
                FL = new FlightList(langStr, srvcStrObj, eventHandler);
                FU = new FlightUploader($window, $document, langStr, srvcStrObj, eventHandler);
                FP = new FlightProccessingStatus(langStr);
                FO = new FlightViewOptions($window, $document, langStr, srvcStrObj, eventHandler);
                B = new BruType($window, $document, langStr, srvcStrObj, eventHandler);
                C = new Chart($window, $document, langStr, srvcStrObj, eventHandler);
                U = new User($window, $document, langStr, srvcStrObj, eventHandler);
                SF = new SearchFlight($window, $document, langStr, srvcStrObj, eventHandler);

                FL.FillFactoryContaider(wsp);
            });
        });

        $window.resize(function (e) {
            if (W !== null) {
                W.ResizeShowcase(e);
            }

            if (FL !== null) {
                FL.ResizeFlightList(e);
            }
            if (FO !== null) {
                FO.ResizeFlightViewOptionsContainer(e);
            }
            if (C !== null) {
                C.ResizeChartContainer(e);
            }

            if (B !== null) {
                B.ResizeBruTypeContainer(e);
            }
        });

        $document.resize(function (e) {
            if (W !== null) {
                W.ResizeShowcase(e);
            }
        });

        eventHandler.on("resizeShowcase", function (e) {
            W.ResizeShowcase(e);
        });

        eventHandler.on("uploading", function () {
            FU.CaptureUploadingItems();
            FP.SupportUploadingStatus();
        });

        eventHandler.on("uploadWithPreview", function () {
            var showcase = W.NewShowcase();
            FU.FillFactoryContaider(showcase);
        });

        eventHandler.on("removeShowcase", function (e, data, callback) {
            var flightUploaderFactoryContainer = data;
            W.RemoveShowcase(flightUploaderFactoryContainer);

            if ($.isFunction(callback)) {
                callback();
            }
        });

        ///=======================================================
        //FlightList
        ///

        eventHandler.on("flightSearchFormShow", function (e, showcase) {
            if (showcase === null) {
                W.RemoveShowcases(1);
                showcase = W.NewShowcase();
            } else {
                W.ClearShowcase(showcase);
            }

            if ((FP !== null) && (FL !== null)) {
                FL.ShowFlightsListInitial(showcase);
                FP.SupportUploadingStatus();
            }
        });

        eventHandler.on("startProccessing", function (e, data) {
            var bruType = data['bruType'],
                fileName = data['fileName'],
                tempFileName = data['tempFileName'];

            if (FP !== null) {
                FP.SetUpload(fileName, bruType, tempFileName);
            }
        });

        eventHandler.on("endProccessing", function (e, data) {
            var fileName = data;
            if (FP !== null) {
                FP.RemoveUpload(fileName);
            }
        });

        eventHandler.on("convertSelectedClicked", function (e) {
            W.RemoveShowcases(1);

            if (FL !== null) {
                FL.ShowFlightsByPath();
            }
        });

        eventHandler.on("viewFlightOptions", function (e, flightId, task, someshowcase) {
            if (someshowcase === null) {
                W.RemoveShowcases(1);
                someshowcase = W.NewShowcase();
            } else {
                W.ClearShowcase(someshowcase);
            }

            if (flightId !== null) {
                FO.flightId = flightId;
            }

            if (task !== null) {
                FO.task = task;
            }

            if (FO.flightId !== null) {
                FO.FillFactoryContaider(someshowcase);
            }
        });

        eventHandler.on("showBruTypeEditingForm", function (e, bruTypeId, task, showcase) {
            if (showcase === null) {
                W.RemoveShowcases(1);
                showcase = W.NewShowcase();
            } else {
                W.ClearShowcase(showcase);
            }

            if (bruTypeId !== null) {
                B.bruTypeId = bruTypeId;
            }

            if (task !== null) {
                B.task = task;
            }

            B.FillFactoryContaider(showcase);
        });

        eventHandler.on("userLogout", function (e) {
            U.logout();
        });

        eventHandler.on("userChangeLanguage", function (e, lang) {
            U.changeLanguage(lang);
        });

        eventHandler.on("userShowList", function (e, showcase) {
            if (showcase === null) {
                W.RemoveShowcases(1);
                showcase = W.NewShowcase();
            } else {
                W.ClearShowcase(showcase);
            }

            U.FillFactoryContaider(showcase);
        });

        eventHandler.on("flightSearchFormShow", function (e, showcase) {
            if (showcase === null) {
                W.RemoveShowcases(1);
                showcase = W.NewShowcase();
            } else {
                W.ClearShowcase(showcase);
            }

            SF.FillFactoryContaider(showcase);
        });

        eventHandler.on("showChart", function (e,
                flightId, tplName,
                stepLength, startCopyTime, startFrame, endFrame,
                apParams, bpParams) {

            W.RemoveShowcases(2);
            var showcase = W.NewShowcase();

            if (C !== null) {
                C.SetChartData(flightId, tplName,
                        stepLength, startCopyTime, startFrame, endFrame,
                        apParams, bpParams);

                C.FillFactoryContaider(showcase);
            }
        });

        eventHandler.on("saveChartTpl", function (e, flightId, tplName, saveChartTplCb) {
            B.copyTemplate(flightId, tplName).then(saveChartTplCb);
        });

        var allowScrollUp = false;
        var allowScrollDown = false;

        function updateScrollPermission(event) {
            var $el = $(event.target);

            allowScrollUp = false;
            allowScrollDown = false;

            if(($el.hasClass('is-scrollable') && $el.scrollTop() > 0)
                || ($el.parents('.is-scrollable').length && $($el.parents('.is-scrollable').get(0)).scrollTop() > 0)
            ) {
                allowScrollUp = true;
            }

            if(($el.hasClass('is-scrollable') && ($el.scrollTop() < ($el.get(0).scrollHeight - $el.get(0).clientHeight)))
                || $el.parents('.is-scrollable').length
                    && ($($el.parents('.is-scrollable').get(0)).scrollTop()
                        < ($el.parents('.is-scrollable').get(0).scrollHeight
                            - $el.parents('.is-scrollable').get(0).clientHeight
                        )
                    )
            ) {
                allowScrollDown = true;
            }
        }

        $(window).bind('mousewheel DOMMouseScroll', function(event){
            updateScrollPermission(event);
            if (event.originalEvent.wheelDelta > 0 || event.originalEvent.detail < 0) {
                if(!allowScrollUp) return false;
            }
            else {
                if(!allowScrollDown) return false;
            }
        });
    });
});
