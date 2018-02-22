import './timeline.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Line, defaults } from 'react-chartjs-2';

class Timeline extends Component {
  render() {
    return (
      <div className='realtime-calibration-realtime-chart'>

      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    isRunning: state.realtimeCalibrationData.status
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(Timeline);
