import { combineReducers } from 'redux';
import { routerReducer } from 'react-router-redux';
import { i18nReducer } from 'react-redux-i18n';

import flightFilter from 'reducers/flightFilter';
import settlementFilter from 'reducers/settlementFilter';
import settlementsReport from 'reducers/settlementsReport';
import fdrs from 'reducers/fdrs';
import flights from 'reducers/flights';
import folders from 'reducers/folders';
import flight from 'reducers/flight';
import fdrCyclo from 'reducers/fdrCyclo';
import flightUploader from 'reducers/flightUploader';
import flightUploadingState from 'reducers/flightUploadingState';
import settings from 'reducers/settings';
import template from 'reducers/template';
import flightTemplates from 'reducers/flightTemplates';
import flightEvents from 'reducers/flightEvents';
import userReducer from 'reducers/userReducer'

export default combineReducers({
    fdrs,
    flight,
    flights,
    folders,
    fdrCyclo,
    flightUploader,
    flightUploadingState,
    flightFilter,
    template,
    flightTemplates,
    flightEvents,
    settlementFilter,
    settlementsReport,
    settings,
    router: routerReducer,
    i18n: i18nReducer,
    user: userReducer
});
