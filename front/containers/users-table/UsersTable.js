import React from 'react';

import MainPage from 'controls/main-page/MainPage';

import Toolbar from 'components/users-table/toolbar/Toolbar';
import Table from 'components/users-table/table/Table';

export default function UsersTable(props) {
    return (
        <div>
            <MainPage/>
            <Toolbar/>
            <Table/>
        </div>
    );
}
