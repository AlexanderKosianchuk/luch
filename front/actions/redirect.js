import { push } from 'react-router-redux'

export default function redirect(payload, replace = false) {
  return function(dispatch) {
    if (replace) {
      dispatch(replace(payload));
      return;
    }

    dispatch(push(payload));
  }
};
