import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { Translate, I18n } from 'react-redux-i18n';

import FlightFilterItem from 'components/flight-filter-item/FlightFilterItem';
import applyFlightFilter from 'actions/applyFlightFilter';
import changeFlightFilterItem from 'actions/changeFlightFilterItem';

class FlightFilter extends React.Component {
    constructor(props) {
        super(props);

        const fields = [
            ["fdr-type", "flight-filter-fdr-type", I18n.t('flightFilter.fdrType'), ''],
            ["bort", "flight-filter-bort",  I18n.t('flightFilter.bort'), ''],
            ["flight", "flight-filter-flight",  I18n.t('flightFilter.voyage'), ''],
            ["departure-airport", "flight-filter-departure-airport",  I18n.t('flightFilter.departureAirport'), 'DDDD'],
            ["arrival-airport", "flight-filter-arrival-airport",  I18n.t('flightFilter.arrivalAirport'), 'AAAA'],
            ["from-date", "flight-filter-from-date",  I18n.t('flightFilter.departureFromDate'), 'YYYY/mm/dd'],
            ["to-date", "flight-filter-to-date",  I18n.t('flightFilter.departureToDate'), 'YYYY/mm/dd']
        ];

        this.flightFilterItems = fields.map((field) =>
            <FlightFilterItem
                key={field[1]}
                propName={field[0]}
                id={field[1]}
                label={field[2]}
                placeholder={field[3]}
                changeFlightFilterItem={this.props.changeFlightFilterItem}
            />
        );
    }

    handleSubmit(event) {
        event.preventDefault();
        this.props.applyFlightFilter(this.props.flightFilter);
    }

    render() {
        return (
            <form onSubmit={this.handleSubmit.bind(this)}>
                <p><b><Translate value='flightFilter.flightInfoFilter'/></b></p>
                { this.flightFilterItems }
                <div className="form-group">
                    <input type="submit" className="btn btn-default" value={ I18n.t('flightFilter.apply') }/>
                </div>
            </form>
        );
    }
}

function mapStateToProps (state) {
    return {
        flightFilter: state.flightFilter
    }
}

function mapDispatchToProps(dispatch) {
    return {
        changeFlightFilterItem: bindActionCreators(changeFlightFilterItem, dispatch),
        applyFlightFilter: bindActionCreators(applyFlightFilter, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightFilter);
