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
            ["fdr-type", "flight-filter-fdr-type", "Label", "FDR type"],
            ["bort", "flight-filter-bort", "Label", "Bort number"],
            ["flight", "flight-filter-flight", "Label", "Flight number"],
            ["departure-airport", "flight-filter-departure-airport", "Label", "Departure airport"],
            ["arrival-airport", "flight-filter-arrival-airport", "Label", "Arrival airport"],
            ["from-date", "flight-filter-from-date", "Label", "From"],
            ["to-date", "flight-filter-to-date", "Label", "To"]
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
                <div className="form-group">
                    <label>Name</label>
                </div>
                {this.flightFilterItems}
                <div className="form-group">
                    <input type="submit" className="btn btn-default" value="Apply" />
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
