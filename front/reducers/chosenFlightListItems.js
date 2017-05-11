const initialState = {
    selectedFlights: [],
    selectedFolders: [],
};

export default function chosenFlightListItems(state = initialState, action) {
    switch (action.type) {
        case 'FLIGHT_LIST_CHANGE_CHECKSTATE':
            return { ...state, ...action.payload }
        default:
            return state;
    }
}
