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
            Header: I18n.t('usersTable.table.login'),
            accessor: 'login'
        }, {
            Header: I18n.t('usersTable.table.name'),
            accessor: 'name'
        }, {
            Header: I18n.t('usersTable.table.email'),
            accessor: 'email'
        }, {
            Header: I18n.t('usersTable.table.phone'),
            accessor: 'phone'
        }, {
            Header: I18n.t('usersTable.table.company'),
            accessor: 'company',
        }, {
            Header: I18n.t('usersTable.table.role'),
            accessor: 'role'
        }, {
            Header: I18n.t('usersTable.table.logo'),
            accessor: 'logo',
            Cell: props => {
                return(
                    <div className='users-table-table__logo'
                        style={{ content: 'url('+ENTRY_URL+props.value+')' }}
                    >
                    </div>
                );
            }
        }, {
            Header: '',
            accessor: 'id',
            minWidth: 90,
            Cell: props => {
                return(
                    <div className='users-table-table__actions'>
                        <span className='users-table-table__glyph-activity glyphicon glyphicon-info-sign'
                            onClick={ this.handleActivityClick.bind(this, props.value) }
                        ></span>
                        <span className='users-table-table__glyph-edit glyphicon glyphicon-edit'
                            onClick={ this.handleEditClick.bind(this, props.value) }
                        ></span>
                        <span className='users-table-table__glyph-trash glyphicon glyphicon-trash'
                            onClick={ this.handleDeleteClick.bind(this, props.value) }
                        ></span>
                    </div>
                );
            }
        }];
    }

    handleActivityClick(id) {
        this.props.redirect('/user-activity/' + id);
    }

    handleEditClick(id) {
        this.props.redirect('/user/edit/' + id);
    }

    handleDeleteClick(id) {
        if (confirm(I18n.t('usersTable.table.confimUserDeleting'))) {
            this.props.request(
                ['users', 'delete'],
                'post',
                'DELETE_USER',
                { userId: id }
            );
        }
    }

    componentDidMount() {
        this.resize();

        if (this.props.pending !== false) {
            this.props.request(
                ['users', 'getUsers'],
                'get',
                'USERS'
            );
        }
    }

    componentDidUpdate() {
        this.resize();
    }

    resize() {
        this.container.style.height = window.innerHeight - TOP_CONTROLS_HEIGHT + 'px';
    }

    buildTable() {
        //copying array
        var data = this.props.users.items.slice();

        return (<TableControl
            data={ data }
            columns={ this.columns }
        />);
    }

    buildBody() {
        if (this.props.pending !== false) {
            return <ContentLoader/>
        } else {
            return this.buildTable();
        }
    }

    render() {
        return (
            <div className='users-table-table'
                ref={(container) => { this.container = container; }}
            >
                { this.buildBody() }
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {
        pending: state.users.pending,
        users: state.users
    };
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch),
        redirect: bindActionCreators(redirect, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Table);
