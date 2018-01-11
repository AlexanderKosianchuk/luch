import './params-container.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import TileItem from 'components/realtime-calibration/tile-item/TileItem';

class ParamsContainer extends Component {
    getIndexById (id, array) {
        let itemIndex = null;
        array.forEach((item, index) => {
            if (item.id === id) itemIndex = index;
        });

        return itemIndex; // or undefined
    };

    buildTile() {
        let analogParamsTile = [];

        this.props.realtimeCalibrationParams
            .chosenContainerAnalogParams
            .forEach((item, index) => {
                let itemId = item.id;
                let data = this.props.data;
                let cyclo = this.props.fdrCyclo;
                let value = 0;
                let frame = [];
                let paramIndex = this.getIndexById(itemId, cyclo.analogParams);

                if (!Number.isInteger(paramIndex)) {
                    return false;
                }

                let param = cyclo.analogParams[paramIndex];

                if (itemId
                    && (data.length > 0)
                    && data[data.length - 1][itemId]
                ) {
                    value = data[data.length - 1][itemId];
                }

                analogParamsTile.push(<TileItem
                    key={ index }
                    value={ value }
                    paramColor={ param.color }
                    name={ param.name }
                    code={ param.code }
                />);
            });

        return analogParamsTile;
    }

    render() {
        return (
            <div className='realtime-calibration-params-container'>
                { this.buildTile() }
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {
        currentFrame: state.realtimeCalibrationData.currentFrame,
        data: state.realtimeCalibrationData.data,
        realtimeCalibrationParams: state.realtimeCalibrationParams,
        fdrCyclo: state.fdrCyclo
    };
}

function mapDispatchToProps(dispatch) {
    return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(ParamsContainer);
