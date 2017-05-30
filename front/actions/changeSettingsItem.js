export default function changeSettingsItem(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_SETTINGS_ITEM',
            payload: payload
        });
    }
};
