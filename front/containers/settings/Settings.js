import './settings.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import _isEmpty from 'lodash.isempty';
import { Translate, I18n } from 'react-redux-i18n';
import { goBack } from 'react-router-redux';

import MainPage from 'controls/main-page/MainPage';
import ContentLoader from 'controls/content-loader/ContentLoader';
import Item from 'components/settings/item/Item';

import getSettings from 'actions/getSettings';
import changeSettingsItem from 'actions/changeSettingsItem';
import setSettings from 'actions/setSettings';
import redirect from 'actions/redirect';

class Settings extends React.Component {
    componentDidMount() {
        this.props.getSettings();
    }

    buildContent() {
        if (this.props.pending !== false) {
            return <ContentLoader/>;
        }

        let settings = this.props.settings.items;
        let options = Object.keys(settings).map((objectKey, index) => {
            let label = I18n.t('settings.' + objectKey);

            return (
                <Item
                    id = { objectKey }
                    key = { objectKey }
                    label = { label }
                    value = { settings[objectKey] }
                    changeValue = { this.props.changeSettingsItem }
                />
            );
        });

        options.push(
            <button key='settingsButton' className='btn btn-default' onClick={ this.onClick.bind(this) } >
                <Translate value='settings.save'/>
            </button>
        );

        return options;
    }

    onClick() {
        this.props.setSettings(this.props.settings.items);
        this.props.redirect('/');
    }

    render () {
        return (
            <div>
                <MainPage />
                <div className='settings container-fluid'>
                    <h4 className='settings__header'>
                        <Translate value='settings.options'/>
                    </h4>
                    { this.buildContent() }
                </div>
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
        getSettings: bindActionCreators(getSettings, dispatch),
        changeSettingsItem: bindActionCreators(changeSettingsItem, dispatch),
        setSettings: bindActionCreators(setSettings, dispatch),
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Settings);
