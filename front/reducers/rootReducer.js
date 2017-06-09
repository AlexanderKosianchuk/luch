import { combineReducers } from 'redux';
import { routerReducer } from 'react-router-redux';
import { i18nReducer } from 'react-redux-i18n';

import flightFilter from 'reducers/flightFilter';
import settlementFilter from 'reducers/settlementFilter';
import settlementsReport from 'reducers/settlementsReport';
import chosenFlightListItems from 'reducers/chosenFlightListItems';
import fdrTypesList from 'reducers/fdrTypesList';
import flightInfo from 'reducers/flightInfo';
import fdrCyclo from 'reducers/fdrCyclo';
import flightUploader from 'reducers/flightUploader';
import flightUploadingState from 'reducers/flightUploadingState';
import settings from 'reducers/settings';
import templateInfo from 'reducers/templateInfo';
import templatesList from 'reducers/templatesList';
import flightEvents from 'reducers/flightEvents';
import chosenTemplates from 'reducers/chosenTemplates';
import userReducer from 'reducers/userReducer'

export default combineReducers({
    fdrTypesList,
    flightInfo,
    fdrCyclo,
    flightUploader,
    flightUploadingState,
    flightFilter,
    templateInfo,
    templatesList,
    flightEvents,
    chosenTemplates,
    settlementFilter,
    settlementsReport,
    chosenFlightListItems,
    settings,
    router: routerReducer,
    i18n: i18nReducer,
    user: userReducer
});
