import './form.sass'

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate, I18n } from 'react-redux-i18n';
import FileInput from 'react-file-input';
import PropTypes from 'prop-types';

import Row from 'components/user-form/row/Row';
import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';
import redirect from 'actions/redirect';

class Form extends Component {
    constructor(props) {
        super(props);

        this.controls = [
            {
                key: 'login',
                label: I18n.t('userForm.form.login'),
                type: 'text',
                placeholder: ''
            },
            {
                key: 'name',
                label: I18n.t('userForm.form.name'),
                type: 'text',
                placeholder: ''
            },
            {
                key: 'email',
                label: I18n.t('userForm.form.email'),
                type: 'email',
                placeholder: 'email@email.com'
            },
            {
                key: 'phone',
                label: I18n.t('userForm.form.phone'),
                type: 'text',
                placeholder: ''
            },
            {
                key: 'pass',
                label: I18n.t('userForm.form.pass'),
                type: 'password',
                placeholder: ''
            },
            {
                key: 'repeatPass',
                label: I18n.t('userForm.form.repeatPass'),
                type: 'password',
                placeholder: ''
            },
            {
                key: 'organization',
                label: I18n.t('userForm.form.organization'),
                type: 'text',
                placeholder: ''
            }
        ];

        this.state = {
            pending: null,
            login: '',
            name: '',
            email: '',
            phone: '',
            pass: '',
            repeatPass: '',
            organization: ''
        }

        let form = this.props.form();
        Object.assign(form, {
                get: () => {
                    return {
                        login: this.state.login,
                        name: this.state.name,
                        email: this.state.email,
                        phone: this.state.phone,
                        pass: this.state.pass,
                        repeatPass: this.state.repeatPass,
                        organization: this.state.organization
                    };
                }
            }
        );
    }

    componentDidMount() {
        if ((this.props.type === 'edit') && (this.state.pending !== false)) {
            this.props.request(
                ['users', 'getUser'],
                'USER',
                'get',
                { id: this.props.userId }
            ).then(() => {
                this.setState({ pending: false });
            })
        }
    }

    buildRows() {
        function isEven(n) {
          n = Number(n);
          return n === 0 || !!(n && !(n%2));
        }

        let rows = [];
        let rowItems = [];

        this.controls.forEach((item, index) => {
            rowItems.push({ ...item, ...{ value: (this.state[item.key] || '') } });
            if (index % 2) {
                rows.push(<Row key={ index }
                    controls={ rowItems }
                    handler={ this.handleChange.bind(this) }
                />);
                rowItems = [];
            }

            if ((index === this.controls.length - 1) && !(index % 2)) {
                rows.push(<Row key={ index }
                    controls={ rowItems }
                    handler={ this.handleChange.bind(this) }
                />);
            }
        });

        return rows;
    }

    handleChange(event) {
        let element = event.target;
        let key = element.getAttribute('data-key');
        let value = element.value;

        if (this.state.hasOwnProperty(key)) {
            this.setState({ [key]: value });
        }
    }

    buildForm() {
        return (
            <form className='user-form-form__container form-horizontal'>
                { this.buildRows() }
                <div className='row'>
                    <div className='col-md-6'>
                        <div className='form-group'>
                          <label className='col-sm-2 control-label'><Translate value='userForm.form.role'/></label>
                          <div className='col-sm-10'>
                              <div className='checkbox'>
                                <label>
                                    <input type='radio' name='role' onChange={ this.handleChange.bind(this) } />
                                    <Translate value='userForm.form.admin'/>
                                </label>
                                <label>
                                    <input type='radio' name='role' onChange={ this.handleChange.bind(this) }/>
                                    <Translate value='userForm.form.moderator'/>
                                </label>
                                <label>
                                    <input type='radio' name='role' onChange={ this.handleChange.bind(this) } checked/>
                                    <Translate value='userForm.form.user'/>
                                </label>
                              </div>
                          </div>
                        </div>
                    </div>
                    <div className='col-md-6'>
                        <div className='form-group'>
                          <label className='col-sm-2 control-label'><Translate value='userForm.form.logo'/></label>
                          <div className='col-sm-10'>
                              <FileInput
                                 className="btn btn-default"
                                 name="flightFile"
                                 placeholder={ I18n.t('userForm.form.chooseFile') }
                                 onChange={ this.handleChange.bind(this) }
                               />
                          </div>
                        </div>
                    </div>
                </div>
            </form>
        );
    }

    buildBody() {
        if ((this.props.type === 'edit') && (this.state.pending !== false)) {
            return <ContentLoader/>
        }

        if (this.props.type === 'create') {
            return this.buildForm();
        }
    }

    render() {
        return (
            <div className='user-form-form'
                ref={(container) => { this.container = container; }}
            >
                { this.buildBody() }
            </div>
        );
    }
}

Form.propTypes = {
    type: PropTypes.string.isRequired,
    userId: PropTypes.number,
};

function mapStateToProps(state) {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch),
        redirect: bindActionCreators(redirect, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Form);
