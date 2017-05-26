/*jslint browser: true*/

'use strict';

import 'jquery';
import facade from 'facade';
// libs with export
import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { Route } from 'react-router';
import createHistory from 'history/createBrowserHistory'
import { ConnectedRouter, routerMiddleware, routerActions, push } from 'react-router-redux';
import { setLocale, loadTranslations, syncTranslationWithStore } from 'react-redux-i18n';
import { UserAuthWrapper } from 'redux-auth-wrapper';

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

import translationsEn from 'translations/translationsEn';
import translationsEs from 'translations/translationsEs';
import translationsRu from 'translations/translationsRu';

const translationsObject = {...translationsEn, ...translationsEs, ...translationsRu};
const history = createHistory({ queryKey: false });
const routerMiddlewareInstance = routerMiddleware(history);
const store = configureStore({}, routerMiddlewareInstance);

store.dispatch(loadTranslations(translationsObject));
store.dispatch(setLocale('ru'));

facade(store);

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
