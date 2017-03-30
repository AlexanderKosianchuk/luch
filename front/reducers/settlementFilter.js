const initialState = {
    avaliableSettlements: {},
    chosenSettlements: {}
};

export default function settlementFilter(state = initialState, action) {
    switch (action.type) {
        case 'SETTLEMENTS_FETCHED':
            state.avaliableSettlements = action.payload;
            state.chosenSettlements = action.payload;
            return { ...state };
        case 'CHANGE_SETTLEMENT_ITEM_CHECKSTATE':
            return { ...state };
        default:
            return state;
    }
}
