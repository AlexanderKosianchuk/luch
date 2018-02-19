const initialState = {
  status: null,
  currentFrame: 0,
  phisics: [],
  binary: [],
  events: [],
  voiceStreams: [],
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
      state.phisics.push(action.payload.resp.phisics);
      state.binary.push(action.payload.resp.binary);
      state.events.push(action.payload.resp.events);
      state.voiceStreams = action.payload.resp.voiceStreams;

      if (state.phisics.length > MAX_POINT_COUNT) {
        state.phisics = state.phisics.splice(1, state.phisics.length - 1);
        state.binary = state.binary.splice(1, state.binary.length - 1);
        state.events = state.events.splice(1, state.events.length - 1);
      }

      return { ...state, ...{
          currentFrame: ++state.currentFrame,
        }
      };
    default:
      return state;
  }
}
