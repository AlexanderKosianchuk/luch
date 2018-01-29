import './fdr-template-selector.sass';

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

class FdrTemplateSelector extends Component {
  componentWillMount() {
    if ((typeof this.props.methodHandler === 'object')
      && (this.props.methodHandler.getSelectedId === null)
    ) {
      this.props.methodHandler.getSelectedId = this.getSelectedId.bind(this);
    }
  }

  componentDidMount() {
    this.fetchTemplates();
  }

  componentDidUpdate() {
    this.fetchTemplates();
  }

  fetchTemplates() {
    if (this.props.pending === null) {
      this.props.request(
        ['fdrTemplate', 'getAll'],
        'get',
        'FDR_TEMPLATES',
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
          defaultIndex = 0;
        }

        if (_isFunction(this.props.handleChange)) {
          this.props.handleChange(resp[defaultIndex]);
        }

        this.props.transmit(
          'CHOOSE_FDR_TEMPLATE',
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
        text: this.getTemplateName(item.name, item.servicePurpose),
        id: item.id
      };
    });
  }

  getTemplateName(name, servicePurpose) {
    let needTranslate = false;

    Object.keys(servicePurpose).forEach((key) => {
      if (servicePurpose[key] === true) {
        needTranslate = true;
      }
    });

    if (needTranslate) {
      return I18n.t('fdrTemplateSelector.item.' + name);
    }

    return name;
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

    if (_isFunction(this.props.handleChange)) {
      this.props.handleChange(chosen);
    }

    this.props.transmit('CHOOSE_FDR_TEMPLATE', chosen);
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
      <div className={ 'fdr-template-selector ' + (isHidden ? 'is-hidden' : '') }>
        <Select
          className='fdr-template-selector__select'
          data={ this.buildList() }
          value={ chosen }
          onSelect={ this.handleSelect.bind(this) }
          ref={(select) => { this.selectTemplate = select; }}
          options={{
            placeholder: I18n.t('fdrTemplateSelector.placeholder'),
            allowClear: allowClear
          }}
        />
      </div>
    );
  }
}

FdrTemplateSelector.propTypes = {
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
    pending: state.fdrTemplates.pending,
    templates: state.fdrTemplates.items,
    chosen: state.fdrTemplates.chosenItems,
    chosenFdr: state.fdrs.chosen
  }
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    transmit: bindActionCreators(transmit, dispatch),
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(FdrTemplateSelector);
