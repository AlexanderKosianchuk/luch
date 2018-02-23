import React from 'react';

import Menu from 'controls/menu/Menu';

import Toolbar from 'components/users-table/toolbar/Toolbar';
import Table from 'components/users-table/table/Table';

export default function UsersTable(props) {
  return (
    <div>
      <Menu/>
      <Toolbar/>
      <Table/>
    </div>
  );
}
