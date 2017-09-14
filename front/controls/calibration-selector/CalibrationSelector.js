import './calibration-selector.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import Select from 'react-select2-wrapper';
import 'react-select2-wrapper/css/select2.min.css';

import request from 'actions/request';

class CalibrationSelector extends Component {
    componentDidMount() {
        if (this.props.pending === null) {
            this.props.request(
                ['calibration', 'getCalibrations'],
                'CALIBRATIONS',
                'get'
            );
        }
    }

    buildList(calibrationList) {
        if (!this.props.calibrations || this.props.calibrations.length === 0) {
            return [];
        }

        return this.props.calibrations.map((item) => {
            return {
                text: item.name,
                id: item.id
            };
        });
    }

    handleSelect() {
        if (!this.selectedCalibration.el[0]) {
            return;
        }

        let el = this.selectedCalibration.el[0];
        let val = parseInt(el.options[el.selectedIndex].value);

        let index = this.props.selectedCalibration.findIndex((item) => {
            return item.id === val;
        });

        if (index === -1) {
            return;
        }

        let chosen = this.props.calibrations[index];
        this.props.transmit('CHOOSE_CALIBRATION', chosen);
    }

    render() {
        let isHidden = true;

        if (this.props.fdrs
            && (this.props.fdrs.length > 0)
            && (this.props.chosen)
        ) {
            isHidden = false;
        }

        return (
          <li className={ "calibration-selector " + (isHidden ? 'is-hidden' : '') }>
              <a href="#"><Select
                  data={ this.buildList(this.props.calibrations) }
                  value={ this.props.chosen.id || null }
                  onSelect={ this.handleSelect.bind(this) }
                  ref={(select) => { this.selectedCalibration = select; }}
                />
              </a>
          </li>
        );
    }
}


CalibrationSelector.propTypes = {
    handleReady: PropTypes.func,

    pending:  PropTypes.bool,
    calibrations: PropTypes.array,
    chosen: PropTypes.object,

    request: PropTypes.func,
    transmit: PropTypes.func
};

function mapStateToProps(state) {
    return {
        pending: state.calibrations.pending,
        calibrations: state.calibrations.items,
        chosen: state.calibrations.chosen
    }
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(CalibrationSelector);
