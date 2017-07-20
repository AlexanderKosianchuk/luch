import './create-user-button.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';

export default function CreateUserButton (props) {
    return (
        <ul className='users-table-create-user-button nav navbar-nav navbar-right'>
            <li><a href='#' className='users-table-create-user-button__a'
                    onClick={ props.handleClick }>
                <span
                    className='users-table-create-user-button__folder glyphicon glyphicon-user' aria-hidden='true'>
                </span>
                <span
                    className='users-table-create-user-button__plus glyphicon glyphicon-plus'
                    aria-hidden='true'>
                </span>
            </a></li>
        </ul>
    );
}

CreateUserButton.propTypes = {
    handleClick: PropTypes.func.isRequired
};
