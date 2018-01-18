import socketIOClient from 'socket.io-client';
import sailsIOClient from 'sails.io.js';
import { bindActionCreators } from 'redux';

import requestAction from 'actions/request';

export default function bindSocket(payload) {
  return function(dispatch) {
    let request = bindActionCreators(requestAction, dispatch);

    dispatch({
      type: 'WEBSOCKET_BINDING'
    });

    let io = sailsIOClient(socketIOClient);

    io.sails.url = payload.interactionUrl;
    io.sails.reconnection = true;

    // workaround till not shure interaction always up
    request(
      ['interaction', 'up'],
      'get'
    ).then(() => {
      io.socket.on('connect', () => {
        dispatch({
          type: 'WEBSOCKET_CONNECTED',
          payload: {
            io: io
          }
        });
      });
    });

    //TODO: on error
  }
};
