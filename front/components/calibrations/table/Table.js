import './table.sass'

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';
import moment from 'moment';

import TableControl from 'controls/table/Table';
import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';
import redirect from 'actions/redirect';

const TOP_CONTROLS_HEIGHT = 105;

class Table extends Component {
  constructor(props) {
    super(props);

    this.columns = [{
      Header: '#',
      accessor: 'number',
      width: 60,
    }, {
      Header: I18n.t('calibration.table.name'),
      accessor: 'name'
    }, {
      Header: I18n.t('calibration.table.fdr'),
      accessor: 'fdrName'
    }, {
      Header: I18n.t('calibration.table.dateCreation'),
      accessor: 'dtCreated'
    }, {
      Header: I18n.t('calibration.table.dateLastEdit'),
      accessor: 'dtUpdated'
    }, {
      Header: '',
      accessor: 'id',
      minWidth: 50,
      Cell: props => {
        return(
          <div className='calibrations-table__actions'>
            <span className='calibrations-table__glyph-edit glyphicon glyphicon-edit'
              onClick={ this.handleEditClick.bind(this, props.value) }
            ></span>
            <span className='calibrations-table__glyph-trash glyphicon glyphicon-trash'
              onClick={ this.handleDeleteClick.bind(this, props.value) }
            ></span>
          </div>
        );
      }
    }];

    this.isLoading = false;

    this.state = {
      data: [],
      pages: 0,
    };
  }

  handleEditClick(id) {
    this.props.redirect('/calibration/update/' + id);
  }

  handleDeleteClick(id) {
    if (confirm(I18n.t('calibration.table.confimDeleting'))) {
      this.props.request(
        ['calibration', 'deleteCalibration'],
        'post',
        'DELETE_CALIBRATION',
        { calibrationId: id }
      ).then(() => this.props.redirect('/calibrations'));
    }
  }

  componentDidMount() {
    this.resize();

    this.fetchData();
  }

  componentDidUpdate(prevProps) {
    this.resize();

    if ((prevProps.fdrId !== this.props.fdrId)
       || (prevProps.page !== this.props.page)
       || (prevProps.pageSize !== this.props.pageSize)
       || (prevProps.calibrations.length !== this.props.calibrations.length)
     ) {
       this.fetchData();
     }
  }

  resize() {
    this.container.style.height = window.innerHeight - TOP_CONTROLS_HEIGHT + 'px';
  }

  fetchData() {
    if (this.isLoading === true) {
      return;
    }

    this.isLoading = true;

    let obj = {};
    if (this.props.fdrId !== null) {
      obj.fdrId = this.props.fdrId;
    }

    this.props.request(
      ['calibration', 'getCalibrationsPage'],
      'get',
      null, {
        ...obj,
        page: this.props.page,
        pageSize: this.props.pageSize
      }
    ).then((res) => {
      let beforeItemsCount = (this.props.page - 1) * this.props.pageSize;
      let counter = 0;
      let arr = new Array(beforeItemsCount);

      let rows = res.rows.map((row) => {
        counter++;
        return { ...row, ... {
          number: beforeItemsCount + counter
        }}
      })

      let data = arr.concat(rows);

      this.setState({
        data: data,
        pages: res.pages
      });

      this.isLoading = false;
    });
  }

  navigate (fdrId, pageIndex, pageSize) {
    if ((fdrId !== null)
      && (pageIndex !== null)
      && (pageSize !== null)
    ) {
      this.props.redirect('/calibrations/'
        + 'fdr-id/' + fdrId + '/'
        + 'page/'+ pageIndex + '/'
        + 'page-size/'+ pageSize
      );
    }

    if ((fdrId === null)
      && (pageIndex !== null)
      && (pageSize !== null)
    ) {
      this.props.redirect('/calibrations/'
        + 'page/' + pageIndex + '/'
        + 'page-size/'+ pageSize
      );
    }

    if ((fdrId !== null)
      && (pageIndex === null)
      && (pageSize == null)
    ) {
      this.props.redirect('/calibrations/' + fdrId);
    }
  }

  onPageChange(pageIndex) {
    this.navigate (this.props.fdrId, (pageIndex + 1), this.props.pageSize);
  }

  onPageSizeChange(pageSize, pageIndex) {
    this.navigate (this.props.fdrId, 1, pageSize);
  }

  handleGetTrProps(state, rowInfo, column, instance) {
    if (!rowInfo) {
      return {};
    }

    let diff = moment().diff(moment(rowInfo.row.dtUpdated, 'YY-MM-DD hh:ii:ss'), 'months');

    if (diff > 3) {
      return {
        style: {
          background: '#ffefaf'
        }
      }
    } else {
      return {};
    }
  }

  render() {
    return (
      <div className='calibrations-table'
        ref={(container) => { this.container = container; }}
      >
        <TableControl
          className={ this.state.isLoading ? 'calibrations-table__hidden' : '' }
          columns={ this.columns }
          onPageChange={ this.onPageChange.bind(this) }
          onPageSizeChange={ this.onPageSizeChange.bind(this) }
          getTrProps={ this.handleGetTrProps.bind(this) }
          data={ this.state.data }
          page={ this.props.page - 1 }
          pages={ this.state.pages }
          pageSize={ this.props.pageSize }
          loading={ this.state.isLoading }
        />
        <ContentLoader
          className={ this.state.isLoading ? '' : 'calibrations-table__hidden' }
        />
      </div>
    );
  }
}

Table.propTypes = {
  fdrId: PropTypes.number,
  page:  PropTypes.number,
  pageSize: PropTypes.number,

  request: PropTypes.func,
  transmit: PropTypes.func
};

function mapStateToProps(state) {
  return {
    calibrations: state.calibrations.items
  };
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch),
    redirect: bindActionCreators(redirect, dispatch),
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(Table);
