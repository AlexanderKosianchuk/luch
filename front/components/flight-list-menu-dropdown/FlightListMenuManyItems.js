import React from 'react';

export default function FlightListMenuManyItems(props) {
    return(
        <ul className="dropdown-menu">
            <li><a href="#">{ props.i18n.deleteItem }</a></li>
            <li><a href="#">{ props.i18n.selectAll }</a></li>
            <li><a href="#">{ props.i18n.removeSelection }</a></li>
        </ul>
    );
}
