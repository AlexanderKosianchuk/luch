import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import Menu from 'controls/menu/Menu';
import Toolbar from 'components/flights-table/toolbar/Toolbar';
import Table from 'components/flights-table/table/Table';

export default function FlightsTable (props) {
  return (
    <div>
      <Menu/>
      <Toolbar viewType='table' />
      <Table/>
    </div>
  );
}
