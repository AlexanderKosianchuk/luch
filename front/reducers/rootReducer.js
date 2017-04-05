import { combineReducers } from 'redux';
import flightFilter from 'reducers/flightFilter';
import settlementFilter from 'reducers/settlementFilter'
import chosenFlightListItems from 'reducers/chosenFlightListItems'

export default combineReducers({
    flightFilter,
    settlementFilter,
    chosenFlightListItems
});
