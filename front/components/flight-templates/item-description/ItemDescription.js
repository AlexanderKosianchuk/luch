import './item-description.sass'

import React from 'react';

export default function ItemDescription(props) {
  function buildParamDesctiption(params) {
    let description = [];
    params.forEach((item, index) => {
      description.push(
        <div key={ index }>{
          item.code
          + ': ' + item.name
          + (item.dim ? (' (' + item.dim  + ')') : '')
          + '. ' + item.channel
        }</div>
      );
    });

    return description;
  }

  return (
    <div className='flight-templates-item-description'>
      { buildParamDesctiption(props.params) }
    </div>
  );
}
