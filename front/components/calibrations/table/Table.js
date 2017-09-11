import './table.sass'
import 'react-table/react-table.css'

import React, { Component } from 'react';
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
            Header: I18n.t('userActivity.table.action'),
            accessor: 'action'
        }, {
            Header: I18n.t('userActivity.table.status'),
            accessor: 'status'
        }, {
            Header: I18n.t('userActivity.table.message'),
            accessor: 'message'
        }, {
            Header: I18n.t('userActivity.table.date'),
            accessor: 'date'
        }];

        this.state = {
            data: [],
            pages: 0,
            isLoading: true,
        }
    }

    componentDidMount() {
        this.resize();
    }

    componentDidUpdate() {
        this.resize();
    }

    resize() {
        this.container.style.height = window.innerHeight - TOP_CONTROLS_HEIGHT + 'px';
    }

    onFetchData(state, instance) {
        this.setState({ loading: true });

        this.props.request(
            ['users', 'getUserActivity'],
            'USER_ACTIVITY',
            'get', {
                userId: this.props.userId,
                page: this.props.page,
                pageSize: this.props.pageSize
            }
        ).then((res) => {
            let rows = res.rows;
            let arr = new Array((this.props.page - 1) * this.props.pageSize);
            let data = arr.concat(rows);

            this.setState({
                data: data,
                pages: res.pages,
                isLoading: false
            });
        });
    }

    onPageChange(pageIndex) {
        this.props.redirect('/user-activity/'
            + this.props.userId + '/'
            + 'page/'+ (pageIndex + 1) + '/'
            + 'page-size/'+ this.props.pageSize
        );
    }

    onPageSizeChange(pageSize, pageIndex) {
        this.props.redirect('/user-activity/'
            + this.props.userId + '/'
            + 'page/'+ (pageIndex + 1) + '/'
            + 'page-size/'+ pageSize
        );
    }

    render() {
        return (
            <div className='user-activity-table'
                ref={(container) => { this.container = container; }}
            >
                <TableControl
                    className={ this.state.isLoading ? 'user-activity-table__hidden' : '' }
                    columns={ this.columns }
                    onFetchData={ this.onFetchData.bind(this) }
                    onPageChange={ this.onPageChange.bind(this) }
                    onPageSizeChange={ this.onPageSizeChange.bind(this) }
                    data={ this.state.data }
                    page={ this.props.page - 1 }
                    pages={ this.state.pages }
                    pageSize={ this.props.pageSize }
                    loading={ this.state.isLoading }
                />
                <ContentLoader
                    className={ this.state.isLoading ? '' : 'user-activity-table__hidden' }
                />
            </div>
        );
    }
}

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
