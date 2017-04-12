import { combineReducers } from 'redux';
import flightFilter from 'reducers/flightFilter';
import settlementFilter from 'reducers/settlementFilter';
import chosenFlightListItems from 'reducers/chosenFlightListItems';
import fdrTypesList from 'reducers/fdrTypesList';
import flightUploader from 'reducers/flightUploader';
import flightUploadingState from 'reducers/flightUploadingState';

export default combineReducers({
    fdrTypesList,
    flightUploader,
    flightUploadingState,
    flightFilter,
    settlementFilter,
    chosenFlightListItems
});
