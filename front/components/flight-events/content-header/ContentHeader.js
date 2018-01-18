import './content-header.sass'

import React from 'react';
import { I18n } from 'react-redux-i18n';

export default function ContentHeader (props) {
  const columns = [
    { value: I18n.t('flightEvents.contentHeader.start'), style: 'col-sm-1' },
    { value: I18n.t('flightEvents.contentHeader.end'), style: 'col-sm-1' },
    { value: I18n.t('flightEvents.contentHeader.duration'), style: 'col-sm-1' },
    { value: I18n.t('flightEvents.contentHeader.code'), style: 'col-sm-1' },
    { value: I18n.t('flightEvents.contentHeader.eventName'), style: 'col-sm-2' },
    { value: I18n.t('flightEvents.contentHeader.algorithm'), style: 'col-sm-1' },
    { value: I18n.t('flightEvents.contentHeader.aditionalInfo'), style: 'col-sm-2' },
    { value: I18n.t('flightEvents.contentHeader.reliability'), style: 'col-sm-1' },
    { value: I18n.t('flightEvents.contentHeader.comment'), style: 'col-sm-2' },
  ];

  const columnsShort = [
    { value: I18n.t('flightEvents.contentHeader.start')
      + ' / ' + I18n.t('flightEvents.contentHeader.end') , style: 'col-sm-2'
    },
    { value: I18n.t('flightEvents.contentHeader.duration'), style: 'col-sm-2' },
    { value: I18n.t('flightEvents.contentHeader.code'), style: 'col-sm-2' },
    { value: I18n.t('flightEvents.contentHeader.eventName'), style: 'col-sm-3' },
    { value: I18n.t('flightEvents.contentHeader.aditionalInfo'), style: 'col-sm-3' },
  ];

  function buildBody() {
    let columnsToBuild = props.isShort ? columnsShort : columns;
    return columnsToBuild.map((item, index) => {
      return <div key={ index } className={ 'flight-events-content-header__cell ' + item.style }>
        { item.value }
      </div>
    })
  }

  return <div className='flight-events-content-header row'>
    { buildBody() }
  </div>;
}
