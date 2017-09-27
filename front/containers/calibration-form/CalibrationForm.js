import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import Menu from 'controls/menu/Menu';

import Toolbar from 'components/calibration-form/toolbar/Toolbar';
import Form from 'components/calibration-form/form/Form';

import request from 'actions/request';

class CalibrationForm extends Component {
    componentDidMount() {
        if (this.props.action === 'update') {
            this.props.request(
                ['calibration', 'getCalibrationById'],
                'get',
                'CALIBRATION',
                { id: this.props.calibrationId }
            );
        }

        if ((this.props.action === 'create')
            && (this.props.cycloFdrId !== this.props.fdrId)
        ) {
            this.props.request(
                ['calibration', 'getCalibrationParams'],
                'get',
                'CALIBRATION_PARAMS',
                { fdrId: this.props.fdrId }
            );
        }
    }

    shouldComponentUpdate(nextProps, nextState) {
        if (nextProps.pending !== this.props.pending) {
            return false;
        }

        return true;
    }

    render() {
        return (
            <div>
                <Menu />
                <Toolbar
                    action={ this.props.action }
                    fdrId={ this.props.fdrId }
                    calibrationId={ this.props.calibrationId }
                />
            </div>
        );
    }
}

/*<Form
    action={ this.props.action }
    fdrId={ this.props.fdrId }
    calibrationId={ this.props.calibrationId }
/>*/

function mapStateToProps(state, ownProps) {
    return {
        pending: state.calibrations.pending,
        fdrId: parseInt(ownProps.match.params.fdrId) || null,
        calibrationId: parseInt(ownProps.match.params.calibrationId) || null,
        action: (ownProps.location.pathname.indexOf('update') > -1) ? 'update' : 'create'
    };
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(CalibrationForm);
