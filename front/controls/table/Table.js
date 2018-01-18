import './table.sass'
import 'react-table/react-table.css'

import React, { Component } from 'react';
import { I18n } from 'react-redux-i18n';
import ReactTable from 'react-table';

let DEFAULT_PAGE_SIZE = 20;

export { DEFAULT_PAGE_SIZE };

export default function Table (props) {
  return (<ReactTable
    className={ 'table-table ' + props.className || '' }
    data={ props.data }
    pages={ props.pages || null } // null - means take data.length
    columns={ props.columns }
    page={ props.page || null }
    pageSizeOptions={ [ 5, 10, 20, 25, 50, 100 ] }
    defaultPageSize={ props.pageSize || DEFAULT_PAGE_SIZE }
    getTrProps={ props.getTrProps || function () { return { className: '' } } }
    onPageChange={ props.onPageChange || null }
    onPageSizeChange={ props.onPageSizeChange || null }
    onFetchData={ props.onFetchData || function () {} }
    previousText={ I18n.t('table.previous') }
    nextText={ I18n.t('table.next') }
    loadingText={ I18n.t('table.loading') }
    noDataText={ I18n.t('table.noRowsFound') }
    pageText={ I18n.t('table.page') }
    ofText={ I18n.t('table.of') }
    rowsText={ I18n.t('table.rows') }
  />);
}
