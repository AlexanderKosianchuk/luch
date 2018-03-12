//import './realtime-chart.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Line, defaults } from 'react-chartjs-2';

class RealtimeChart extends Component {
  constructor(props) {
    super(props);
    defaults.global.animation = false;
  }

  rotateData() {
    let lines = [];

    this.props.data.forEach((frame, pointIndex) => {
      // NOTE:  first (0) item is counter
      // and it is suitable for labels

      if (!Array.isArray(frame)) {
        return;
      }

      frame.forEach((point, channelIndex) => {
        if (!Array.isArray(lines[channelIndex])) {
          lines[channelIndex] = [];
        }

        lines[channelIndex].push(point);
      });
    });

    return lines;
  }

  getData() {
    let lines = this.rotateData();
    let chartData = {
      labels: lines[0] || [],
      datasets: []
    };

    this.props.realtimeCalibrationParams
      .chartAnalogParams
      .forEach((item, index) => {
        let itemId = item.id;
        let cyclo = this.props.fdrCyclo;

        if (cyclo.analogParams.length < 1) {
          return false;
        }

        let paramIndex = cyclo.analogParams.findIndex((element) => {
          return element.id === itemId;
        });

        if (!Number.isInteger(paramIndex)) {
          return false;
        }

        let param = cyclo.analogParams[paramIndex];

        let line = [];
        if (lines[itemId + 1]) { // first(0) item is reserved (clock)
          line = lines[itemId + 1];
        }

        chartData.datasets.push({
          fill: false,
          label: param.code,
          yAxisID: param.code,
          borderColor: '#' + param.color,
          data: line
        });
      });

    return chartData;
  }

  getOptions() {
    let chartOptions = {
      scales: {
        yAxes: []
      }
    };

    let count = this.props.realtimeCalibrationParams
      .chartAnalogParams
      .length - 1;

    let lines = this.rotateData();

    this.props.realtimeCalibrationParams
      .chartAnalogParams
      .forEach((item, index) => {
        let itemId = item.id;
        let cyclo = this.props.fdrCyclo;

        if (cyclo.analogParams.length < 1) {
          return false;
        }

        let paramIndex = cyclo.analogParams.findIndex((element) => {
          return element.id === itemId;
        });

        if (!Number.isInteger(paramIndex)) {
          return false;
        }

        let param = cyclo.analogParams[paramIndex];

        let ticks = {
          max: 1,
          min: 0
        }

        if (lines[itemId + 1]) {
          let line = lines[itemId + 1];
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
        }

        chartOptions.scales.yAxes.push({
          id: param.code,
          type: 'linear',
          display: false,
          ticks: ticks
        });
      });

    return chartOptions;
  }

  render() {
    if (this.props.realtimeCalibrationParams
      .chartAnalogParams
      .length === 0
    ) {
      return null;
    }

    return (
      <div className='realtime-calibration-realtime-chart'>
      <Line
        height={ 100 }
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
    currentFrame: state.realtimeCalibrationData.currentFrame,
    data: state.realtimeCalibrationData.data,
    realtimeCalibrationParams: state.realtimeCalibrationParams,
    fdrCyclo: state.fdrCyclo
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(RealtimeChart);
