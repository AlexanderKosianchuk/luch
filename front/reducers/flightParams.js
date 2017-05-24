const initialState = {
    pending: null,
    chosenAnalogParams: [],
    chosenBinaryParams: []
};

export default function flightParams(state = initialState, action) {
    switch (action.type) {
        case 'CHANGE_FLIGHT_PARAM_CHECKSTATE':
            let getIndexById = function (id, array) {
                let itemIndex = null;
                array.forEach((item, index) => {
                    if (item.id === id) itemIndex = index;
                });

                return itemIndex; // or undefined
            }

            let chosenParams = [];
            if (action.payload.type === 'a') {
                chosenParams = state.chosenAnalogParams;
            } else if (action.payload.type === 'b') {
                chosenParams = state.chosenBinaryParams;
            }

            let itemIndex = getIndexById (
                action.payload.id,
                chosenParams
            );

            if ((action.payload.state === false)
                && (itemIndex !== null)
            ) {
                chosenParams.splice(itemIndex, 1);
            }

            if ((action.payload.state === true)
                && (itemIndex === null)
            ) {
                chosenParams.push({
                    id: action.payload.id,
                    type: action.payload.type
                });
            }

            return { ...state };
        default:
            return state;
    }
}
