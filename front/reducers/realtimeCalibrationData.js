const initialState = {
  status: null,
  currentFrame: 0,
  data: [],
  errorCode: null
};

const MAX_POINT_COUNT = 100;

export default function realtimeCalibrationData(state = initialState, action) {
  switch (action.type) {
    case 'CHANGE_REALTIME_CALIBRATING_STATUS':
      return { ...state,
        ...{ status: action.payload.status }
      };
    case 'RECEIVED_REALTIME_CALIBRATING_NEW_FRAME':
      let data = state.data;
      data.push(action.payload.data);

      if (state.data.length > MAX_POINT_COUNT) {
        data = state.data.splice(1, state.data.length - 1);
      }

      return { ...state, ...{
          status: 'onAir',
          data: data,
          currentFrame: ++state.currentFrame,
        }
      };
    default:
      return state;
  }
}
