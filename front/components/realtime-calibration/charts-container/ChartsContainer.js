import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import RealtimeChart from 'components/realtime-calibration/realtime-chart/RealtimeChart';

class ChartsContainer extends Component {
  rotateData(data) {
    let lines = [];

    data.forEach((frame, pointIndex) => {
      if (typeof frame !== 'object') {
        return;
      }

      Object.keys(frame).forEach((key, channelIndex) => {
        if (!lines.hasOwnProperty(channelIndex)) {
          lines[channelIndex] = [];
        }

        lines[channelIndex].push(frame[key]);
      });
    });

    return lines;
  }

  build() {
    let charts = this.props.params.chartAnalogParams;
    let lines = this.rotateData(this.props.phisics);

    return charts.map((item, index) => {
      let line = [];
      if (lines.hasOwnProperty(item.id)) {
        line = lines[item.id];
      }

      return <RealtimeChart
        key={ index }
        param={ item }
        line={ line }
        timeline={ this.props.timeline }
      />
    });
  }

  render() {
    return (
      <div className='realtime-calibration-realtime-chart-container'>
        { this.build() }
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    params: state.realtimeCalibrationParams,
    phisics: state.realtimeCalibrationData.phisics,
    timeline: state.realtimeCalibrationData.timeline,
    currentFrame: state.realtimeCalibrationData.currentFrame,
  }
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(ChartsContainer);
