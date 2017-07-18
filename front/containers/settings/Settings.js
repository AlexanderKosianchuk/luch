import './settings.sass';

import React from 'react';

import MainPage from 'controls/main-page/MainPage';
import List from 'components/settings/list/List';

export default function Settings (props) {
    return (
        <div>
            <MainPage/>
            <List/>
        </div>
    );
}
