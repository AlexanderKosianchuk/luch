import { combineReducers } from 'redux';
import flightFilter from 'reducers/flightFilter';
import settlementFilter from 'reducers/settlementFilter';
import chosenFlightListItems from 'reducers/chosenFlightListItems';
import fdrTypesList from 'reducers/fdrTypesList';
import flightUploader from 'reducers/flightUploader';

export default combineReducers({
    fdrTypesList,
    flightUploader,
    flightFilter,
    settlementFilter,
    chosenFlightListItems
});
