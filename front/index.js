/*jslint browser: true*/
/*global $, jQuery*/
/*global Language, WindowFactory, FlightList, FlightUploader*/
/*global FlightViewOptions, Fdr, Chart, User, SearchFlight*/

'use strict';

// libs
import 'jquery';
import 'jquery-ui';
import 'jquery-ui/ui/widgets/button';
import 'jquery-ui/ui/widgets/menu';
import 'colorpicker-amin';
import 'jstree';
import 'flot-charts';
import 'flot-charts/jquery.flot.time';
import 'flot-charts/jquery.flot.symbol';
import 'flot-charts/jquery.flot.navigate';
import 'flot-charts/jquery.flot.resize';
import 'flot-charts/jquery.flot.crosshair';
import 'datatables';
import 'bootstrap-loader';

// lib styles
import 'jquery-ui/themes/base/all.css';
import 'jstree/dist/themes/default/style.min.css';
import 'colorpicker-amin/jquery.colorpicker.css';

//old styles
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
import FlightList from 'FlightList';
import FlightUploader from 'FlightUploader';
import FlightViewOptions from 'FlightViewOptions';
import ChartService from 'Chart';
import User from 'User';
import SearchFlight from 'SearchFlight';
import Calibration from 'Calibration';

// react implementation
import Results from 'components/results/Results';
import Flights from 'components/flights/Flights';
import UserOptions from 'components/user-options/UserOptions';
import UserLogin from 'components/user-login/UserLogin';
import FlightsSearch from 'components/flights-search/FlightsSearch';
import Calibrations from 'components/calibrations/Calibrations';
import Users from 'components/users/Users';
import FlightEvents from 'components/flight-events/FlightEvents';
import FlightTemplates from 'components/flight-templates/FlightTemplates';
import FlightParams from 'components/flight-params/FlightParams';
import UploadingPreview from 'components/uploading-preview/UploadingPreview';
import Chart from 'components/chart/Chart';
import configureStore from 'store/configureStore';

import startFlightUploading from 'actions/startFlightUploading';

import translationsEn from 'translations/translationsEn';
import translationsEs from 'translations/translationsEs';
import translationsRu from 'translations/translationsRu';

const translationsObject = {...translationsEn, ...translationsEs, ...translationsRu};
const history = createHistory({ queryKey: false });
const routerMiddlewareInstance = routerMiddleware(history);
const store = configureStore({}, routerMiddlewareInstance);

store.dispatch(loadTranslations(translationsObject));
store.dispatch(setLocale('ru'));

function refreshFlightsList() {
    function getCurrentRoute(state) {
        return state.router.location.pathname;
    }

    let currentLocation = getCurrentRoute(store.getState())

    if ((currentLocation === '/')
        || (currentLocation === '/#')
        || (currentLocation.indexOf('flights/tree') > -1)
    ) {
        $(document).trigger('flightsTreeShow', [
            $('#container')
        ]);
    }

    if (currentLocation.indexOf('flights/table') > -1) {
        $(document).trigger('flightsTableShow', [
            $('#container')
        ]);
    }
}

let currentFlightUploadingStateValue;
store.subscribe(() => {
    function getUploadingState(state) {
        return state.flightUploadingState.length;
    }

    let previousFlightUploadingStateValue = currentFlightUploadingStateValue;
     currentFlightUploadingStateValue = getUploadingState(store.getState())

    if ((currentFlightUploadingStateValue === 0)
        && (previousFlightUploadingStateValue > 0)
    ) {
        refreshFlightsList();
    }
});

$(document).on('importItem', function (e, form) {
    let dfd = $.Deferred();
    let FU = new FlightUploader(store);
    FU.Import(form, dfd);
    dfd.promise();

    dfd.then(() => {
        if ($('#container')) {
            refreshFlightsList();
        }
    });
});

$(document).on('uploadWithPreview', function (e, showcase, uploadingUid, fdrId, calibrationId) {
    let FU = new FlightUploader(store);
    FU.FillFactoryContaider(showcase, uploadingUid, fdrId, calibrationId);
});

$(document).on('startProccessing', function (e, uploadingUid) {
    store.dispatch(startFlightUploading({
        uploadingUid: uploadingUid
    }));
});

$(document).on('endProccessing', function (e, uploadingUid) {
    store.dispatch(() => () => {
        dispatch({
            type: 'FLIGHT_UPLOADING_COMPLETE',
            payload: {
                uploadingUid: uploadingUid
            }
        });
    });
});

$(document).on('flightsTreeShow', function (e, someshowcase) {
    let FL = new FlightList(store);
    FL.FillFactoryContaider(someshowcase);
});

$(document).on('flightsTableShow', function (e, someshowcase) {
    let FL = new FlightList(store);
    FL.setView('table');
    FL.FillFactoryContaider(someshowcase);
});

$(document).on('flightEvents', function (e, someshowcase, flightId) {
    let FO = new FlightViewOptions(store);
    FO.task = 'getEventsList';
    FO.flightId = flightId;
    FO.FillFactoryContaider(someshowcase);
});

$(document).on('flightParams', function (e, someshowcase, flightId) {
    let FO = new FlightViewOptions(store);
    FO.task = 'getParamList';
    FO.flightId = flightId;
    FO.FillFactoryContaider(someshowcase);
});

$(document).on('chartShow', function (
    e, showcase,
    flightId, tplName,
    stepLength, startCopyTime,
    startFrame, endFrame,
    apParams, bpParams
) {
    var C = new ChartService(store);
    C.SetChartData(
        flightId, tplName,
        stepLength, startCopyTime,
        startFrame, endFrame,
        apParams, bpParams
    );
    C.FillFactoryContaider(showcase);
});

$(document).on('userShowList', function (e, showcase) {
    let U = new User(store);
    U.FillFactoryContaider(showcase);
});

$(document).on('changeLanguage', function (e, newLanguage) {
    let U = new User(store);
    U.changeLanguage(newLanguage);
});

$(document).on('flightSearchFormShow', function (e, showcase) {
    let SF = new SearchFlight(store);
    SF.FillFactoryContaider(showcase);
});

$(document).on('calibrationsShow', function (e, showcase) {
    let CLB = new Calibration(store);
    CLB.FillFactoryContaider(showcase);
});

let topMenuService = {
    uploadWithPreview: function(form, uploadingUid, fdrId, fdrName, calibrationId) {
        eventHandler.trigger('uploadWithPreview', [form, uploadingUid, fdrId, fdrName, calibrationId]);
    },
};

// Redirects to /login by default
const UserIsAuthenticated = UserAuthWrapper({
    authSelector: state => state.user, // how to get the user state
    redirectAction: routerActions.replace, // the redux action to dispatch for redirect
    wrapperDisplayName: 'UserIsAuthenticated' // a nice name for this auth check
});

$(document).ready(function () {
    if (($('html').attr('login') !== '')
        && ($('html').attr('lang') !== '')
    ) {
        let login = $('html').attr('login');
        let lang = $('html').attr('lang');
        store.dispatch({
            type: 'USER_LOGGED_IN',
            payload: {
                login: login,
                lang: lang
            }
        });
        store.dispatch(setLocale(lang.toLowerCase()));
    }

    ReactDOM.render(
        <Provider store={ store }>
            <ConnectedRouter history={ history }>
              <div>
                <Route exact path='/login' component={ UserLogin } />
                <Route exact path='/' component={ UserIsAuthenticated(Flights) } />
                <Route exact path='/flights/:viewType' component={ UserIsAuthenticated(Flights) } />
                <Route exact path='/user-options' component={ UserIsAuthenticated(UserOptions) } />
                <Route exact path='/flights-search' component={ UserIsAuthenticated(FlightsSearch) } />
                <Route exact path='/results' component={ UserIsAuthenticated(Results) } />
                <Route exact path='/calibrations' component={ UserIsAuthenticated(Calibrations) } />
                <Route exact path='/users' component={ UserIsAuthenticated(Users) } />
                <Route path='/flight-events/:id' component={ UserIsAuthenticated(FlightEvents) } />
                <Route path='/flight-templates/:id' component={ UserIsAuthenticated(FlightTemplates) } />
                <Route path='/flight-params/:id' component={ UserIsAuthenticated(FlightParams) } />
                <Route path='/uploading/:uploadingUid/fdr-id/:fdrId' /*calibration-id/:calibrationId possible*/
                    component={ UserIsAuthenticated(UploadingPreview) }
                />
                <Route path='/chart/flight-id/:id/template-name/:templateName/from-frame/:fromFrame/to-frame/:toFrame'
                    component={ UserIsAuthenticated(Chart) }
                />
              </div>
            </ConnectedRouter>
        </Provider>,
        document.getElementById('root')
    );
});
