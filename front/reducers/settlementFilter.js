const initialState = {
    receiving: null,
    avaliableSettlements: [],
    chosenSettlements: []
};

export default function settlementFilter(state = initialState, action) {
    switch (action.type) {
        case 'APPLY_FLIGHT_FILTER':
            state.receiving = true;
            return { ...state };
        case 'SETTLEMENTS_FETCHED':
            return { ... Object.assign(state, {
                receiving: false,
                avaliableSettlements: action.payload.slice(), // copy array
                chosenSettlements: action.payload.slice()
            })};
        case 'CHANGE_SETTLEMENT_ITEM_CHECKSTATE':
            let getIndexById = function (id, array) {
                let itemIndex = null;
                array.forEach((item, index) => {
                    if (item.id === id) itemIndex = index;
                });

                return itemIndex; // or undefined
            }

            let itemIndex = getIndexById(action.payload.id, state.chosenSettlements);

            if ((action.payload.state === false)
                && (itemIndex !== null)
            ) {
                state.chosenSettlements.splice(itemIndex, 1);
            }

            if ((action.payload.state === true)
                && (itemIndex === null)
            ) {
                let getItemById = function(id, array) {
                    var result  = array.filter((o) => o.id == id);

                    return result ? result[0] : null; // or undefined
                }

                let item = getItemById(action.payload.id, state.avaliableSettlements);
                state.chosenSettlements.push(item);
            }

            return { ...state };
        default:
            return state;
    }
}
