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
            isLoading: true
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
        this.setState({ loading: true })
        // fetch your data
        /*Axios.post('mysite.com/data', {
            page: state.page,
            pageSize: state.pageSize,
            sorted: state.sorted,
            filtered: state.filtered
        }).then((res) => {
            this.setState({
                data: res.data.rows,
                pages: res.data.pages,
                loading: false
            });
        });*/
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
