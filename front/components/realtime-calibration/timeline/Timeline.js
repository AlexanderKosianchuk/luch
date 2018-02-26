import './timeline.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Line, defaults } from 'react-chartjs-2';

import request from 'actions/request';

class Timeline extends Component {
  constructor(props) {
    super(props);
    defaults.global.animation = false;
  }

  calcPoints(rawData) {
    let points = [];

    for (let ii = 0; ii < rawData.length; ii++) {
      let item = rawData[ii];

      if (item !== null) {
        points.push({
          x: item,
          y: 1
        });
        continue;
      }

      if ((ii > 0) && (ii < rawData.length - 1)) {
        points.push({
          x: (rawData[ii-1] + rawData[ii+1]) / 2,
          y: item // null
        });
        continue;
      }
    }

    return points;
  }

  getData() {
    return {
      datasets: [{
        fill: false,
        label: 'timeline',
        borderColor: '#053a5c',
        data: this.calcPoints(this.props.fullTimeline)
      }]
    };
  }

  getOptions() {
    let that = this;

    return {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        yAxes: [{
          id: 'timeline',
          type: 'linear',
          display: false,
          ticks: {
            max: 1.01,
            min: 0.99
          }
        }],
        xAxes: [{
          position: 'top',
          type: 'time',
          time: {
            displayFormats: {
              quarter: 'HH:mm:ss'
            }
          },
          ticks: {
            autoSkip: true,
            maxRotation: 0,
            minRotation: 0
          }
        }]
      },
      legend: {
        display: false
      },
      tooltips: {
        yAlign: 'bottom',
        callbacks: {
          title: function(tooltipItem, data) {
            return null;
          },
          label: function(tooltipItem, data) {
            let date = new Date(tooltipItem.xLabel);
            let hours = date.getHours();
            let minutes = '0' + date.getMinutes();
            let seconds = '0' + date.getSeconds();

            return hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);
          },
          afterLabel: function(tooltipItem, data) {
            return null;
          }
        },
        bodyFontSize: 14,
        displayColors: false
      },
      onClick: function (event, activeElements) {
        let elementIndex = activeElements[0];
        let value = this.data
          .datasets[elementIndex._datasetIndex]
          .data[elementIndex._index]
          .x;

          that.props.request(
            ['uploader', 'getSegment'],
            'get',
            'REALTIME_CALIBRATION_SEGMENT_COMPLETE',
            {
              timestamp: value,
              uploadingUid: that.props.uid
            }
          );
      }
    };
  }

  render() {
    return (
      <div className='realtime-calibration-timeline'>
        <Line
          height={ 50 }
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
    isRunning: state.realtimeCalibrationData.status,
    fullTimeline: state.realtimeCalibrationData.fullTimeline,
  };
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(Timeline);
