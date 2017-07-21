import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';
import PropTypes from 'prop-types';

const TOP_CONTROLS_HEIGHT = 105;

export default function Row (props) {
    function buildInput() {
        let controls = props.controls;

        return controls.map((item, index) => {
            return (
                <div className='col-md-6' key={ index }>
                    <div className='form-group'>
                        <label htmlFor={ ('user-form-form__' + item.key) } className='col-sm-2 control-label'>{ item.label }</label>
                        <div className='col-sm-10'>
                            <input type={ item.type } className='form-control'
                                id={ ('user-form-form__' + item.key) }
                                placeholder={ item.placeholder }
                                value={ item.value || '' }
                                data-key={ item.key }
                                onChange={ props.handler }
                            />
                        </div>
                    </div>
                </div>
            );
        });
    }

    return (
        <div className='row'>
            { buildInput() }
        </div>
    );
}

Row.propTypes = {
    controls: PropTypes.array.isRequired,
    handler: PropTypes.func.isRequired,
};
