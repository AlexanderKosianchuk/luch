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

class Settings extends React.Component {
    buildContent() {
        let settings = this.props.settings;
        if (_isEmpty(settings)) {
            this.props.getSettings();
            return <ContentLoader/>;
        }

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
        this.props.setSettings(this.props.settings);
        this.props.goBack(-2); // -2 because # in url
    }

    render () {
        let content = this.buildContent();

        return (
            <div>
                <MainPage />
                <div className='settings container-fluid'>
                    <h4 className='settings__header'>
                        <Translate value='settings.options'/>
                    </h4>
                    { content }
                </div>
            </div>
        );
    }
}

function mapStateToProps (state) {
    return {
        settings: state.settings
    };
}

function mapDispatchToProps(dispatch) {
    return {
        getSettings: bindActionCreators(getSettings, dispatch),
        changeSettingsItem: bindActionCreators(changeSettingsItem, dispatch),
        setSettings: bindActionCreators(setSettings, dispatch),
        goBack: bindActionCreators(
            () => { return((dispatch) => { dispatch(goBack()) }) },
            dispatch
        ),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Settings);
