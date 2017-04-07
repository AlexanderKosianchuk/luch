import { combineReducers } from 'redux';
import flightFilter from 'reducers/flightFilter';
import settlementFilter from 'reducers/settlementFilter';
import chosenFlightListItems from 'reducers/chosenFlightListItems';
import fdrTypesList from 'reducers/fdrTypesList';

export default combineReducers({
    fdrTypesList,
    flightFilter,
    settlementFilter,
    chosenFlightListItems
});
