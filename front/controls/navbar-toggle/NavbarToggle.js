import './navbar-toggle.sass';

import React from 'react';

export default function NavbarToggle(props) {
  return (
    <button type='button'
      className={ (props.styleClass || '') + ' navbar-toggle collapsed' }
      data-toggle='collapse'
      data-target={ props.target || '#bs-navbar-collapse' }
      aria-expanded='false'
    >
      <span className='sr-only'>Toggle navigation</span>
      <span className='icon-bar'></span>
      <span className='icon-bar'></span>
      <span className='icon-bar'></span>
    </button>
  );
}
