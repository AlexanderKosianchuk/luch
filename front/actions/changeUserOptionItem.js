export default function changeUserOptionItem(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_USER_OPTION_ITEM',
            payload: payload
        });
    }
};
