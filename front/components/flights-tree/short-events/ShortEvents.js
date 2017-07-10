import './short-events.sass'
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

const TOP_CONTROLS_HEIGHT = 105;

class ShortEvents extends React.Component {
    componentDidMount() {
        this.checkFlightEvents();
    }

    componentDidUpdate() {
        this.checkFlightEvents();
    }

    checkFlightEvents() {
        if (this.props.flightsList.chosenItems.length !== 1) {
            return;
        }

        let chosenFlight = this.props.flightsList.chosenItems[0];
        if ((this.props.flightEventsPending === null)
            || ((this.props.flightEventsPending === false)
                && (this.props.flightEvents.flightId !== chosenFlight.id)
            )
        ) {
            this.props.getFlightEvents({ flightId: chosenFlight.id });
        }

        this.resize();
    }

    resize(event) {
        this.container.style.height = window.innerHeight - TOP_CONTROLS_HEIGHT + 'px';
    }

    eventsAvaliable() {
        if (this.props.flightEvents.items
            && (Object.keys(this.props.flightEvents.items).length > 0)
            && (this.props.flightsList.chosenItems.length === 1)
            && (this.props.flightsList.chosenItems[0].id === this.props.flightEvents.flightId)
        ) {
            return true;
        }

        return false;
    }

    buildBody() {
        if (!this.eventsAvaliable()) {
            return '';
        }

        let chosenFlight = this.props.flightsList.chosenItems[0];
        return <Accordion
            items={ this.props.flightEvents.items }
            flightId={ chosenFlight.id }
            isShort={ true }
        />;
    }

    render() {
        return (
            <div className={ 'flights-tree-short-events ' + (this.eventsAvaliable() ? '' : 'is-hidden') }
                ref={(container) => { this.container = container; }}
            >
                { this.buildBody() }
            </div>
        );
    }
}

ShortEvents.propTypes = {
    flightsList: PropTypes.shape({
        chosenItems: PropTypes.array
    }).isRequired,
    flightEvents: PropTypes.shape({
        items: PropTypes.object
    }).isRequired,
    flightInfoPending: PropTypes.oneOf([true, false, null])
};

function mapStateToProps(state) {
    return {
        flightsList: state.flightsList,
        flightEventsPending: state.flightEvents.pending,
        flightEvents: state.flightEvents
    };
}

function mapDispatchToProps(dispatch) {
    return {
        getFlightEvents: bindActionCreators(getFlightEvents, dispatch),
    };
}

export default connect(mapStateToProps, mapDispatchToProps)(ShortEvents);
