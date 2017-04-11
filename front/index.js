/*jslint browser: true*/
/*global $, jQuery*/
/*global Language, WindowFactory, FlightList, FlightUploader, FlightProccessingStatus*/
/*global FlightViewOptions, Fdr, Chart, User, SearchFlight*/

'use strict';

// libs
import 'jquery';
import 'jquery-mousewheel';
import 'jquery-ui';
import 'jquery-ui/ui/widgets/dialog';
import 'jquery-ui/ui/widgets/button';
import 'jquery-ui/ui/widgets/menu';
import 'jquery-ui/ui/widgets/slider';
import 'colorpicker-amin';
import 'chosen-npm';
import 'blueimp-file-upload';
import 'jstree';
import 'flot-charts';
import 'flot-charts/jquery.flot.time';
import 'flot-charts/jquery.flot.symbol';
import 'flot-charts/jquery.flot.navigate';
import 'flot-charts/jquery.flot.resize';
import 'datatables';
import 'bootstrap-loader';

// libs with export
import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';

// lib styles
import 'jquery-ui/themes/base/all.css';
import 'jstree/dist/themes/default/style.min.css';
import 'blueimp-file-upload/css/jquery.fileupload.css';
import 'blueimp-file-upload/css/jquery.fileupload-ui.css';
import 'colorpicker-amin/jquery.colorpicker.css';
import 'chosen-npm/public/chosen.css';

//old styles
import 'stylesheets/pages/bruTypeTemplates.css';
import 'stylesheets/pages/viewOptionsParams.css';
import 'stylesheets/pages/viewOptionsEvents.css';
import 'stylesheets/pages/chart.css';
import 'stylesheets/pages/user.css';
import 'stylesheets/pages/flight.css';
import 'stylesheets/pages/searchFlight.css';
import 'stylesheets/pages/login.css';
import 'stylesheets/pages/calibration.css';
import 'stylesheets/style.css';

// old prototypes
import Language from "Language";
import WindowFactory from "WindowFactory";
import FlightList from "FlightList";
import FlightUploader from "FlightUploader";
import FlightProccessingStatus from "FlightProccessingStatus";
import FlightViewOptions from "FlightViewOptions";
import Fdr from "Fdr";
import Chart from "Chart";
import User from "User";
import SearchFlight from "SearchFlight";
import Calibration from "Calibration";

// react implementation
import Results from 'components/results/Results';
import Flights from 'components/flights/Flights';
import configureStore from 'store/configureStore';

const store = configureStore({});

$(document).ready(function () {
    var i18n = {},
        $document = $(document),
        $window = $(window),
        userLang = $('html').attr("lang"),
        userLogin = $('html').attr("login"),
        avaliableLanguages = $('html').attr("avaliable-languages").toUpperCase().split(','),
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
        SF = null,
        CLB = null;

    LA.GetLanguage().done(function (data) {
        var langStr = i18n = data;
        var wsp = W.NewShowcase();
        FL = new FlightList(langStr, eventHandler, userLogin, store);
        FU = new FlightUploader($window, $document, langStr, eventHandler);
        FP = new FlightProccessingStatus(langStr);
        FO = new FlightViewOptions($window, $document, langStr, eventHandler);
        B = new Fdr($window, $document, langStr, eventHandler);
        C = new Chart($window, $document, langStr, eventHandler);
        U = new User($window, $document, langStr, eventHandler);
        SF = new SearchFlight($window, $document, langStr, eventHandler);
        CLB = new Calibration($window, $document, langStr, eventHandler);

        let flightsServise = {
            showFlightsList: function () {
                eventHandler.trigger("flightListShow", [
                    $('#flightsContainer')
                ]);
            },
            showFlightSearch: function () {
                eventHandler.trigger("flightSearchFormShow", [
                    $('#flightsContainer')
                ]);
            },
            showResults: function () {
                eventHandler.trigger("resultsLeftMenuRow", [
                    $('#flightsContainer')
                ]);
            },
            showCalibrations: function () {
                eventHandler.trigger("calibrationFormShow", [
                    $('#flightsContainer')
                ]);
            },
            showUsers: function () {
                eventHandler.trigger("userShowList", [
                    $('#flightsContainer')
                ]);
            }
        };

        let topMenuService = {
            userLogout: function() {
                eventHandler.trigger("userLogout")
            },
            changeLanguage: function(newLang) {
                eventHandler.trigger("userChangeLanguage", [newLang]);
            },
            uploadWithPreview: function(form, uploadingUid, fdrId, fdrName, calibrationId) {
                eventHandler.trigger("uploadWithPreview", [form, uploadingUid, fdrId, fdrName, calibrationId]);
            }
        };

        ReactDOM.render(
            <Provider store={ store }>
                <Flights
                    i18n={ i18n }
                    userLogin={ userLogin }
                    userLang={ userLang }
                    avaliableLanguages={ avaliableLanguages }
                    flightsServise={ flightsServise }
                    topMenuService={ topMenuService }
                />
            </Provider>,
            wsp.get(0)
        );
    });

    eventHandler.on("resizeShowcase", function (e) {
        W.ResizeShowcase(e);
    });

    eventHandler.on("uploadWithPreview", function (e, form, uploadingUid, fdrId, fdrName, calibrationId) {
        var showcase = W.NewShowcase();
        FU.FillFactoryContaider(showcase, form, uploadingUid, fdrId, fdrName, calibrationId);
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

    eventHandler.on("flightListShow", function (e, someshowcase) {
        if (someshowcase === null) {
            W.RemoveShowcases(1);
            someshowcase = W.NewShowcase();
        } else {
            W.ClearShowcase(someshowcase);
        }

        FL.FillFactoryContaider(someshowcase);
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

    eventHandler.on("resultsLeftMenuRow", function (e, showcase) {
        if (showcase === null) {
            W.RemoveShowcases(1);
            showcase = W.NewShowcase();
        } else {
            W.ClearShowcase(showcase);
        }

        ReactDOM.render(
            <Provider store={store}>
                <Results i18n={i18n} />
            </Provider>,
            showcase.get(0)
        );
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

    eventHandler.on("calibrationFormShow", function (e, showcase) {
        if (showcase === null) {
            W.RemoveShowcases(1);
            showcase = W.NewShowcase();
        } else {
            W.ClearShowcase(showcase);
        }

        CLB.FillFactoryContaider(showcase);
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
