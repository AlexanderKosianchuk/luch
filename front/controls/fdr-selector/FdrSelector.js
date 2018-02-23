import './fdr-selector.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { I18n } from 'react-redux-i18n';
import _isFunction from 'lodash.isfunction';

import Select from 'react-select2-wrapper';
import 'react-select2-wrapper/css/select2.min.css';

import request from 'actions/request';
import transmit from 'actions/transmit';

class FdrSelector extends Component {
  componentWillMount() {
    if ((typeof this.props.methodHandler === 'object')
      && (this.props.methodHandler.getSelectedFdrId === null)
    ) {
      this.props.methodHandler.getSelectedFdrId = this.getSelectedId.bind(this);
    }
  }

  componentDidMount() {
    if (this.props.pending === null) {
      this.props.request(
        ['fdr', 'getFdrs'],
        'get',
        'FDRS'
      ).then((resp) => {
        if (resp.length < 1) {
          return;
        }

        if (_isFunction(this.props.handleChange)) {
          this.props.handleChange(resp[0]);
        }

        this.props.transmit('CHOOSE_FDR', resp[0])
          .then(() => {
            this.props.transmit('CLEAR_FDR_TEMPLATES');
          });
      });
    } else {
      if (Number.isInteger(parseInt(this.props.chosenFdrId))
          && (this.props.chosenFdrId !== this.props.chosen.id)
      ) {

        let chosenIndex = this.props.fdrs.findIndex((item) => {
          return item.id === this.props.chosenFdrId;
        });

        if (chosenIndex === -1) {
          return;
        }

        if (_isFunction(this.props.handleChange)) {
          this.props.handleChange(this.props.fdrs[chosenIndex]);
        }

        this.props.transmit(
          'CHOOSE_FDR',
          this.props.fdrs[chosenIndex]
        ).then(() => {
          this.props.transmit('CLEAR_FDR_TEMPLATES');
        });
      }
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

    if (_isFunction(this.props.handleChange)) {
      this.props.handleChange(chosen);
    }

    this.props.transmit('CHOOSE_FDR', chosen)
      .then(() => {
        this.props.transmit('CLEAR_FDR_TEMPLATES');
      });
  }

  getSelectedId() {
    return this.props.chosen.id;
  }

  render() {
    let isHidden = true;

    if (this.props.fdrs
      && (this.props.fdrs.length > 0)
      && (this.props.chosen)
    ) {
      isHidden = false;
    }

    let chosen = this.props.chosenFdrId || this.props.chosen.id || null;
    let allowClear = false;
    if (this.props.isClear) {
      chosen = null;
      allowClear = true;
    }

    return (
      <li className={ 'fdr-selector ' + (isHidden ? 'is-hidden' : '') }>
        <a href='#'><Select
          className='fdr-selector__select'
          data={ this.buildList() }
          value={ chosen }
          onSelect={ this.handleSelect.bind(this) }
          ref={(select) => { this.selectFdrType = select; }}
          options={{
            placeholder: I18n.t('fdrSelector.placeholder'),
            allowClear: allowClear
          }}
        />
        </a>
      </li>
    );
  }
}

FdrSelector.propTypes = {
  isClear: PropTypes.bool,
  chosenFdrId: PropTypes.number,
  handleChange: PropTypes.func,
  methodHandler: PropTypes.object,

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
