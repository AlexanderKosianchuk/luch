import './user-options.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import _isEmpty from 'lodash.isempty';

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
            let label = objectKey;
            if (this.props.i18n[objectKey]) {
                label = this.props.i18n[objectKey];
            }

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
            <button className='btn btn-default' onClick={ this.onClick.bind(this) } >
                { this.props.i18n.save }
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
            <div className='user-options container-fluid'>
                <h4 className='user-options__header'>
                    { this.props.i18n.userOptions }
                </h4>
                { content }
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
