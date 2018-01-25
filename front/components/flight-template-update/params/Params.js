import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import CycloParams from 'controls/cyclo-params/CycloParams';

class Params extends Component {
  render () {
    let templateAnalogParams = this.props.params
      .filter((item) => (item.type === 'ap'));

    let templateBinaryParams = this.props.params
      .filter((item) => (item.type === 'bp'));

    return (
      <div>
        <CycloParams
          flightId={ this.props.flightId }
          colorPickerEnabled={ false }
          chosenAnalogParams={ templateAnalogParams }
          chosenBinaryParams={ templateBinaryParams }
          storeCheckstate={ true }
        />
      </div>
    );
  }
}

Params.propTypes = {
  params: PropTypes.array.isRequired
};

function mapStateToProps(state) {
  return {
    params: state.flightTemplate.params
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(Params);
