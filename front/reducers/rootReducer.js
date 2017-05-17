import { combineReducers } from 'redux';
import { routerReducer } from 'react-router-redux';
import { i18nReducer } from 'react-redux-i18n';

import flightFilter from 'reducers/flightFilter';
import settlementFilter from 'reducers/settlementFilter';
import settlementsReport from 'reducers/settlementsReport';
import chosenFlightListItems from 'reducers/chosenFlightListItems';
import fdrTypesList from 'reducers/fdrTypesList';
import flightUploader from 'reducers/flightUploader';
import flightUploadingState from 'reducers/flightUploadingState';
import userOptions from 'reducers/userOptions';
import userReducer from 'reducers/userReducer'

export default combineReducers({
    fdrTypesList,
    flightUploader,
    flightUploadingState,
    flightFilter,
    settlementFilter,
    settlementsReport,
    chosenFlightListItems,
    userOptions,
    router: routerReducer,
    i18n: i18nReducer,
    user: userReducer
});
