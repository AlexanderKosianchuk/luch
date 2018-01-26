import React, { Component } from 'react';
import { connect } from 'react-redux';

import Menu from 'controls/menu/Menu';

import Wrapper from 'components/realtime-calibration/wrapper/Wrapper';

class RealTimeCalibration extends Component {
  render() {
    return (
      <div>
        <Menu />
        <Wrapper
          fdrId={ this.props.fdrId }
          paramsSource={ this.props.paramsSource }
          fdrTemplateId={ this.props.fdrTemplateId }
        />
      </div>
    );
  }
}

function mapStateToProps(state, ownProps) {
  return {
    fdrId: ownProps.match.params.fdrId || null,
    paramsSource: ownProps.match.params.paramsSource || 'template',
    fdrTemplateId: ownProps.match.params.fdrTemplateId || null
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(RealTimeCalibration);
