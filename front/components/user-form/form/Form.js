import './form.sass'

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate, I18n } from 'react-redux-i18n';
import PropTypes from 'prop-types';
import _isEqual from 'lodash.isequal';

import FileInput from 'controls/file-input/FileInput';

import Row from 'components/user-form/row/Row';
import RoleRadio from 'components/user-form/role-radio/RoleRadio';
import AvaliableFdrsSelector from 'components/user-form/avaliable-fdrs-selector/AvaliableFdrsSelector';
import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';
import redirect from 'actions/redirect';

const EDIT_TYPE = 'edit';
const CREATE_TYPE = 'create';

class Form extends Component {
  constructor(props) {
    super(props);

    this.controls = [
      {
        key: 'login',
        label: I18n.t('userForm.form.login') + '*',
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
        label: I18n.t('userForm.form.pass') + '*',
        type: 'password',
        placeholder: ''
      },
      {
        key: 'repeatPass',
        label: I18n.t('userForm.form.repeatPass') + '*',
        type: 'password',
        placeholder: ''
      },
      {
        key: 'company',
        label: I18n.t('userForm.form.company') + '*',
        type: 'text',
        placeholder: ''
      }
    ];

    this.state = {
      message: '',
      login: '',
      name: '',
      email: '',
      phone: '',
      pass: '',
      repeatPass: '',
      company: '',
      role: ''
    }

    props.onSubmit(this.handleSaveClick.bind(this));
  }

  handleSaveClick() {
    this.props.request(
      (this.props.type === EDIT_TYPE) ? ['users', 'updateUser'] : ['users', 'createUser'],
      'post',
      (this.props.type === EDIT_TYPE) ? 'EDIT_USER' : 'CREATE_USER',
      new FormData(this.userForm)
    ).then(
      (response) => this.props.redirect('/users'),
      (response) => {
        if (response.forwardingDescription) {
          this.setState({ message: I18n.t('userForm.form.' + response.forwardingDescription) })
        } else {
          this.setState({ message: I18n.t('userForm.form.creationError') })
        }
      }
    );
  }

  componentDidMount() {
    if ((this.props.type === EDIT_TYPE) && (this.props.pending !== false)) {
      this.props.request(
        ['users', 'getUsers'],
        'get',
        'USERS'
      );
    }

    this.setValues(this.props);
  }

  componentWillReceiveProps(nextProps) {
    this.setValues(nextProps);
  }

  isEmptyObject(o) {
    return Object.keys(o).every(function(x) {
      return o[x]===''||o[x]===null;  // or just "return o[x];" for falsy values
    });
  }

  setValues(props) {
    if ((props.type !== EDIT_TYPE) || (props.pending !== false)) {
      return;
    }

    if (!this.isEmptyObject(this.state)) {
      return;
    }

    let index = props.users.findIndex((element) => {
      return element.id === props.userId;
    });

    if (index === -1) {
      return;
    }

    let user = props.users[index];
    user.pass = '';
    let newState = { ...this.state, ...user };
    if (!_isEqual(newState, this.state)) {
      this.setState(newState);
    }
  }

  componetnWillUnmount() {
    this.props.offSubmit();
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
      <form
        className='user-form-form__container form-horizontal'
        ref={ (form) => { this.userForm = form; }}
      >
        <div className='hidden'>
          <input name='id' type='text' defaultValue={ this.props.userId } />
        </div>
        <div className='row'>
          <div className='col-md-12 text-danger user-form-form__server-message'>
            { this.state.message }
          </div>
        </div>
        { this.buildRows() }
        <div className='row'>
          <div className='col-md-6'>
            <div className='form-group'>
              <label className='col-sm-2 control-label'>{ I18n.t('userForm.form.avaliableFdrs') + '*' }</label>
              <div className='col-sm-10'>
              <AvaliableFdrsSelector/>
              </div>
            </div>
          </div>
          <div className='col-md-6'>
            <div className='form-group'>
              <label className='col-sm-2 control-label'>{ I18n.t('userForm.form.role') + '*' }</label>
              <div className='col-sm-10'>
                <RoleRadio
                  role={ this.state.role }
                />
              </div>
            </div>
          </div>
        </div>
        <div className='row'>
          <div className='col-md-6'>
            <div className='form-group'>
              <label className='col-sm-2 control-label'><Translate value='userForm.form.logo'/></label>
              <div className='col-sm-10'>
                <FileInput
                 className="btn btn-default"
                 name="userLogo"
                 placeholder={ I18n.t('userForm.form.chooseFile') }
                 />
              </div>
            </div>
          </div>
        </div>
      </form>
    );
  }

  buildBody() {
    if ((this.props.type === EDIT_TYPE)
      && (this.props.pending !== false)
    ) {
      return <ContentLoader/>
    }

    return this.buildForm();
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
  return {
    pending: state.users.pending,
    users: state.users.items.slice()
  };
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    redirect: bindActionCreators(redirect, dispatch),
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(Form);
