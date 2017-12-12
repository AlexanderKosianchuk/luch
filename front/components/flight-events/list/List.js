import './list.sass'
import 'rc-collapse/assets/index.css';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import { Translate } from 'react-redux-i18n';

import Title from 'components/flight-events/title/Title';
import Accordion from 'components/flight-events/accordion/Accordion';
import ContentLoader from 'controls/content-loader/ContentLoader';
import FlightComment from 'components/flight-events/flight-comment/FlightComment';

import request from 'actions/request';

class List extends React.Component {
    componentDidMount() {
        if ((this.props.pending === null)
            || ((this.props.pending === false)
                && (this.props.flight.id !== this.props.flightId)
            )
        ) {
            this.props.request(
                ['flights', 'getFlightInfo'],
                'get',
                'FLIGHT',
                { flightId: this.props.flightId }
            );
        }

        if ((this.props.flightEventsPending === null)
            || ((this.props.flightEventsPending === false)
                && (this.props.flightEvents.id !== this.props.flightId)
            )
        ) {
            this.props.request(
                ['flightEvents', 'getFlightEvents'],
                'get',
                'FLIGHT_EVENTS',
                { flightId: this.props.flightId }
            );
        }
    }

    buildList() {
        if ((this.props.flightEventsPending !== false)
            || (this.props.flightEvents.id === this.props.flightId)
        ) {
            return <ContentLoader/>
        }

        if (this.props.isProcessed === false) {
            return <div className='flight-events-list__not-processed'>
                <Translate value='flightEvents.list.processingNotPerformed'/>
            </div>;
        }

        if (Object.keys(this.props.flightEvents.items).length === 0) {
            return <div className='flight-events-list__not-processed'>
                <Translate value='flightEvents.list.noEvents'/>
            </div>;
        }

        return <Accordion
            items={ this.props.flightEvents.items }
            flightId= { this.props.flightId }
        />;
    }

    buildBody() {
        if (this.props.pending !== false) {
            return <ContentLoader/>
        }

        return this.buildList();
    }

    bulidTitle() {
        if (this.props.flight.id === this.props.flightId) {
            return <Title flight={ this.props.flight.data }/>;
        }

        return '';
    }

    render() {
        return (
            <div className='flight-events-list'>
                { this.bulidTitle() }
                <FlightComment flightId={ this.props.flightId }/>
                { this.buildBody() }
            </div>
        );
    }
}

List.propTypes = {
    flightId: PropTypes.number.isRequired,
    flight:  PropTypes.shape({
        fdrName: PropTypes.string,
        bort: PropTypes.string,
        voyage: PropTypes.string,
        departureAirport: PropTypes.string,
        arrivalAirport: PropTypes.string,
        startCopyTimeFormated: PropTypes.string,
        fdrName: PropTypes.string,
        aditionalInfo: PropTypes.object
    }).isRequired,
    pending: PropTypes.oneOf([true, false, null]),
    flightEventsPending: PropTypes.oneOf([true, false, null]),
    isProcessed: PropTypes.oneOf([true, false, null])
};

function mapStateToProps(state) {
    return {
        pending: state.flight.pending,
        flight: state.flight,
        flightEventsPending: state.flightEvents.pending,
        isProcessed: state.flightEvents.isProcessed,
        flightEvents: state.flightEvents
    };
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch)
    };
}

export default connect(mapStateToProps, mapDispatchToProps)(List);
