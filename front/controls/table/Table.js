import './table.sass'
import 'react-table/react-table.css'

import React, { Component } from 'react';
import { I18n } from 'react-redux-i18n';
import ReactTable from 'react-table';

export default function Table (props) {
    return (<ReactTable
        className='table-table'
        data={ props.data }
        columns={ props.columns }
        getTrProps={ props.getTrProps || function () { return { className: '' } } }
        previousText={ I18n.t('table.previous') }
        nextText={ I18n.t('table.next') }
        loadingText={ I18n.t('table.loading') }
        noDataText={ I18n.t('table.noRowsFound') }
        pageText={ I18n.t('table.page') }
        ofText={ I18n.t('table.of') }
        rowsText={ I18n.t('table.rows') }
    />);
}
