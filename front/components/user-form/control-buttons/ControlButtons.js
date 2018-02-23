import React, { Component } from 'react';
import PropTypes from 'prop-types';

export default function ControlButtons (props) {
  return (
    <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
      <ul className='nav navbar-nav navbar-right'>
        <li><a href='#' onClick={ props.handleListClick }>
          <span
            className='glyphicon glyphicon-list' aria-hidden='true'>
          </span>
        </a></li>
        <li><a href='#' onClick={ props.handleSaveClick }>
          <span
            className='glyphicon glyphicon-floppy-disk' aria-hidden='true'>
          </span>
        </a></li>
      </ul>
    </div>
  );
}

ControlButtons.propTypes = {
  handleListClick: PropTypes.func.isRequired,
  handleSaveClick: PropTypes.func.isRequired
};
