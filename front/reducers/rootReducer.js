import { combineReducers } from 'redux';
import flightFilter from 'reducers/flightFilter';
import settlementFilter from 'reducers/settlementFilter'

export default combineReducers({
    flightFilter,
    settlementFilter
});
