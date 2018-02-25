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

  getBinaryLine(data, id) {
    return data.map((frame) => {
      let index = frame.findIndex((item) => {
        return item.id === id;
      });

      if (index === -1) {
        return null;
      }

      return 1
    });
  }

  buildAnalog() {
    let lines = this.rotateData(this.props.phisics);

    return this.props.params.chartAnalogParams
      .map((item, index) => {
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

  buildBinary() {
    return this.props.params.chartBinaryParams
      .map((item, index) => {
        return <RealtimeChart
          key={ index }
          param={ item }
          line={ this.getBinaryLine(this.props.binary, item.id) }
          timeline={ this.props.timeline }
          isBinary={ true }
        />
      });
  }

  render() {
    return (
      <div className='realtime-calibration-realtime-chart-container'>
        { this.buildAnalog() }
        { this.buildBinary() }
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    params: state.realtimeCalibrationParams,
    phisics: state.realtimeCalibrationData.phisics,
    binary: state.realtimeCalibrationData.binary,
    timeline: state.realtimeCalibrationData.timeline,
    currentFrame: state.realtimeCalibrationData.currentFrame,
  }
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(ChartsContainer);
