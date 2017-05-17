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
import { routerMiddleware, routerActions } from 'react-router-redux';
import { setLocale, loadTranslations, syncTranslationWithStore } from 'react-redux-i18n';
import { UserAuthWrapper } from 'redux-auth-wrapper';

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
import UserLogin from 'components/user-login/UserLogin';
import configureStore from 'store/configureStore';

import reportFlightUploadingProgressAction from 'actions/reportFlightUploadingProgress';
import startFlightUploadingAction from 'actions/startFlightUploading';
import completeFlightUploadingAction from 'actions/completeFlightUploading';

import translationsEn from 'translations/translationsEn';
import translationsEs from 'translations/translationsEs';
import translationsRu from 'translations/translationsRu';

const translationsObject = {...translationsEn, ...translationsEs, ...translationsRu};
const history = createHistory();
const routerMiddlewareInstance = routerMiddleware(history);
const store = configureStore({}, routerMiddlewareInstance);

store.dispatch(loadTranslations(translationsObject));
store.dispatch(setLocale('en'));

$(document).ready(function () {
    var i18n = null,
        $document = $(document),
        $window = $(window),
        userLang = $('html').attr("lang"),
        LA = new Language(userLang),
        W = new WindowFactory($window, $document),
        FU = null,
        FO = null,
        F = null,
        C = null,
        U = null,
        FL = null,
        SF = null,
        CLB = null;

    LA.GetLanguage().done(function (data) {
        var langStr = i18n = data;
        var wsp = W.NewShowcase();
        FL = new FlightList(langStr, store);
        FU = new FlightUploader(langStr);
        FO = new FlightViewOptions(langStr);
        F = new Fdr(langStr);
        C = new Chart(langStr);
        U = new User(langStr);
        SF = new SearchFlight(langStr);
        CLB = new Calibration(langStr);

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

        // Redirects to /login by default
        const UserIsAuthenticated = UserAuthWrapper({
            authSelector: state => state.user, // how to get the user state
            redirectAction: routerActions.replace, // the redux action to dispatch for redirect
            wrapperDisplayName: 'UserIsAuthenticated' // a nice name for this auth check
        });

        ReactDOM.render(
            <Provider store={ store }>
                <ConnectedRouter history={ history }>
                  <div>
                    <Route exact path="/" component={ UserIsAuthenticated(Flights) } />
                    <Route exact path="/login" component={ UserLogin } />
                  </div>
                </ConnectedRouter>
            </Provider>,
            wsp.get(0)
        );

        let currentFlightUploadingStateValue;
        function selectFlightUploadingState(state) {
            return state.flightUploadingState.length;
        }

        store.subscribe(() => {
            let previousFlightUploadingStateValue = currentFlightUploadingStateValue;
             currentFlightUploadingStateValue = selectFlightUploadingState(store.getState())

             if ((currentFlightUploadingStateValue === 0)
                && (previousFlightUploadingStateValue > 0)
             ) {
                $(document).trigger("flightListShow", [
                    $('#flightsContainer')
                ]);
             }
        });
    });

    $(document).on("resizeShowcase", function (e) {
        W.ResizeShowcase(e);
    });

    $(document).on("uploadWithPreview", function (e, form, uploadingUid, fdrId, fdrName, calibrationId) {
        var showcase = W.NewShowcase();
        FU.FillFactoryContaider(showcase, form, uploadingUid, fdrId, fdrName, calibrationId);
    });

    $(document).on("importItem", function (e, form) {
        let dfd = $.Deferred();
        FU.Import(form, dfd);
        dfd.promise();

        dfd.then(
            () => {
                if ($('#flightsContainer')) {
                    $(document).trigger("flightListShow", [
                        $('#flightsContainer')
                    ]);
                    return this;
                }

                location.reload();
            }
        );
    });


    $(document).on("removeShowcase", function (e, data, callback) {
        var flightUploaderFactoryContainer = data;
        W.RemoveShowcase(flightUploaderFactoryContainer);

        if ($.isFunction(callback)) {
            callback();
        }
    });

    ///=======================================================
    //FlightList
    ///

    $(document).on("startProccessing", function (e, uploadingUid) {
        store.dispatch(startFlightUploadingAction({
            uploadingUid: uploadingUid
        }));
    });

    $(document).on("endProccessing", function (e, uploadingUid) {
        store.dispatch(() => () => {
            dispatch({
                type: 'FLIGHT_UPLOADING_COMPLETE',
                payload: {
                    uploadingUid: uploadingUid
                }
            });
        });
    });

    $(document).on("convertSelectedClicked", function (e) {
        W.RemoveShowcases(1);

        if (FL !== null) {
            FL.ShowFlightsByPath();
        }
    });

    $(document).on("flightListShow", function (e, someshowcase) {
        if (someshowcase === null) {
            W.RemoveShowcases(1);
            someshowcase = W.NewShowcase();
        } else {
            W.ClearShowcase(someshowcase);
        }

        FL.FillFactoryContaider(someshowcase);
    });

    $(document).on("userOptionsShow", function (e, showcase) {
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

    $(document).on("viewFlightOptions", function (e, flightId, task, someshowcase) {
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

    $(document).on("showBruTypeEditingForm", function (e, bruTypeId, task, showcase) {
        if (showcase === null) {
            W.RemoveShowcases(1);
            showcase = W.NewShowcase();
        } else {
            W.ClearShowcase(showcase);
        }

        if (bruTypeId !== null) {
            F.bruTypeId = bruTypeId;
        }

        if (task !== null) {
            F.task = task;
        }

        Fdr.FillFactoryContaider(showcase);
    });

    $(document).on("resultsLeftMenuRow", function (e, showcase) {
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

    $(document).on("userLogout", function (e) {
        U.logout();
    });

    $(document).on("userChangeLanguage", function (e, lang) {
        U.changeLanguage(lang);
    });

    $(document).on("userShowList", function (e, showcase) {
        if (showcase === null) {
            W.RemoveShowcases(1);
            showcase = W.NewShowcase();
        } else {
            W.ClearShowcase(showcase);
        }

        U.FillFactoryContaider(showcase);
    });

    $(document).on("flightSearchFormShow", function (e, showcase) {
        if (showcase === null) {
            W.RemoveShowcases(1);
            showcase = W.NewShowcase();
        } else {
            W.ClearShowcase(showcase);
        }

        SF.FillFactoryContaider(showcase);
    });

    $(document).on("calibrationFormShow", function (e, showcase) {
        if (showcase === null) {
            W.RemoveShowcases(1);
            showcase = W.NewShowcase();
        } else {
            W.ClearShowcase(showcase);
        }

        CLB.FillFactoryContaider(showcase);
    });

    $(document).on("showChart", function (e,
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

    $(document).on("saveChartTpl", function (e, flightId, tplName, saveChartTplCb) {
        Fdr.copyTemplate(flightId, tplName).then(saveChartTplCb);
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
