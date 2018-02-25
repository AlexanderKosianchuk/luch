import './realtime-chart.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Line, defaults } from 'react-chartjs-2';

class RealtimeChart extends Component {
  constructor(props) {
    super(props);
    defaults.global.animation = false;
  }

  getData() {
    return {
      labels: this.props.timeline,
      datasets: [{
        fill: false,
        label: this.props.param.code,
        backgroundColor: '#' + this.props.param.color,
        borderColor: '#' + this.props.param.color,
        data: this.props.line
      }]
    };
  }

  getOptions() {
    return {
      legend: {
        onClick: (e) => e.stopPropagation()
      }
    };
  }

  render() {
    return (
      <div className='realtime-calibration-realtime-chart'>
      <Line
        height={ 280 }
        data={ this.getData() }
        options={ this.getOptions() }
        redraw={ true }
      />
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {

    data: state.realtimeCalibrationData.data,
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(RealtimeChart);
