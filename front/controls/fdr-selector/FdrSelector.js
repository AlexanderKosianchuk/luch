import './fdr-selector.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import Select from 'react-select2-wrapper';
import 'react-select2-wrapper/css/select2.min.css';

import request from 'actions/request';
import transmit from 'actions/transmit';

class FdrSelector extends Component {
    componentDidMount() {
        if (this.props.pending === null) {
            this.props.request(
                ['fdr', 'getFdrs'],
                'FDRS',
                'get'
            );
        }
    }

    buildList() {
        if (!this.props.fdrs || this.props.fdrs.length === 0) {
            return [];
        }

        return this.props.fdrs.map((item) => {
            return {
                text: item.name,
                id: item.id
            };
        });
    }

    handleSelect() {
        if (!this.selectFdrType.el[0]) {
            return;
        }

        let el = this.selectFdrType.el[0];
        let val = parseInt(el.options[el.selectedIndex].value);

        let index = this.props.fdrs.findIndex((item) => {
            return item.id === val;
        });

        if (index === -1) {
            return;
        }

        let chosen = this.props.fdrs[index];
        this.props.transmit('CHOOSE_FDR', chosen);

        if (!chosen.calibrations
            || (chosen.calibrations.length === 0)
        ) {
            return
        }

        this.props.transmit('CHOOSE_CALIBRATION', chosen.calibrations[0]);
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
          <li className={ "fdr-selector " + (isHidden ? 'is-hidden' : '') }>
              <a href="#"><Select
                  data={ this.buildList() }
                  value={ this.props.chosen.id || null }
                  onSelect={ this.handleSelect.bind(this) }
                  ref={(select) => { this.selectFdrType = select; }}
                />
              </a>
          </li>
        );
    }
}

FdrSelector.propTypes = {
    handleReady: PropTypes.func,

    pending:  PropTypes.bool,
    fdrs: PropTypes.array,
    chosen: PropTypes.object,

    request: PropTypes.func,
    transmit: PropTypes.func
};

function mapStateToProps(state) {
    return {
        pending: state.fdrs.pending,
        fdrs: state.fdrs.items,
        chosen: state.fdrs.chosen
    }
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch),
        transmit: bindActionCreators(transmit, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FdrSelector);
