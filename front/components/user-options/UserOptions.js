import './user-options.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import _isEmpty from 'lodash.isempty';
import { Translate, I18n } from 'react-redux-i18n';

import MainPage from 'components/main-page/MainPage';
import ContentLoader from 'components/content-loader/ContentLoader';
import UserOptionsItem from 'components/user-options-item/UserOptionsItem';
import getUserOptionsAction from 'actions/getUserOptions';
import changeUserOptionItemAction from 'actions/changeUserOptionItem';
import setUserOptionsAction from 'actions/setUserOptions';

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
        getUserOptions: bindActionCreators(getUserOptionsAction, dispatch),
        changeUserOptionItem: bindActionCreators(changeUserOptionItemAction, dispatch),
        setUserOptions: bindActionCreators(setUserOptionsAction, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(UserOptions);
