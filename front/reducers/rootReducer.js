import { combineReducers } from 'redux';
import flightFilter from 'reducers/flightFilter';
import settlementFilter from 'reducers/settlementFilter';
import settlementsReport from 'reducers/settlementsReport';
import chosenFlightListItems from 'reducers/chosenFlightListItems';
import fdrTypesList from 'reducers/fdrTypesList';
import flightUploader from 'reducers/flightUploader';
import flightUploadingState from 'reducers/flightUploadingState';
import userOptions from 'reducers/userOptions';

export default combineReducers({
    fdrTypesList,
    flightUploader,
    flightUploadingState,
    flightFilter,
    settlementFilter,
    settlementsReport,
    chosenFlightListItems,
    userOptions
});
