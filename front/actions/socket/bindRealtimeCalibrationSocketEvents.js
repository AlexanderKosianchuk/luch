import socketIOClient from 'socket.io-client';
import sailsIOClient from 'sails.io.js';

export default function bindRealtimeCalibrationSocketEvents(payload) {
    return function(dispatch) {
        dispatch({
            type: 'CHANGE_REALTIME_CALIBRATING_STATUS',
            payload: {
                status: 'bindingSocket'
            }
        });

        let io = sailsIOClient(socketIOClient);

        io.sails.url = payload.interactionUrl;
        io.sails.reconnection = true;

        io.socket.on('connect', () => {
            io.socket.get(
                (payload.interactionUrl + '/realtimeCalibration/register')
            );

            dispatch({
                type: 'CHANGE_REALTIME_CALIBRATING_STATUS',
                payload: {
                    status: 'waitingData'
                }
            });
        });

        io.socket.on('newData', (resp) => {
            dispatch({
                type: 'RECEIVED_REALTIME_CALIBRATING_NEW_FRAME',
                payload: {
                    data: resp.data
                }
            });
        });
    }
};
