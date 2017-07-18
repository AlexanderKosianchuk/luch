import './login.sass';

import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { Translate } from 'react-redux-i18n';

import login from 'actions/particular/login';

class Login extends Component {
    constructor(props) {
        super(props);

        this.state = {
            message: ''
        }
    }

    auth() {
        this.props.login({
            login: this.loginInput.value,
            pass: this.passInput.value
        }).catch((response) => {
            this.setState({
                message: response.message
            });
        })
    }

    render() {
        return (
            <div className='login container-fluid'>

                <div className='row'>
                    <div className='login__logo'></div>
                </div>

                <div className='login__form-wrapper'>
                    <div className='row'>
                        <div className='login__header'>
                            <Translate value='login.loginForm'/>
                        </div>
                    </div>

                    <div className={ 'row login__message-row ' + ((this.state.message === '') ? 'is-hidden' : '') }>
                        <div className='col-sm-offset-4 col-sm-4'>
                            <Translate value={ 'login.' + this.state.message }/>
                        </div>
                    </div>

                    <div className='row login__row'>
                        <div className='col-sm-offset-4 col-sm-4'>
                            <Translate value='login.userName'/>
                            <input name='login' ref={ (textInput) => { this.loginInput = textInput; }} className='form-control' type='text'/>
                        </div>
                    </div>

                    <div className='row login__row'>
                        <div className='col-sm-offset-4 col-sm-4'>
                            <Translate value='login.password'/>
                            <input name='pass' ref={ (textInput) => { this.passInput = textInput; }} className='form-control' type='text'/>
                        </div>
                    </div>

                    <div className='row login__row'>
                        <button onClick={ this.auth.bind(this) }className='btn btn-default login__btn' >
                            <Translate value='login.authorize'/>
                        </button>
                    </div>
                </div>
            </div>
        );
    }
}

function mapDispatchToProps(dispatch) {
    return {
        login: bindActionCreators(login, dispatch)
    }
}

export default connect(() => { return {} }, mapDispatchToProps)(Login);
