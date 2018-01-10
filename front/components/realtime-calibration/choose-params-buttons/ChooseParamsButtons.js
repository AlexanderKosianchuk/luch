import './choose-params-buttons.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import CycloParams from 'controls/cyclo-params/CycloParams';
import Dialog from 'controls/dialog/Dialog';

class ChooseParamsButtons extends Component {
    constructor(props) {
        super(props);

        this.state = {
            chartParamsDialogShown: false,
            containerParamsDialogShown: false
        }
    }

    handleChartParamsDialogToggle(event) {
        event.preventDefault();

        this.setState({ chartParamsDialogShown: !this.state.chartParamsDialogShown });
    }

    handleContainerParamsDialogToggle() {
        event.preventDefault();

        this.setState({ containerParamsDialogShown: !this.state.containerParamsDialogShown });
    }

    buildChartParamsDialogBody() {
        return <CycloParams
            fdrId={ this.props.handler.getSelectedFdrId() }
        />
    }

    buildContainerParamsDialogBody() {
        return <CycloParams
            fdrId={ this.props.handler.getSelectedFdrId() }
        />
    }

    render() {
        return (
            <div className='realtime-calibration-choose-params-buttons'>
                <Translate value='realtimeCalibration.chooseParamsButtons.chooseParamsToShow'/>
                <button
                    className='btn btn-default realtime-calibration-choose-params-buttons__button'
                    onClick={ this.handleChartParamsDialogToggle.bind(this) }
                >
                    <Translate value='realtimeCalibration.chooseParamsButtons.chartParams'/>
                </button>
                <button
                    className='btn btn-default realtime-calibration-choose-params-buttons__button'
                    onClick={ this.handleContainerParamsDialogToggle.bind(this) }
                >
                    <Translate value='realtimeCalibration.chooseParamsButtons.containerParams'/>
                </button>
                <Dialog
                    isShown={ this.state.chartParamsDialogShown }
                    handleClose={ this.handleChartParamsDialogToggle.bind(this) }
                    buildBody={ this.buildChartParamsDialogBody.bind(this) }
                />
                <Dialog
                    isShown={ this.state.containerParamsDialogShown }
                    handleClose={ this.handleContainerParamsDialogToggle.bind(this) }
                    buildBody={ this.buildContainerParamsDialogBody.bind(this) }
                />
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
