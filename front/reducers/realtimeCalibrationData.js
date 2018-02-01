const initialState = {
  status: null,
  currentFrame: 0,
  phisics: [],
  binary: [],
  events: [],
  errorCode: null
};

const MAX_POINT_COUNT = 100;

export default function realtimeCalibrationData(state = initialState, action) {
  switch (action.type) {
    case 'POST_REALTIME_CALIBRATION_RECEIVING_COMPLETE':
      return { ...state,
        ...{ status: true }
      };
    case 'POST_REALTIME_CALIBRATION_FREEZE_COMPLETE':
      return { ...state,
        ...{ status: false }
      };
    case 'POST_REALTIME_CALIBRATION_BREAK_COMPLETE':
      return { ...state,
        ...{ status: null }
      };
    case 'RECEIVED_REALTIME_CALIBRATING_NEW_FRAME':
      let data = state.data;
      debugger;
      data.push(action.payload.resp.data);

      if (state.data.length > MAX_POINT_COUNT) {
        data = state.data.splice(1, state.data.length - 1);
      }

      return { ...state, ...{
          status: true,
          data: data,
          currentFrame: ++state.currentFrame,
        }
      };
    default:
      return state;
  }
}
