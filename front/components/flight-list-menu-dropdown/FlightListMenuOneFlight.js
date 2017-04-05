import React from 'react';

export default function FlightListMenuOneFlight(props) {
    return(
        <ul className="dropdown-menu">
            <li><a href="#">{ props.i18n.deleteItem }</a></li>
            <li><a href="#">{ props.i18n.selectAll }</a></li>
            <li><a href="#">{ props.i18n.exportItem }</a></li>
            <li><a href="#">{ props.i18n.processItem }</a></li>
            <li><a href="#">{ props.i18n.exportCoordinates }</a></li>
            <li><a href="#">{ props.i18n.removeSelection }</a></li>
        </ul>
    );
}
