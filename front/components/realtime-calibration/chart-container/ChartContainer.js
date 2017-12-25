import './chart-container.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import RealTimeChart from 'components/realtime-calibration/realtime-chart/RealTimeChart';

import request from 'actions/request';
import bindRealtimeCalibrationSocketEvents from 'actions/socket/bindRealtimeCalibrationSocketEvents';

const INTERACTION_URL = 'http://localhost:1337';

class ChartContainer extends Component {
    componentWillReceiveProps(nextProps) {
        if ((nextProps.status === 'init')
            && (this.props.status !== nextProps.status)
        ) {
            this.props.bindRealtimeCalibrationSocketEvents({
                interactionUrl: INTERACTION_URL
            });
        }
    }

    buildBody() {
        if (this.props.status === null) {
             return (<div className='realtime-calibration-chart-container__label'>
                 <Translate value='realtimeCalibration.chartContainer.configureConnection'/>
             </div>);
        } else if (this.props.status === 'init') {
             return (<div className='realtime-calibration-chart-container__label'>
                 <Translate value='realtimeCalibration.chartContainer.init'/>
             </div>);
        } else if (this.props.status === 'bindingSocket') {
             return (<div className='realtime-calibration-chart-container__label'>
                 <Translate value='realtimeCalibration.chartContainer.connectionPending'/>
             </div>);
        } else if (this.props.status === 'waitingData') {
            return (<div className='realtime-calibration-chart-container__label'>
                <Translate value='realtimeCalibration.chartContainer.waitingData'/>
            </div>);
        } else if (this.props.status === 'onAir') {
            return <RealTimeChart/>;
        }
    }

    render() {
        return (
            <div className='realtime-calibration-chart-container'>
                { this.buildBody() }
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {
        status: state.realTimeCalibrationData.status,
        errorCode: state.realTimeCalibrationData.errorCode
    };
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch),
        bindRealtimeCalibrationSocketEvents: bindActionCreators(bindRealtimeCalibrationSocketEvents, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(ChartContainer);
