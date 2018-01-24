import './cyclo-params.sass'

import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import Tile from 'controls/cyclo-params/tile/Tile';
import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';
import transmit from 'actions/transmit';

class CycloParams extends Component {
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

    if (this.props.storeCheckstate
      && Array.isArray(this.props.chosenAnalogParams)
    ) {
      this.props.chosenAnalogParams.forEach((item) => {
        this.props.transmit(
          'CHANGE_FLIGHT_PARAM_CHECKSTATE',
          {
            id: item.id,
            paramType: 'ap',
            state: true,
            storeCheckstate: true
          }
        );
      });
    }

    if (this.props.storeCheckstate
      && Array.isArray(this.props.chosenBinaryParams)
    ) {
      this.props.chosenBinaryParams.forEach((item) => {
        this.props.transmit(
          'CHANGE_FLIGHT_PARAM_CHECKSTATE',
          {
            id: item.id,
            paramType: 'bp',
            state: true,
            storeCheckstate: true
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
        storeCheckstate={ this.props.storeCheckstate }
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
