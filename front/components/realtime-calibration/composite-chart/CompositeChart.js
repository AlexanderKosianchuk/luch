import './composite-chart.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { Line, defaults } from 'react-chartjs-2';

import transmit from 'actions/transmit';

class CompositeChart extends Component {
  constructor(props) {
    super(props);
    defaults.global.animation = false;
  }

  getData() {
    let datasets = this.props.lines
      .map((item, index) => {
        return {
          fill: false,
          label: item.param.code,
          yAxisID: item.param.code,
          borderColor: '#' + item.param.color,
          backgroundColor: '#' + item.param.color,
          data: item.data
        };
      });

    return {
      labels: this.props.timeline.length > 0 ? this.props.timeline : [0],
      datasets: datasets || []
    };
  }

  getOptions() {
    let count = this.props.lines.reduce((result, item) => {
      if ((item.param.type === 'ap')) {
        result++;
      }

      return result;
    }, -1);

    let bpCount = this.props.lines.reduce((result, item) => {
      if ((item.param.type === 'bp')) {
        result++;
      }

      return result;
    }, 0);

    if (bpCount > 0) {
      count++;
    }

    let yApAxes = this.props.lines
      .filter((item) => (item.param.type === 'ap'))
      .map((item, index) => {
        let line = item.data;
        let param = item.param;

        let ticks = {
          max: 1,
          min: 0
        }

        let min = Math.min(...line);
        let max = Math.max(...line);
        let curCorridor = 0;

        if ((index == 0) && (max > 1)){
          max += max * 0.15;//prevent first(top) param out ceiling chart boundary
        }

        if (max == min){
          max += 0.001;
        }

        if (max > 0){
          curCorridor = ((max - min) * 1.05);
        } else {
          curCorridor = -((min - max) * 1.05);
        }

        ticks.max = max + (index * curCorridor);
        ticks.min = min - ((count - index) * curCorridor);

        return {
          id: param.code,
          type: 'linear',
          display: false,
          ticks: ticks
        };
      });

    let busyCorridor = ((count - 1) / count * 100);
    let freeCorridor = 100 - busyCorridor; //100%
    let curCorridor = freeCorridor / bpCount;

    let yBpAxes = this.props.lines
      .filter((item) => (item.param.type === 'bp'))
      .map((item, index) => {
        return {
          id: item.param.code,
          type: 'linear',
          display: false,
          ticks: {
            max: 100 - (curCorridor * index),
            min: 0 - (curCorridor * index)
          }
        };
      });

    return {
      maintainAspectRatio: false,
      scales: {
        yAxes: yApAxes.concat(yBpAxes),
        xAxes: [{
          type: 'time',
          time: {
            unit: 'second'
          }
        }]
      },
      legend: {
        onClick: (e, data) => {
          e.stopPropagation();
          this.props.transmit('REMOVE_REALTIME_CALIBRATION_CHART_PARAM_BY_CODE', {
            code: data.text,
            group: this.props.group
          });
        }
      }
    };
  }

  handleClick() {
    this.props.transmit('REMOVE_REALTIME_CALIBRATION_CHART_PARAM_GROUP', {
      group: this.props.group
    });
  }

  render() {
    return (
      <div className='realtime-calibration-composite-chart'>
        <Line
          height={ 300 }
          data={ this.getData() }
          options={ this.getOptions() }
          redraw={ true }
        />
        <div
          className='realtime-calibration-composite-chart__checkbox'
          onClick={ this.handleClick.bind(this) }
        >
          <span className='glyphicon glyphicon-remove'></span>
        </div>
      </div>
    );
  }
}

CompositeChart.propTypes = {
  lines: PropTypes.array.isRequired,
  timeline: PropTypes.array.isRequired,
  group: PropTypes.number.isRequired,
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

export default connect(mapStateToProps, mapDispatchToProps)(CompositeChart);
