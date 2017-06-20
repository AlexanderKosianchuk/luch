import './folder-controls.sass';

import React, { Component } from 'react';
import { I18n } from 'react-redux-i18n';

import deleteFlight from 'actions/deleteFlight';

export default function FolderControls(props) {
    function handleClickTrash() {
        if (confirm(I18n.t('flights.folderControls.confirm'))) {
            this.props.deleteFlight({ id: this.props.folderInfo.id });
        }
    }

    return (
        <div className='flights-folder-controls'>
            <span className='flights-folder-controls__glyphicon glyphicon glyphicon-pencil'></span>
            <span
                className={
                    'flights-folder-controls__glyphicon '
                    + 'flights-folder-controls__glyphicon-danger '
                    + 'glyphicon glyphicon-trash'
                }
                onClick={ handleClickTrash }
            >
            </span>
        </div>
    );
}
