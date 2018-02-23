import './cyclo-params.sass'

import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import Tile from 'controls/cyclo-params/tile/Tile';
import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';
import transmit from 'actions/transmit';

const DEFAULT_EVENT = 'CHANGE_FLIGHT_PARAM_CHECKSTATE';

class CycloParams extends Component {
  constructor(props) {
    super(props);

    if (props.eventToDispatch) {
      this.eventToDispatch = props.eventToDispatch;
    } else {
      this.eventToDispatch = DEFAULT_EVENT;
    }
  }

  componentDidMount() {
    if (this.props.flightId && this.props.flightId !== null) {
      this.props.request(
        ['fdr', 'getCyclo'],
        'get',
        'FDR_CYCLO',
        { flightId: this.props.flightId }
      );
    }

    if (this.props.fdrId && this.props.fdrId !== null) {
      this.props.request(
        ['fdr', 'getCycloByFdrId'],
        'get',
        'FDR_CYCLO',
        { fdrId: this.props.fdrId }
      );
    }

    if (Array.isArray(this.props.chosenAnalogParams)) {
      this.props.chosenAnalogParams.forEach((item) => {
        this.props.transmit(
          this.eventToDispatch,
          {
            ...item,
            ...{ state: true }
          }
        );
      });
    }

    if (Array.isArray(this.props.chosenBinaryParams)) {
      this.props.chosenBinaryParams.forEach((item) => {
        this.props.transmit(
          this.eventToDispatch,
          {
            ...item,
            ...{ state: true }
          }
        );
      });
    }
  }

  buildBody() {
    if (this.props.cycloFetching !== false) {
      return <ContentLoader/>
    } else {
      return <Tile
        analogParams={ this.props.fdrCyclo.analogParams }
        binaryParams={ this.props.fdrCyclo.binaryParams }
        chosenAnalogParams={ this.props.chosenAnalogParams || [] }
        chosenBinaryParams={ this.props.chosenBinaryParams || [] }
        flightId={ this.props.flightId }
        colorPickerEnabled={ this.props.colorPickerEnabled }
        eventToDispatch={ this.eventToDispatch }
      />
    }
  }

  render() {
    return <div className='cyclo-params'>
      { this.buildBody() }
    </div>;
  }
}

function mapStateToProps(state) {
  return {
    cycloFetching: state.fdrCyclo.pending,
    fdrCyclo: state.fdrCyclo,
  }
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    transmit: bindActionCreators(transmit, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(CycloParams);
