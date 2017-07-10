import React from 'react';

import Row from 'components/flight-events/row/Row';

export default function Content(props) {
    return (
        <div className='flight-events-content'>{
            props.rows.map((item, index) => {
                return (<Row
                    key={ index }
                    item={ item }
                    flightId={ props.flightId }
                    isShort={ props.isShort || false }
                />);
            })
        }</div>
    );
}
