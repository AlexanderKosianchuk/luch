export default function chooseFlightTemplate(payload) {
    return function(dispatch) {
        if (payload.checkstate === 'checked') {
            dispatch({
                type: 'TEMPLATE_CHOSEN',
                payload: payload
            });
        } else if (payload.checkstate === '') {
            dispatch({
                type: 'TEMPLATE_UNCHOSEN',
                payload: payload
            });
        }
    }
};
