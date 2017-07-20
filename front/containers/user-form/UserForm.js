import React, { Component } from 'react';
import { connect } from 'react-redux';

import Menu from 'controls/menu/Menu';

import Toolbar from 'components/user-form/toolbar/Toolbar';
import Form from 'components/user-form/form/Form';

class UserForm extends Component {
    render() {
        return (
            <div>
                <Menu/>
                <Toolbar type={ this.props.type } />
                <Form type={ this.props.type } userId={ this.props.userId } />
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        type: ownProps.match.params.type || 'create',
        userId: parseInt(ownProps.match.params.userId) || null
    };
}

export default connect(mapStateToProps, () => { return {} })(UserForm);
