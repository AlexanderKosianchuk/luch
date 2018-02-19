import './voice-streams.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';

import { Howl, Howler } from 'howler';

class VoiceStreams extends Component {
  constructor(props) {
    super(props);

    this.howls = [];
  }

  shouldComponentUpdate(nextProps) {
    if (nextProps.isRunning !== this.props.isRunning) {
      return true;
    }

    let shouldUpdate = false;

    for (let ii = 0; ii < nextProps.streamUrls.length; ii++) {
      if (nextProps.streamUrls[ii] !== this.props.streamUrls[ii]) {
        shouldUpdate = true;
      }
    }

    return shouldUpdate;
  }

  componentDidUpdate() {
    if (!this.props.isRunning) {
      this.howls.forEach((howl) => {
        howl.unload();
      });

      this.howls = [];

      return;
    }

    if ((NODE_ENV === 'dev')
      && (this.props.streamUrls.length > 0)
    ) {
      this.howls.push(this.howl(this.props.streamUrls[0]));
      return;
    }

    this.props.streamUrls.map((src, index) => {
      this.howls.push(this.howl(src));
    });
  }

  howl(src) {
    return new Howl({
      src: src,
      html5: true,
      format: ['wav'],
      autoplay: true,
      loop: true
    });
  }

  render() {
    return (
      <div className='realtime-calibration-voice-streams'></div>
    );
  }
}

function mapStateToProps(state) {
  return {
    streamUrls: state.realtimeCalibrationData.voiceStreams,
    isRunning: state.realtimeCalibrationData.status,
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(VoiceStreams);
