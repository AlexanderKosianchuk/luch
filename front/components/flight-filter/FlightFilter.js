import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import FlightFilterItem from 'components/flight-filter-item/FlightFilterItem';
import applyFlightFilterAction from 'actions/applyFlightFilter';
import changeFlightFilterItemAction from 'actions/changeFlightFilterItem';

class FlightFilter extends React.Component {
    constructor(props) {
        super(props);

        const fields = [
            ["fdr-type", "flight-filter-fdr-type", props.i18n.fdrType, ''],
            ["bort", "flight-filter-bort",  props.i18n.bort, ''],
            ["flight", "flight-filter-flight",  props.i18n.voyage, ''],
            ["departure-airport", "flight-filter-departure-airport",  props.i18n.departureAirport, 'DDDD'],
            ["arrival-airport", "flight-filter-arrival-airport",  props.i18n.arrivalAirport, 'AAAA'],
            ["from-date", "flight-filter-from-date",  props.i18n.departureFromDate, 'YYYY/mm/dd'],
            ["to-date", "flight-filter-to-date",  props.i18n.departureToDate, 'YYYY/mm/dd']
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
                <p><b>{ this.props.i18n.flightInfoFilter }</b></p>
                { this.flightFilterItems }
                <div className="form-group">
                    <input type="submit" className="btn btn-default" value={ this.props.i18n.apply }/>
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
        changeFlightFilterItem: bindActionCreators(changeFlightFilterItemAction, dispatch),
        applyFlightFilter: bindActionCreators(applyFlightFilterAction, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightFilter);
