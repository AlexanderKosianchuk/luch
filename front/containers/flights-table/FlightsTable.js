import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'controls/main-page/MainPage';
import Toolbar from 'components/flights-table/toolbar/Toolbar';
import Table from 'components/flights-table/table/Table';

export default function FlightsTable (props) {
    return (
        <div>
            <MainPage/>
            <Toolbar viewType='table' />
            <Table/>
        </div>
    );
}
