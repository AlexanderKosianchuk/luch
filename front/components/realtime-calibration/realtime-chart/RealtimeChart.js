import './realtime-chart.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { Line, defaults } from 'react-chartjs-2';

import transmit from 'actions/transmit';

class RealtimeChart extends Component {
  constructor(props) {
    super(props);
    defaults.global.animation = false;
  }

  getData() {
    return {
      labels: this.props.timeline.length > 0 ? this.props.timeline : [0],
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
      scales: {
        xAxes: [{
          type: 'time',
          time: {
            unit: 'second'
          }
        }]
      },
      legend: {
        onClick: (e) => e.stopPropagation()
      }
    };
  }

  handleClick() {
    this.props.transmit('CHANGE_REALTIME_CALIBRATION_CHART_PARAM_CHECKSTATE', {
      ...this.props.param,
      ...{ state: false }
    });
  }

  render() {
    return (
      <div className='realtime-calibration-realtime-chart'>
        <Line
          height={ this.props.isBinary ? 100 : 280 }
          data={ this.getData() }
          options={ this.getOptions() }
          redraw={ true }
        />
        <div
          className='realtime-calibration-realtime-chart__checkbox'
          onClick={ this.handleClick.bind(this) }
        >
          <span className='glyphicon glyphicon-remove'></span>
        </div>
      </div>
    );
  }
}

RealtimeChart.propTypes = {
  param: PropTypes.object.isRequired,
  line: PropTypes.array.isRequired,
  timeline: PropTypes.array.isRequired,
  transmit: PropTypes.func.isRequired
};

function mapStateToProps(state) {
  return {};
}

function mapDispatchToProps(dispatch) {
  return {
    transmit: bindActionCreators(transmit, dispatch),
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(RealtimeChart);
