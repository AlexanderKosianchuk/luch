import './login.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { Translate } from 'react-redux-i18n';

import login from 'actions/particular/login';

function UserLogin (props) {
    let loginInput;
    let passInput;

    let auth = function() {
        props.login({
            login: loginInput.value,
            pass: passInput.value
        })
    }

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

                <div className='row login__row'>
                    <div className='col-sm-4'></div>
                    <div className='col-sm-4'>
                        <Translate value='login.userName'/>
                        <input name='login' ref={ (textInput) => { loginInput = textInput; }} className='form-control' type='text'/>
                    </div>
                    <div className='col-sm-4'></div>
                </div>

                <div className='row login__row'>
                    <div className='col-sm-4'></div>
                    <div className='col-sm-4'>
                        <Translate value='login.password'/>
                        <input name='pass' ref={ (textInput) => { passInput = textInput; }} className='form-control' type='text'/>
                    </div>
                    <div className='col-sm-4'></div>
                </div>

                <div className='row login__row'>
                    <button onClick={ auth }className='btn btn-default login__btn' >
                        <Translate value='login.authorize'/>
                    </button>
                </div>
            </div>
        </div>
    );
}

function mapDispatchToProps(dispatch) {
    return {
        login: bindActionCreators(login, dispatch)
    }
}

export default connect(() => { return {} }, mapDispatchToProps)(UserLogin);
