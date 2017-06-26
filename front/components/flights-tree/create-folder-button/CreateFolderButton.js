import './create-folder-button.sass';

import React, { Component } from 'react';

export default function CreateFolderButton (props) {
    if (typeof props.handleCreateFolderClick !== "function") {
        throw new Error('Component should receive handleCreateFolderClick handling function');
    }

    return (
        <ul className='flights-tree-create-folder-button nav navbar-nav navbar-right'>
            <li><a href='#' className='flights-tree-create-folder-button__a'
                    onClick={ props.handleCreateFolderClick }>
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
