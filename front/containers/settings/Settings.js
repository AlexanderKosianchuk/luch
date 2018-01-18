import './settings.sass';

import React from 'react';

import Menu from 'controls/menu/Menu';
import List from 'components/settings/list/List';

export default function Settings (props) {
  return (
    <div>
      <Menu/>
      <List/>
    </div>
  );
}
