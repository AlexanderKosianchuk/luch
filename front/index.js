/*jslint browser: true*/
/*global $, jQuery*/
/*global Language, WindowFactory, FlightList, FlightUploader*/
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
import 'blueimp-file-upload';
import 'jstree';
import 'flot-charts';
import 'flot-charts/jquery.flot.time';
import 'flot-charts/jquery.flot.symbol';
import 'flot-charts/jquery.flot.navigate';
import 'flot-charts/jquery.flot.resize';
import 'datatables';
import 'bootstrap-loader';

// lib styles
import 'jquery-ui/themes/base/all.css';
import 'jstree/dist/themes/default/style.min.css';
import 'blueimp-file-upload/css/jquery.fileupload.css';
import 'blueimp-file-upload/css/jquery.fileupload-ui.css';
import 'colorpicker-amin/jquery.colorpicker.css';

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

// libs with export
import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { Route } from 'react-router';
import { ConnectedRouter } from 'react-router-redux';
import createHistory from 'history/createBrowserHistory'
import { routerMiddleware } from 'react-router-redux';

// old prototypes
import Language from "Language";
import WindowFactory from "WindowFactory";
import FlightList from "FlightList";
import FlightUploader from "FlightUploader";
import FlightViewOptions from "FlightViewOptions";
import Fdr from "Fdr";
import Chart from "Chart";
import User from "User";
import SearchFlight from "SearchFlight";
import Calibration from "Calibration";

// react implementation
import Results from 'components/results/Results';
import Flights from 'components/flights/Flights';
import UserOptions from 'components/user-options/UserOptions';
import configureStore from 'store/configureStore';

import reportFlightUploadingProgressAction from 'actions/reportFlightUploadingProgress';
import startFlightUploadingAction from 'actions/startFlightUploading';
import completeFlightUploadingAction from 'actions/completeFlightUploading';

const history = createHistory();
const routerMiddlewareInstance = routerMiddleware(history);
const store = configureStore({}, routerMiddlewareInstance);

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
                eventHandler.trigger("userLogout");
            },
            userOptionsShow: function() {
                eventHandler.trigger("userOptionsShow", [
                    $('#flightsContainer')
                ]);
            },
            changeLanguage: function(newLang) {
                eventHandler.trigger("userChangeLanguage", [newLang]);
            },
            uploadWithPreview: function(form, uploadingUid, fdrId, fdrName, calibrationId) {
                eventHandler.trigger("uploadWithPreview", [form, uploadingUid, fdrId, fdrName, calibrationId]);
            },
            easyUploading: function(uploadingUid, fdrId, calibrationId) {
                eventHandler.trigger("easyUploading", [uploadingUid, fdrId, calibrationId]);
            },
            importItem: function(form) {
                eventHandler.trigger("importItem", [form]);
            }
        };

debugger;
        ReactDOM.render(
            <Provider store={ store }>
                <ConnectedRouter history={ history }>
                  <div>
                    <Route exact path="/" component={ Flights } />
                  </div>
                </ConnectedRouter>
            </Provider>,
            wsp.get(0)
        );

        let currentValue;
        function select(state) {
            return state.flightUploadingState.length;
        }

        store.subscribe(() => {
            let previousValue = currentValue;
             currentValue = select(store.getState())

             if ((currentValue === 0)
                && (previousValue > 0)
            ) {
                eventHandler.trigger("flightListShow", [
                    $('#flightsContainer')
                ]);
             }
        });
    });

    eventHandler.on("resizeShowcase", function (e) {
        W.ResizeShowcase(e);
    });

    eventHandler.on("uploadWithPreview", function (e, form, uploadingUid, fdrId, fdrName, calibrationId) {
        var showcase = W.NewShowcase();
        FU.FillFactoryContaider(showcase, form, uploadingUid, fdrId, fdrName, calibrationId);
    });

    eventHandler.on("importItem", function (e, form) {
        let dfd = $.Deferred();
        FU.Import(form, dfd);
        dfd.promise();

        dfd.then(
            () => {
                if ($('#flightsContainer')) {
                    eventHandler.trigger("flightListShow", [
                        $('#flightsContainer')
                    ]);
                    return this;
                }

                location.reload();
            }
        );
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

    eventHandler.on("startProccessing", function (e, uploadingUid) {
        store.dispatch(startFlightUploadingAction({
            uploadingUid: uploadingUid
        }));
    });

    eventHandler.on("endProccessing", function (e, uploadingUid) {
        store.dispatch(() => () => {
            dispatch({
                type: 'FLIGHT_UPLOADING_COMPLETE',
                payload: {
                    uploadingUid: uploadingUid
                }
            });
        });
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

    eventHandler.on("userOptionsShow", function (e, showcase) {
        if (showcase === null) {
            W.RemoveShowcases(1);
            showcase = W.NewShowcase();
        } else {
            W.ClearShowcase(showcase);
        }

        ReactDOM.render(
            <Provider store={store}>
                <UserOptions i18n={i18n} />
            </Provider>,
            showcase.get(0)
        );
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
