import 'react-table/react-table.css'

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import TableControl from 'controls/table/Table';
import ContentLoader from 'controls/content-loader/ContentLoader';

import get from 'actions/get';
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
            accessor: 'organization',
        }, {
            Header: I18n.t('users.table.lang'),
            accessor: 'lang',
        }, {
            Header: I18n.t('users.table.role'),
            accessor: 'role'
        }, {
            Header: I18n.t('users.table.logo'),
            accessor: 'logo'
        }];
    }

    componentDidMount() {
        this.resize();

        if (this.props.pending !== false) {
            this.props.getAll(
                'users/getList',
                'USERS_LIST'
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
                if (!rowInfo || !this.props.usersList.chosenItems) {
                    return '';
                }

                let flightId = rowInfo.original.id;

                let isChosen = this.props.usersList.chosenItems.some((element) => {
                    return element.id === flightId
                });

                return isChosen ? 'is-chosen' : '';
            })(),
            onClick: event => {
                let target = event.currentTarget;
                target.classList.toggle('is-chosen');

                let flightId = rowInfo.original.id;
                this.props.transmit(
                    'FLIGHT_LIST_CHOISE_TOGGLE',
                    {
                        id: flightId,
                        checkstate: target.classList.contains('is-chosen')
                    }
                );
            }
        }
    }

    buildTable() {
        //copying array
        var data = this.props.usersList.items.slice();

        return (<Table
            data={ data }
            columns={ this.columns }
            getTrProps={ this.handleGetTrProps.bind(this) }
        />);
    }

    buildBody() {
        if (this.props.usersList.pending !== false) {
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
        usersList: state.usersList,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        get: bindActionCreators(get, dispatch),
        transmit: bindActionCreators(transmit, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Table);
