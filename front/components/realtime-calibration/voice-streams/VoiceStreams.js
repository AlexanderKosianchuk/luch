import './voice-streams.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';

class VoiceStreams extends Component {
  render() {
    return (
      <div className='realtime-calibration-voice-streams'>
        {
          this.props.streamUrls.map((item, index) => {
            document.amplitude_config = {
                'amplitude_live': true,
                'amplitude_live_source': item
            };

            return (
              <div key={ index }>
                <audio id='single-song' preload='none'>
                  <source src={ item } type='audio/wav' id='single-song' />
                </audio>
                <div id='amplitude-play-pause' className='amplitude-paused'></div>
              </div>
            );
          })
        }
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    streamUrls: state.realtimeCalibrationData.voiceStreams,
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(VoiceStreams);
