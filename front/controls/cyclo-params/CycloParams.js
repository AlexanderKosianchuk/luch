import './cyclo-params.sass'

import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import Tile from 'controls/cyclo-params/tile/Tile';
import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';

class CycloParams extends Component {
  componentWillMount() {
    if (this.props.flightId && this.props.flightId !== null) {
      this.props.request(
        ['fdr', 'getCyclo'],
        'get',
        'FDR_CYCLO',
        { flightId: this.props.flightId }
      );

      return;
    }

    if (this.props.fdrId && this.props.fdrId !== null) {
      this.props.request(
        ['fdr', 'getCycloByFdrId'],
        'get',
        'FDR_CYCLO',
        { fdrId: this.props.fdrId }
      );
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
        context={ this.props.context }
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
    fdrCyclo: state.fdrCyclo
  }
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(CycloParams);
