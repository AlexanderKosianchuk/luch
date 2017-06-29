import './content.sass'

import React from 'react';
import { I18n } from 'react-redux-i18n';

export default function Content (props) {
    const columns = [
        { value: I18n.t('flightEvents.contentHeader.start'), style: 'col-xs-1' },
        { value: I18n.t('flightEvents.contentHeader.end'), style: 'col-xs-1' },
        { value: I18n.t('flightEvents.contentHeader.duration'), style: 'col-xs-1' },
        { value: I18n.t('flightEvents.contentHeader.code'), style: 'col-xs-1' },
        { value: I18n.t('flightEvents.contentHeader.eventName'), style: 'col-xs-2' },
        { value: I18n.t('flightEvents.contentHeader.algorithm'), style: 'col-xs-1' },
        { value: I18n.t('flightEvents.contentHeader.aditionalInfo'), style: 'col-xs-2' },
        { value: I18n.t('flightEvents.contentHeader.reliability'), style: 'col-xs-1' },
        { value: I18n.t('flightEvents.contentHeader.comment'), style: 'col-xs-2' },
    ];

    return <div className='flight-events-content row'>
        {
            props.rows.map((item, index) => {
                return columns.map((col, colIndex) => {
                    return <div key={ colIndex } className={ col.style }>
                        { col.value }
                    </div>
                })
            })
        }
    </div>;
}
