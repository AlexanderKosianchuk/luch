import React from 'react';

export default function FlightListMenuNoSelection(props) {
    return(
        <ul className="dropdown-menu">
            <li><a data="tree" href="#">{ props.i18n.selectAll }</a></li>
        </ul>
    );
}
