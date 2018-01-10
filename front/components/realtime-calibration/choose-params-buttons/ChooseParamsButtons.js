import './choose-params-buttons.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

class ChooseParamsButtons extends Component {
    handleChooseChartParamsClick() {

    }

    handleChooseContainerParamsClick() {

    }

    render() {
        return (
            <div className='realtime-calibration-choose-params-buttons'>
                <Translate value='realtimeCalibration.chooseParamsButtons.chooseParamsToShow'/>
                <button
                    className='btn btn-default realtime-calibration-choose-params-buttons__button'
                    onClick={ this.handleChooseChartParamsClick.bind(this) }
                >
                    <Translate value='realtimeCalibration.chooseParamsButtons.chartParams'/>
                </button>
                <button
                    className='btn btn-default realtime-calibration-choose-params-buttons__button'
                    onClick={ this.handleChooseContainerParamsClick.bind(this) }
                >
                    <Translate value='realtimeCalibration.chooseParamsButtons.containerParams'/>
                </button>
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(ChooseParamsButtons);
