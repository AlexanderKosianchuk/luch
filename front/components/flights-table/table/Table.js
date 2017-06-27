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

    handleGetTrProps(state, rowInfo, column, instance) {
        return {
            className: (() => {
                if (!rowInfo || !this.props.flightsList.chosenItems) {
                    return '';
                }

                let flightId = rowInfo.original.id;

                let isChosen = this.props.flightsList.chosenItems.some((element) => {
                    return element.id === flightId
                });

                return isChosen ? 'is-chosen' : '';
            })(),
            onClick: event => {
                let target = event.currentTarget;
                target.classList.toggle('is-chosen');

                let flightId = rowInfo.original.id;
                this.props.flightListChoiceToggle({
                    id: flightId,
                    checkstate: target.classList.contains('is-chosen')
                });
            }
        }
    }

    buildTable() {
        //copying array 
        var data = this.props.flightsList.items.slice();

        return (<ReactTable
            data={ data }
            columns={ this.columns }
            className='flights-table-table__table'
            getTrProps={ this.handleGetTrProps.bind(this) }
            previousText={ I18n.t('flightsTable.table.previous') }
            nextText={ I18n.t('flightsTable.table.next') }
            loadingText={ I18n.t('flightsTable.table.loading') }
            noDataText={ I18n.t('flightsTable.table.noRowsFound') }
            pageText={ I18n.t('flightsTable.table.page') }
            ofText={ I18n.t('flightsTable.table.of') }
            rowsText={ I18n.t('flightsTable.table.rows') }
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
