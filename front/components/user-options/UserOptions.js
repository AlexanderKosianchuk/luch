import './user-options.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import _isEmpty from 'lodash.isempty';
import { Translate, I18n } from 'react-redux-i18n';
import { goBack } from 'react-router-redux';

import MainPage from 'components/main-page/MainPage';
import ContentLoader from 'components/content-loader/ContentLoader';
import UserOptionsItem from 'components/user-options-item/UserOptionsItem';

import getUserOptions from 'actions/getUserOptions';
import changeUserOptionItem from 'actions/changeUserOptionItem';
import setUserOptions from 'actions/setUserOptions';

class UserOptions extends React.Component {
    buildContent() {
        let userOptions = this.props.userOptions;
        if (_isEmpty(userOptions)) {
            this.props.getUserOptions();
            return <ContentLoader/>;
        }

        let options = Object.keys(userOptions).map((objectKey, index) => {
            let label = I18n.t(objectKey);

            return (
                <UserOptionsItem
                    id = { objectKey }
                    key = { objectKey }
                    label = { label }
                    value = { userOptions[objectKey] }
                    changeValue = { this.props.changeUserOptionItem }
                />
            );
        });

        options.push(
            <button key='userOptionsButton' className='btn btn-default' onClick={ this.onClick.bind(this) } >
                <Translate value='userOptions.save'/>
            </button>
        );

        return options;
    }

    onClick() {
        this.props.setUserOptions(this.props.userOptions);
        this.props.goBack();
    }

    render () {
        let content = this.buildContent();

        return (
            <div>
                <MainPage />
                <div className='user-options container-fluid'>
                    <h4 className='user-options__header'>
                        <Translate value='userOptions.options'/>
                    </h4>
                    { content }
                </div>
            </div>
        );
    }
}

function mapStateToProps (state) {
    return {
        userOptions: state.userOptions
    };
}

function mapDispatchToProps(dispatch) {
    return {
        getUserOptions: bindActionCreators(getUserOptions, dispatch),
        changeUserOptionItem: bindActionCreators(changeUserOptionItem, dispatch),
        setUserOptions: bindActionCreators(setUserOptions, dispatch),
        goBack: bindActionCreators(
            () => { return((dispatch) => { dispatch(goBack()) }) },
            dispatch
        ),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(UserOptions);
