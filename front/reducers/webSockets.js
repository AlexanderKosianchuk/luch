const initialState = {
    status: null,
    io: null
};

const MAX_POINT_COUNT = 100;

export default function webSockets(state = initialState, action) {
    switch (action.type) {
        case 'WEBSOCKETS_BINDED':
            return {
                status: true,
                io: action.payload.io
            };
        default:
            return state;
    }
}
