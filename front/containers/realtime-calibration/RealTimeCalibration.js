import React, { Component } from 'react';
import { connect } from 'react-redux';

import Menu from 'controls/menu/Menu';

import Wrapper from 'components/realtime-calibration/wrapper/Wrapper';

class RealTimeCalibration extends Component {
  render() {
    return (
      <div>
        <Menu />
        <Wrapper fdrId={ this.props.fdrId } />
      </div>
    );
  }
}

function mapStateToProps(state, ownProps) {
  return {
    fdrId: parseInt(ownProps.match.params.fdrId) || null,
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(RealTimeCalibration);
