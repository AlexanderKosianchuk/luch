import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import RealtimeChart from 'components/realtime-calibration/realtime-chart/RealtimeChart';
import CompositeChart from 'components/realtime-calibration/composite-chart/CompositeChart';

class ChartsContainer extends Component {
  rotateData(data) {
    let lines = [];

    data.forEach((frame, pointIndex) => {
      if (typeof frame !== 'object') {
        return;
      }

      Object.keys(frame).forEach((key, channelIndex) => {
        if (!lines.hasOwnProperty(key)) {
          lines[key] = [];
        }

        lines[key].push(frame[key]);
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

  buildСomposite() {
    let lines = this.rotateData(this.props.phisics);
    let groups = {};

    this.props.charts.grouped.forEach((item) => {
      if (!groups[item.group]) {
        groups[item.group] = [];
      }

      groups[item.group].push(item);
    });

    return Object.keys(groups).map((key, index) => {
      let params = groups[key];
      let chartLines = params.reduce((result, item) => {
        if ((item.type === 'ap') && lines[item.id]) {
          result.push({
            param: item,
            data: lines[item.id]
          });
        }

        return result;
      }, []);

      let chartBinaries = params.reduce((result, item) => {
        if (item.type === 'bp') {
          result.push({
            param: item,
            data: this.getBinaryLine(this.props.binary, item.id)
          });
        }

        return result;
      }, []);

      return (
        <CompositeChart
          key={ index }
          lines={ chartLines.concat(chartBinaries) }
          group={ parseInt(key) }
          timeline={ this.props.timeline }
        />
      );
    });
  }

  buildAnalog() {
    let lines = this.rotateData(this.props.phisics);

    return this.props.charts.analog
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
    return this.props.charts.binary
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
        { this.buildСomposite() }
        { this.buildAnalog() }
        { this.buildBinary() }
      </div>
    );
  }
}

ChartsContainer.propTypes = {
  charts: PropTypes.shape({
    analog: PropTypes.array.isRequired,
    binary: PropTypes.array.isRequired,
    grouped: PropTypes.array.isRequired
  }),
  phisics: PropTypes.array.isRequired,
  binary: PropTypes.array.isRequired,
  timeline: PropTypes.array.isRequired,
  currentFrame: PropTypes.number.isRequired
};

function mapStateToProps(state) {
  return {
    charts: state.realtimeCalibrationCharts,
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
