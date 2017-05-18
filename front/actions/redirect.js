import { push } from 'react-router-redux'

export default function redirect(payload) {
    return function(dispatch) {
        dispatch(push(payload));
    }
};
