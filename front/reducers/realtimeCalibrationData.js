const initialState = {
  status: null,
  previousFrame: -1,
  currentFrame: 0,
  timeline: [],
  fullTimeline: [],
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
      state.fullTimeline.push(null);

      return { ...state,
        ...{ status: false }
      };
    case 'POST_REALTIME_CALIBRATION_BREAK_COMPLETE':
      return { ...state,
        ...{ status: null }
      };
    case 'RECEIVED_REALTIME_CALIBRATING_NEW_FRAME':
      if (!check(action.payload.response.phisics, 'number')) {
        return state;
      }

      if ((state.previousFrame + 1) !== state.currentFrame) {
        state.currentFrame = state.previousFrame + 1;
        state.timeline = [];
        state.phisics = [];
        state.binary = [];
        state.events = [];
      }

      state.timeline.push(action.payload.response.timestamp);
      state.fullTimeline.push(action.payload.response.timestamp);

      state.phisics.push(action.payload.response.phisics);

      if (check(action.payload.response.binary, 'object')) {
        state.binary.push(action.payload.response.binary);
      } else {
        state.binary.push([]);
      }

      if (check(action.payload.response.events, 'object')) {
        state.events.push(action.payload.response.events);
      } else {
        state.events.push([]);
      }

      state.voiceStreams = action.payload.response.voiceStreams;

      if (state.timeline.length > MAX_POINT_COUNT) {
        state.timeline = state.timeline.splice(1, state.timeline.length - 1);
        state.phisics = state.phisics.splice(1, state.phisics.length - 1);
        state.binary = state.binary.splice(1, state.binary.length - 1);
        state.events = state.events.splice(1, state.events.length - 1);
      }

      return { ...state, ...{
          currentFrame: state.currentFrame + 1,
          previousFrame: state.currentFrame
        }
      };
    case 'GET_REALTIME_CALIBRATION_SEGMENT_COMPLETE':
      return { ...state, ...{
        currentFrame: action.payload.response.currentFrame,
        timeline: action.payload.response.timeline,
        phisics: action.payload.response.phisics,
        binary: action.payload.response.binary,
        events: action.payload.response.events,
      }}
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
