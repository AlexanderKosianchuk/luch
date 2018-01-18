import React from 'react';

import Menu from 'controls/menu/Menu';
import Toolbar from 'components/flights-tree/toolbar/Toolbar';
import View from 'components/flights-tree/view/View';

export default function FlightsTree () {
  return (
    <div>
      <Menu/>
      <Toolbar viewType='tree'/>
      <View/>
    </div>
  );
}
