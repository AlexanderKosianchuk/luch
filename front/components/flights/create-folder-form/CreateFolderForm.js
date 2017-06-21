import './create-folder-form.sass';

import React, { Component } from 'react';

export default function CreateFolderForm (props) {
    let textInput = null;

    if (typeof props.handleSaveFolderClick !== 'function') {
        throw new Error('Component should receive handleSaveFolderClick handling function');
    }

    function handleClick(event) {
        let textInputValue = textInput.value;
        if ((textInputValue !== null)
            && (typeof textInputValue === 'string')
            && (textInputValue.length > 3)
        ) {
            props.handleSaveFolderClick(event, textInputValue);
        }
    }

    return (
        <ul className='flights-create-folder-form nav navbar-nav navbar-right'>
            <li><a href='#' className='flights-create-folder-form__name'>
                <input
                    className='form-control'
                    type='text'
                    ref={(input) => { textInput = input; }}
                />
            </a></li>
            <li><a href='#' onClick={ handleClick }>
                <span
                    className='glyphicon glyphicon-floppy-disk'
                    aria-hidden='true'>
                </span>
            </a></li>
        </ul>
    );
}
