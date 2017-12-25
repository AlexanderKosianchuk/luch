import './realtime-chart.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Line } from 'react-chartjs-2';

class RealTimeChart extends Component {
    rotateData() {
        let lines = [];

        this.props.data.forEach((frame, pointIndex) => {
            // NOTE:  first (0) item is counter
            // and it is suitable for labels

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

        console.log(lines[2]);

        return {
           labels: lines[0],
           datasets: [{
               fill: false,
               label: 'A',
               yAxisID: 'A',
               borderColor: 'rgba(75,192,192,1)',
               data: lines[1]
           }, {
               fill: false,
               label: 'B',
               yAxisID: 'B',
               borderColor: 'rgba(192,75,192,1)',
               data: lines[2]
           }, {
               fill: false,
               label: 'D',
               yAxisID: 'D',
               borderColor: 'rgba(192,192,75,1)',
               data: lines[3]
           }]
       };
    }

    getOptions() {
        return  {
            scales: {
              yAxes: [{
                id: 'A',
                type: 'linear',
                position: 'left',
              }, {
                id: 'B',
                type: 'linear',
                display: false,
                ticks: {
                  max: 2000,
                  min: 0
                }
              }, {
                id: 'D',
                type: 'linear',
                display: false,
                ticks: {
                  max: 2000,
                  min: 0
                }
              }]
            }
        };
    }

    render() {
        return (
            <div className='realtime-calibration-realtime-chart'>
                <Line data={ this.getData() } options={ this.getOptions() } />
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {
        currentFrame: state.realTimeCalibrationData.currentFrame,
        data: state.realTimeCalibrationData.data,
    };
}

function mapDispatchToProps(dispatch) {
    return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(RealTimeChart);
