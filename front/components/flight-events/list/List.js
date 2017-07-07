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

import getFlightInfo from 'actions/getFlightInfo';
import getFlightEvents from 'actions/getFlightEvents';

class List extends React.Component {
    componentDidMount() {
        if (this.props.flightInfoPending !== false) {
            this.props.getFlightInfo({ flightId: this.props.flightId });
        }

        if (this.props.flightEventsPending !== false) {
            this.props.getFlightEvents({ flightId: this.props.flightId });
        }
    }

    buildList() {
        if (this.props.flightEventsPending !== false) {
            return <ContentLoader/>
        }

        if (this.props.isProcessed === false) {
            return <div className='flight-events-list__not-processed'>
                <Translate value='flightEvents.list.processingNotPerformed'/>
            </div>;
        }

        return <Accordion
            items={ this.props.flightEvents.items }
            flightId= { this.props.flightId }
        />;
    }

    buildBody() {
        if (this.props.flightInfoPending !== false) {
            return <ContentLoader/>
        }

        return this.buildList();
    }

    render() {
        return (
            <div className='flight-events-list'>
                <Title flightInfo={ this.props.flightInfo.data }/>
                { this.buildBody() }
            </div>
        );
    }
}

List.propTypes = {
    flightId: PropTypes.number.isRequired,
    flightInfo:  PropTypes.shape({
        fdrName: PropTypes.string,
        bort: PropTypes.string,
        voyage: PropTypes.string,
        departureAirport: PropTypes.string,
        arrivalAirport: PropTypes.string,
        startCopyTimeFormated: PropTypes.string,
        fdrName: PropTypes.string,
        aditionalInfo: PropTypes.object
    }).isRequired,
    flightInfoPending: PropTypes.oneOf([true, false, null]),
    isProcessed: PropTypes.oneOf([true, false, null])
};

function mapStateToProps(state) {
    return {
        flightInfoPending: state.flightInfo.pending,
        flightInfo: state.flightInfo,
        flightEventsPending: state.flightEvents.pending,
        isProcessed: state.flightEvents.isProcessed,
        flightEvents: state.flightEvents
    };
}

function mapDispatchToProps(dispatch) {
    return {
        getFlightInfo: bindActionCreators(getFlightInfo, dispatch),
        getFlightEvents: bindActionCreators(getFlightEvents, dispatch),
    };
}

export default connect(mapStateToProps, mapDispatchToProps)(List);
