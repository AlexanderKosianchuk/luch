import './list.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import _isEmpty from 'lodash.isempty';
import { Translate, I18n } from 'react-redux-i18n';
import { goBack } from 'react-router-redux';

import ContentLoader from 'controls/content-loader/ContentLoader';
import Item from 'components/settings/item/Item';

import request from 'actions/request';
import transmit from 'actions/transmit';
import redirect from 'actions/redirect';

class List extends React.Component {
  componentDidMount() {
    this.props.request(
      ['users', 'getUserSettings'],
      'get',
      'USER_SETTINGS',
    );
  }

  buildContent() {
    if (this.props.pending !== false) {
      return <ContentLoader/>;
    }

    let settings = this.props.settings.items;
    let options = Object.keys(settings).map((objectKey, index) => {
      let label = I18n.t('settings.list.' + objectKey);

      return (
        <Item
          id = { objectKey }
          key = { objectKey }
          label = { label }
          value = { settings[objectKey] }
          changeValue = { this.changeSettingsItem.bind(this) }
        />
      );
    });

    options.push(
      <button key='settingsButton' className='btn btn-default' onClick={ this.onClick.bind(this) } >
        <Translate value='settings.list.save'/>
      </button>
    );

    return options;
  }

  changeSettingsItem(payload) {
    this.props.transmit(
      'CHANGE_SETTINGS_ITEM',
      payload
    )
  }

  onClick() {
    this.props.request(
      ['users', 'setUserSettings'],
      'put',
      'USER_SETTINGS',
      { settings: this.props.settings.items }
    ).then(() => this.props.redirect('/'));
  }

  render () {
    return (
      <div className='settings-list-list container-fluid'>
        <h4 className='settings__header'>
          <Translate value='settings.list.options'/>
        </h4>
        { this.buildContent() }
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    pending: state.settings.pending,
    settings: state.settings
  };
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    transmit: bindActionCreators(transmit, dispatch),
    redirect: bindActionCreators(redirect, dispatch)
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(List);
