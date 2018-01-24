import './template-selector.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { I18n } from 'react-redux-i18n';

import Select from 'react-select2-wrapper';
import 'react-select2-wrapper/css/select2.min.css';

import request from 'actions/request';
import transmit from 'actions/transmit';

class TemplateSelector extends Component {
  componentWillMount() {
    if ((typeof this.props.methodHandler === 'object')
      && (this.props.methodHandler.getSelectedId === null)
    ) {
      this.props.methodHandler.getSelectedId = this.getSelectedId.bind(this);
    }
  }

  componentDidMount() {
    if (this.props.pending === null) {
      this.props.request(
        ['templates', 'getFdrTemplates'],
        'get',
        'FLIGHT_TEMPLATES',
        { fdrId: this.props.fdrId }
      ).then((resp) => {
        if (resp.length < 1) {
          return;
        }

        let defaultIndex = resp.findIndex((item)  => {
          return item.servicePurpose
            && item.servicePurpose.isDefault === true;
        });

        if (defaultIndex === -1) {
          return;
        }

        this.props.transmit(
          'CHOOSE_TEMPLATE',
          { id: resp[defaultIndex].id }
        );
      });
    }
  }

  buildList() {
    if (!this.props.templates || this.props.templates.length === 0) {
      return [];
    }

    return this.props.templates.map((item) => {
      return {
        text: item.name,
        id: item.id
      };
    });
  }

  handleSelect() {
    if (!this.selectTemplate.el[0]) {
      return;
    }

    let el = this.selectTemplate.el[0];
    let val = parseInt(el.options[el.selectedIndex].value);

    let index = this.props.templates.findIndex((item) => {
      return item.id === val;
    });

    if (index === -1) {
      return;
    }

    let chosen = this.props.templates[index];

    if (typeof this.props.handleChange === 'function') {
      this.props.handleChange(chosen);
      return;
    }

    this.props.transmit('CHOOSE_TEMPLATE', chosen);
  }

  getSelectedId() {
    return this.props.chosen.id;
  }

  render() {
    let isHidden = true;

    if (this.props.templates
      && (this.props.templates.length > 0)
      && (this.props.chosen)
    ) {
      isHidden = false;
    }

    let chosen = null;
    let allowClear = false;
    if (this.props.isClear) {
      allowClear = true;
    } else {
      if (this.props.chosenTemplateId) {
        chosen = this.props.chosenTemplateId;
      } else if (this.props.chosen.length > 0) {
        chosen = this.props.chosen[0].id;
      }
    }

    return (
      <div className={ 'template-selector ' + (isHidden ? 'is-hidden' : '') }>
        <Select
          className='template-selector__select'
          data={ this.buildList() }
          value={ chosen }
          onSelect={ this.handleSelect.bind(this) }
          ref={(select) => { this.selectTemplate = select; }}
          options={{
            placeholder: I18n.t('templateSelector.placeholder'),
            allowClear: allowClear
          }}
        />
      </div>
    );
  }
}

TemplateSelector.propTypes = {
  fdrId: PropTypes.number,
  isClear: PropTypes.bool,
  chosenTemplateId: PropTypes.number,
  handleChange: PropTypes.func,
  methodHandler: PropTypes.object,

  pending:  PropTypes.bool,
  templates: PropTypes.array,
  chosen: PropTypes.array,

  request: PropTypes.func,
  transmit: PropTypes.func
};

function mapStateToProps(state) {
  return {
    pending: state.flightTemplates.pending,
    templates: state.flightTemplates.items,
    chosen: state.flightTemplates.chosenItems
  }
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    transmit: bindActionCreators(transmit, dispatch),
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(TemplateSelector);
