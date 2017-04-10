export default function changeSettlementItemCheckstate(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_SETTLEMENT_ITEM_CHECKSTATE'
        });
    }
};
