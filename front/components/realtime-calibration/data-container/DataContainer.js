import './data-container.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import RealTimeChart from 'components/realtime-calibration/realtime-chart/RealTimeChart';

import request from 'actions/request';
import bindRealtimeCalibrationSocketEvents from 'actions/socket/bindRealtimeCalibrationSocketEvents';

class DataContainer extends Component {
    componentWillReceiveProps(nextProps) {
        if ((nextProps.status === 'init')
            && (this.props.status !== nextProps.status)
        ) {
            this.props.bindRealtimeCalibrationSocketEvents({
                interactionUrl: this.props.interactionUrl,
                status: this.props.webSocketsStatus,
                uid: this.props.uid
            });
        }
    }

    buildBody() {
        if (this.props.status === null) {
             return (<div className='realtime-calibration-data-container__label'>
                 <Translate value='realtimeCalibration.dataContainer.configureConnection'/>
             </div>);
        } else if (this.props.status === 'init') {
             return (<div className='realtime-calibration-data-container__label'>
                 <Translate value='realtimeCalibration.dataContainer.init'/>
             </div>);
        } else if (this.props.status === 'bindingSocket') {
             return (<div className='realtime-calibration-data-container__label'>
                 <Translate value='realtimeCalibration.dataContainer.connectionPending'/>
             </div>);
        } else if (this.props.status === 'waitingData') {
            return (<div className='realtime-calibration-data-container__label'>
                <Translate value='realtimeCalibration.dataContainer.waitingData'/>
            </div>);
        } else if (this.props.status === 'onAir') {
            return <RealTimeChart/>;
        }
    }

    render() {
        return (
            <div className='realtime-calibration-data-container'>
                { this.buildBody() }
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {
        status: state.realTimeCalibrationData.status,
        errorCode: state.realTimeCalibrationData.errorCode,
        webSocketsStatus: state.webSockets.status
    };
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch),
        bindRealtimeCalibrationSocketEvents: bindActionCreators(bindRealtimeCalibrationSocketEvents, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(DataContainer);
