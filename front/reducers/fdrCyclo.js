const initialState = {
    pending: null,
    analogParams: [],
    binaryParams: [],
    chosenAnalogParams: [],
    chosenBinaryParams: []
};

export default function fdrCyclo(state = initialState, action) {
    switch (action.type) {
        case 'FDR_CYCLO_PENDING':
            return { ...state, ...{ pending: true }};
        case 'FDR_CYCLO_RECEIVED':
            return { ...state,
                ...{
                    pending: false,
                    analogParams: action.payload.analogParams,
                    binaryParams: action.payload.binaryParams,
                }
            };
        case 'CHANGE_FLIGHT_PARAM_CHECKSTATE':
            let getIndexById = function (id, array) {
                let itemIndex = null;
                array.forEach((item, index) => {
                    if (item.id === id) itemIndex = index;
                });

                return itemIndex; // or undefined
            }

            let chosenParams = [];
            if (action.payload.paramType === 'ap') {
                chosenParams = state.chosenAnalogParams;
            } else if (action.payload.paramType === 'bp') {
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
                    paramType: action.payload.paramType
                });
            }

            return { ...state };
        default:
            return state;
    }
}
