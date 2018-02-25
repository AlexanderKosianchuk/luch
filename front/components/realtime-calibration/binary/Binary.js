import './binary.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import BinaryTileItem from 'components/realtime-calibration/binary-tile-item/BinaryTileItem';

class Binary extends Component {
  buildTile() {
    let bp = this.props.params.containerBinaryParams;

    let binaryData = bp.map((item, index) => {
      if (this.props.data.length === 0) {
        return {
          ...item, ...{
            value: false
          }
        };
      }

      let lastTriggeredBp = this.props.data[this.props.data.length - 1];
      let searchedIndex = lastTriggeredBp.findIndex((binary) => {
        return item.id === binary.id;
      });

      if (searchedIndex !== -1) {
        return {
          ...item, ...{
            value: true
          }
        };
      }

      return {
        ...item, ...{
          value: false
        }
      };
    });

    return binaryData.map((item, index) => {
      return (<BinaryTileItem
        key={ index }
        param={ item }
      />);
    });
  }

  render() {
    return (
      <div className='realtime-calibration-binary'>
        <div className='realtime-calibration-binary__header'>
          { (this.props.params.containerBinaryParams.length > 0) && (
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
