import './choose-params-buttons.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate, I18n } from 'react-redux-i18n';

import CycloParams from 'controls/cyclo-params/CycloParams';
import Dialog from 'controls/dialog/Dialog';

const CYCLO_CHART_PARAMS_CONTEXT = 'realtimeCalibrationChartParams';
const CYCLO_CONTAINER_PARAMS_CONTEXT = 'realtimeCalibrationContainerParams';

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

  handleContainerParamsDialogToggle(event) {
    event.preventDefault();

    this.setState({ containerParamsDialogShown: !this.state.containerParamsDialogShown });
  }

  buildChartParamsDialogBody() {
    return <CycloParams
      fdrId={ this.props.handler.getSelectedFdrId() }
      context={ CYCLO_CHART_PARAMS_CONTEXT }
      chosenAnalogParams={ this.props.realtimeCalibrationParams.chosenChartAnalogParams }
      chosenBinaryParams={ this.props.realtimeCalibrationParams.chosenChartBinaryParams }
    />
  }

  buildContainerParamsDialogBody() {
    return <CycloParams
      fdrId={ this.props.handler.getSelectedFdrId() }
      context={ CYCLO_CONTAINER_PARAMS_CONTEXT }
      chosenAnalogParams={ this.props.realtimeCalibrationParams.chosenContainerAnalogParams }
      chosenBinaryParams={ this.props.realtimeCalibrationParams.chosenContainerBinaryParams }
    />
  }

  buildChartParamsDialogFooter() {
    return <button type="button" className="btn btn-default"
      onClick={ this.handleChartParamsDialogToggle.bind(this) }
    >
      <Translate value='realtimeCalibration.chooseParamsButtons.apply'/>
    </button>;
  }

  buildContainerParamsDialogFooter() {
    return <button type="button" className="btn btn-default"
      onClick={ this.handleContainerParamsDialogToggle.bind(this) }
    >
      <Translate value='realtimeCalibration.chooseParamsButtons.apply'/>
    </button>;
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
          buildTitle={ () => { return I18n.t('realtimeCalibration.chooseParamsButtons.chooseParamsToShowOnChart') }}
          buildBody={ this.buildChartParamsDialogBody.bind(this) }
          buildFooter={ this.buildChartParamsDialogFooter.bind(this) }
        />
        <Dialog
          isShown={ this.state.containerParamsDialogShown }
          handleClose={ this.handleContainerParamsDialogToggle.bind(this) }
          buildTitle={ () => { return I18n.t('realtimeCalibration.chooseParamsButtons.chooseParamsToShowInContainer') }}
          buildBody={ this.buildContainerParamsDialogBody.bind(this) }
          buildFooter={ this.buildContainerParamsDialogFooter.bind(this) }
        />
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    realtimeCalibrationParams: state.realtimeCalibrationParams
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(ChooseParamsButtons);
