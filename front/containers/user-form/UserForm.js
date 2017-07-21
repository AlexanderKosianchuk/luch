import React, { Component } from 'react';
import { connect } from 'react-redux';

import Menu from 'controls/menu/Menu';

import Toolbar from 'components/user-form/toolbar/Toolbar';
import Form from 'components/user-form/form/Form';

class UserForm extends Component {
    construct() {
        this.form = {
            get: null
        };
    }

    /*
    This function is used to pass to Form this.form object.
    Form redefines get method to return its state.
    Toolbar uses get method to get form state after "Save" button click
    */
    form() { return this.form; }

    render() {
        return (
            <div>
                <Menu/>
                <Toolbar
                    type={ this.props.type }
                    form={ this.form }
                />
                <Form
                    type={ this.props.type }
                    userId={ this.props.userId }
                    form={ this.form }
                />
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
