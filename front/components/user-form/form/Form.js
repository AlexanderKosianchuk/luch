import './form.sass'

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';
import FileInput from 'react-file-input';
import PropTypes from 'prop-types';

import Row from 'components/user-form/row/Row';
import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';
import redirect from 'actions/redirect';

const controls = [
    [
        {
            key: '11',
            label: '11',
            type: 'text',
            placeholder: 'text'
        },
        {
            key: '12',
            label: '12',
            type: 'text',
            placeholder: 'text'
        }
    ],
    [
        {
            key: '21',
            label: '21',
            type: 'text',
            placeholder: 'text'
        },
        {
            key: '22',
            label: '22',
            type: 'text',
            placeholder: 'text'
        }
    ],
    [
        {
            key: '31',
            label: '31',
            type: 'text',
            placeholder: 'text'
        }
    ]
];

class Form extends Component {
    constructor(props) {
        super(props);

        this.state = {
            pending: null
        }
    }

    componentDidMount() {
        if ((this.props.type === 'edit') && (this.state.pending !== false)) {
            this.props.request(
                ['users', 'getUser'],
                'USER',
                'get',
                { id: this.props.userId }
            );
        }
    }

    buildForm() {
        return (
            <form className='user-form-form__container form-horizontal'>
                {
                    controls.map((item, index) => {
                        return <Row key={ index } controls={ item }/>
                    })
                }
                <div className='row'>
                    <div className='checkbox'>
                      <label>
                          <input type='radio' name='role'/> Check me out
                      </label>
                      <label>
                          <input type='radio' name='role'/> Check me out
                      </label>
                      <label>
                          <input type='radio' name='role'/> Check me out
                      </label>
                    </div>
                </div>
                <div className='row'>
                    <div className='form-group'>
                      <label className='col-sm-2 control-label'>File input</label>
                      <div className='col-sm-10'>
                          <FileInput
                             className="btn btn-default"
                             name="flightFile"
                             placeholder={ I18n.t('topMenu.flightUploaderDropdown.chooseFile') }
                             value={ '' }
                           />
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
