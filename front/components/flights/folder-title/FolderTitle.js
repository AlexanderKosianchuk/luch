import './folder-title.sass';

import React, { Component } from 'react';
import { Translate } from 'react-redux-i18n';

export default function FolderTitle(props) {
    return (
        <div className='flights-folder-title'>
            { props.folderInfo.name }
        </div>
    );
}
