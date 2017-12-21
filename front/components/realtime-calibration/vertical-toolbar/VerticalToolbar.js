import './vertical-toolbar.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import FdrSelector from 'controls/fdr-selector/FdrSelector';
import CalibrationSelector from 'controls/calibration-selector/CalibrationSelector';

import request from 'actions/request';

class VerticalToolbar extends Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <div className='realtime-calibration-vertical-toolbar'
            >
                <div className='realtime-calibration-vertical-toolbar__connection-params'>
                    <Translate value='realtimeCalibration.verticalToolbar.connectionParams'/>
                </div>
                <div className='realtime-calibration-vertical-toolbar__label'>
                    <Translate value='realtimeCalibration.verticalToolbar.fdrType'/>
                </div>
                <div>
                    <ul className='realtime-calibration-vertical-toolbar__fdr-type'>
                        <FdrSelector />
                        <CalibrationSelector/>
                    </ul>
                </div>
            </div>
        );
    }
}

function mapStateToProps() {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(VerticalToolbar);
