import './choose-params-buttons.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate, I18n } from 'react-redux-i18n';
import Switch from 'react-bootstrap-switch';

import FdrTemplateSelector from 'controls/fdr-template-selector/FdrTemplateSelector';
import CycloParams from 'controls/cyclo-params/CycloParams';
import Dialog from 'controls/dialog/Dialog';

import transmit from 'actions/transmit';

const EVENT_TO_DISPATCH = 'CHANGE_REALTIME_CALIBRATION_PARAM_CHECKSTATE';

class ChooseParamsButtons extends Component {
  constructor(props) {
    super(props);

    this.state = {
      containerParamsDialogShown: false
    }
  }

  handleContainerParamsDialogToggle(event) {
    event.preventDefault();

    this.setState({ containerParamsDialogShown: !this.state.containerParamsDialogShown });
  }

  buildContainerParamsDialogBody() {
    return <CycloParams
      fdrId={ this.props.fdrId }
      eventToDispatch={ EVENT_TO_DISPATCH }
      chosenAnalogParams={ this.props.realtimeCalibrationParams.containerAnalogParams }
      chosenBinaryParams={ this.props.realtimeCalibrationParams.containerBinaryParams }
    />
  }

  buildContainerParamsDialogFooter() {
    return <button type="button" className="btn btn-default"
      onClick={ this.handleContainerParamsDialogToggle.bind(this) }
    >
      <Translate value='realtimeCalibration.chooseParamsButtons.apply'/>
    </button>;
  }

  handleSwitch(elem, state) {
    if (state === true) {
      this.props.changeParamsSource('template');
    } else {
      this.props.changeParamsSource('manual');
    }
  }

  handleFdrTemplateChange() {
    this.props.transmit('CLEAR_REALTIME_CALIBRATION_PARAMS');
  }

  render() {
    return (
      <div className='realtime-calibration-choose-params-buttons'>
        <div>
          <Translate value='realtimeCalibration.chooseParamsButtons.chooseSource'/>
          <Switch
            bsSize='small'
            onText={ I18n.t('realtimeCalibration.chooseParamsButtons.template') }
            offText={ I18n.t('realtimeCalibration.chooseParamsButtons.manual') }
            onChange={(el, state) => this.handleSwitch(el, state)}
            name='paramsSource'
            defaultValue={ (this.props.paramsSource === 'template') && (this.props.fdrId !== null) }
          />
        </div>
          { ((this.props.paramsSource === 'template')
            && (this.props.fdrId !== null))
            ? (
            <FdrTemplateSelector
              fdrId={ this.props.fdrId }
              handleChange={ this.handleFdrTemplateChange.bind(this) }
            />
          ) : (
            <div>
              <button
                className='btn btn-default realtime-calibration-choose-params-buttons__button'
                onClick={ this.handleContainerParamsDialogToggle.bind(this) }
              >
                <Translate value='realtimeCalibration.chooseParamsButtons.containerParams'/>
              </button>
              <Dialog
                isShown={ this.state.containerParamsDialogShown }
                handleClose={ this.handleContainerParamsDialogToggle.bind(this) }
                buildTitle={ () => { return I18n.t('realtimeCalibration.chooseParamsButtons.chooseParamsToShowInContainer') }}
                buildBody={ this.buildContainerParamsDialogBody.bind(this) }
                buildFooter={ this.buildContainerParamsDialogFooter.bind(this) }
              />
            </div>
          )}
      </div>
    );
  }
}

ChooseParamsButtons.propTypes = {
  fdrId: PropTypes.number,
  changeParamsSource: PropTypes.func.isRequired,
  realtimeCalibrationParams: PropTypes.object.isRequired
}

function mapStateToProps(state) {
  return {
    realtimeCalibrationParams: state.realtimeCalibrationParams
  };
}

function mapDispatchToProps(dispatch) {
  return {
    transmit: bindActionCreators(transmit, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(ChooseParamsButtons);
