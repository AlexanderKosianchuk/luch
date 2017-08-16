import React, { Component } from 'react';
import { connect } from 'react-redux';
import ee from 'event-emitter';
import Menu from 'controls/menu/Menu';

import Toolbar from 'components/user-form/toolbar/Toolbar';
import Form from 'components/user-form/form/Form';

class UserForm extends Component {
    constructor(props) {
        super(props);

        let emitter = ee();
        this.submit = () => emitter.emit('user-form-submit');
        this.onSubmit = (callback) => emitter.on('user-form-submit', callback);
        this.offSubmit = () => emitter.off('user-form-submit');
    }

    render() {
        return (
            <div>
                <Menu/>
                <Toolbar
                    type={ this.props.type }
                    submit={ this.submit }
                />
                <Form
                    type={ this.props.type }
                    userId={ this.props.userId }
                    onSubmit={ this.onSubmit }
                    offSubmit={ this.offSubmit }
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
