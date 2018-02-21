import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import RealtimeChart from 'components/realtime-calibration/realtime-chart/RealtimeChart';

class ChartsContainer extends Component {
  render() {
    return (
      <div className='realtime-calibration-realtime-chart'>

      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    realtimeCalibrationParams: state.realtimeCalibrationParams,
    fdrCyclo: state.fdrCyclo
  }
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(ChartsContainer);
