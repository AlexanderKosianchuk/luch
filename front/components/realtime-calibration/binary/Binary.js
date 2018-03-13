import './binary.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import TileItem from 'components/realtime-calibration/tile-item/TileItem';

class Binary extends Component {
  buildTile() {
    return this.props.params.binary.map((item, index) => {
      let value = false;

      if (this.props.data.length > 0) {
        let lastTriggeredBp = this.props.data[this.props.data.length - 1];
        let searchedIndex = lastTriggeredBp.findIndex((binary) => {
          return item.id === binary.id;
        });

        if (searchedIndex !== -1) {
          value = true;
        }
      }

      return (<TileItem
        key={ index }
        param={ item }
        value={ value }
        canChartDisplay={ true }
        onlyBinaryValue={ true }
      />);
    });
  }

  render() {
    return (
      <div className='realtime-calibration-binary'>
        <div className='realtime-calibration-binary__header'>
          { (this.props.params.binary.length > 0) && (
            <Translate value='realtimeCalibration.binary.header' />
          )}
        </div>
        <div className='realtime-calibration-binary__container'>
          { this.buildTile() }
        </div>
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    currentFrame: state.realtimeCalibrationData.currentFrame,
    data: state.realtimeCalibrationData.binary,
    params: state.realtimeCalibrationParams
  };
}

function mapDispatchToProps(dispatch) {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(Binary);
