import './table.sass';
import 'react-table/react-table.css'

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';
import ReactTable from 'react-table';
import ContentLoader from 'controls/content-loader/ContentLoader';

import getFlightsList from 'actions/getFlightsList';
import getFoldersList from 'actions/getFoldersList';
import flightListChoiceToggle from 'actions/flightListChoiceToggle';

const TOP_CONTROLS_HEIGHT = 105;

class Table extends Component {
    constructor(props) {
        super(props);

        this.columns = [{
            Header: I18n.t('flightsTable.table.bort'),
            accessor: 'bort'
        }, {
            Header: I18n.t('flightsTable.table.voyage'),
            accessor: 'voyage',
        }, {
            Header: I18n.t('flightsTable.table.performer'),
            accessor: 'performer',
        }, {
            Header: I18n.t('flightsTable.table.startCopyTime'),
            accessor: 'startCopyTimeFormated'
        }, {
            Header: I18n.t('flightsTable.table.departureAirport'),
            accessor: 'departureAirport'
        }, {
            Header: I18n.t('flightsTable.table.arrivalAirport'),
            accessor: 'arrivalAirport'
        }];
    }

    componentDidMount() {
        this.resize();

        if (this.props.pending !== false) {
            this.props.getFlightsList();
        }
    }

    componentDidUpdate() {
        this.resize();
    }

    resize() {
        this.container.style.height = window.innerHeight - TOP_CONTROLS_HEIGHT + 'px';
    }

    buildTable() {
        return (<ReactTable
            data={ this.props.flightsList.items }
            columns={ this.columns }
            className='flights-table-table__table'
        />);
    }

    buildBody() {
        if (this.props.flightsList.pending !== false) {
            return <ContentLoader/>
        } else {
            return this.buildTable();
        }
    }

    render() {
        return (
            <div className='flights-table-table'
                ref={(container) => { this.container = container; }}
            >
                { this.buildBody() }
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {
        flightsList: state.flightsList,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        getFlightsList: bindActionCreators(getFlightsList, dispatch),
        flightListChoiceToggle: bindActionCreators(flightListChoiceToggle, dispatch),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Table);
