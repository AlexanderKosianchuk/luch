const initialState = {
  status: null,
  currentFrame: 0,
  timeline: [],
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
      if (!check(action.payload.resp.phisics, 'number')) {
        return state;
      }

      state.timeline.push(state.currentFrame);

      state.phisics.push(action.payload.resp.phisics);

      if (check(action.payload.resp.binary, 'object')) {
        state.binary.push(action.payload.resp.binary);
      } else {
        state.binary.push([]);
      }

      if (check(action.payload.resp.events, 'object')) {
        state.events.push(action.payload.resp.events);
      } else {
        state.events.push([]);
      }

      state.voiceStreams = action.payload.resp.voiceStreams;

      if (state.timeline.length > MAX_POINT_COUNT) {
        state.timeline = state.timeline.splice(1, state.timeline.length - 1);
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

function check(item, type) {
  if ((typeof(item) === 'object')
    && (Object.keys(item).length > 0)
    && typeof(item[Object.keys(item)[0]]) === type
  ) {
    return true;
  }

  return false;
}
