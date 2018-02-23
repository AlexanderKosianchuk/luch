import './create-folder-button.sass';

import React, { Component } from 'react';
import PropTypes from 'prop-types';

export default function CreateFolderButton (props) {
  return (
    <ul className='flights-tree-create-folder-button nav navbar-nav navbar-right'>
      <li><a href='#' className='flights-tree-create-folder-button__a'
          onClick={ props.handleClick }>
        <span
          className='flights-tree-create-folder-button__folder glyphicon glyphicon-folder-close' aria-hidden='true'>
        </span>
        <span
          className='flights-tree-create-folder-button__plus glyphicon glyphicon-plus'
          aria-hidden='true'>
        </span>
      </a></li>
    </ul>
  );
}

CreateFolderButton.propTypes = {
  handleClick: PropTypes.func.isRequired
};
