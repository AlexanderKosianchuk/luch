import './table.sass'
import 'react-table/react-table.css'

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

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
        }];

        this.isLoading = false;

        this.state = {
            data: [],
            pages: 0,
        };
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

        this.props.request(
            ['calibration', 'getCalibrationsPage'],
            'get',
            null,
            {
                fdrId: this.props.fdrId,
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
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch),
        redirect: bindActionCreators(redirect, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Table);
