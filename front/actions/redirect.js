import { push, replace } from 'react-router-redux'

export default function redirect(payload, useReplace = false) {
  return function(dispatch) {
    if (useReplace) {
      dispatch(replace(payload));
      return;
    }

    dispatch(push(payload));
  }
};
