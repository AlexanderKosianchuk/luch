import './table.sass'
import 'react-table/react-table.css'

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import TableControl from 'controls/table/Table';
import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';
import transmit from 'actions/transmit';

const TOP_CONTROLS_HEIGHT = 105;

class Table extends Component {
    constructor(props) {
        super(props);

        this.columns = [{
            Header: I18n.t('users.table.login'),
            accessor: 'login'
        }, {
            Header: I18n.t('users.table.organization'),
            accessor: 'company',
        }, {
            Header: I18n.t('users.table.lang'),
            accessor: 'lang',
        }, {
            Header: I18n.t('users.table.role'),
            accessor: 'role'
        }, {
            Header: I18n.t('users.table.logo'),
            accessor: 'logo',
            Cell: props => {
                return(
                    <div className='users-table-table__logo'
                        style={{ content: 'url('+ENTRY_URL+'?'+props.value+')' }}
                    >
                    </div>
                );
            }
        }];
    }

    componentDidMount() {
        this.resize();

        if (this.props.pending !== false) {
            this.props.request(
                ['users', 'getUsers'],
                'USERS',
                'get'
            );
        }
    }

    componentDidUpdate() {
        this.resize();
    }

    resize() {
        this.container.style.height = window.innerHeight - TOP_CONTROLS_HEIGHT + 'px';
    }

    handleGetTrProps(state, rowInfo, column, instance) {
        return {
            className: (() => {
                if (!rowInfo || !this.props.users.chosenItems) {
                    return '';
                }

                let isChosen = this.props.users.chosenItems.some((element) => {
                    return element.id === rowInfo.original.id
                });

                return isChosen ? 'is-chosen' : '';
            })(),
            onClick: event => {
                let target = event.currentTarget;
                target.classList.toggle('is-chosen');

                this.props.transmit(
                    'USERS_CHOISE_TOGGLE',
                    {
                        id: rowInfo.original.id,
                        checkstate: target.classList.contains('is-chosen')
                    }
                );
            }
        }
    }

    buildTable() {
        //copying array
        var data = this.props.users.items.slice();

        return (<TableControl
            data={ data }
            columns={ this.columns }
            getTrProps={ this.handleGetTrProps.bind(this) }
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
        transmit: bindActionCreators(transmit, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Table);
