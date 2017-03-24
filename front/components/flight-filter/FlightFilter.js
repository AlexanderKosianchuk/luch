var React = require('react');
var FlightFilterItem = require(COMPONENTS_PATH + '/flight-filter-item/FlightFilterItem');

class FlightFilter extends React.Component {
    constructor(props) {
        super(props);

        const fields = [
            ["flight-filter-fdr-type", "Label", "FDR type"],
            ["flight-filter-bort", "Label", "Bort number"],
            ["flight-filter-flight", "Label", "Flight number"],
            ["flight-filter-departure-airport", "Label", "Departure airport"],
            ["flight-filter-arrival-airport", "Label", "Arrival airport"],
            ["flight-filter-from-date", "Label", "From"],
            ["flight-filter-to-date", "Label", "To"]
        ];

        this.flightFilterItems = fields.map((field) =>
            <FlightFilterItem
                key={field.toString()}
                id={field[0]}
                label={field[1]}
                placeholder={field[2]}/>
        );

        this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleSubmit(event) {
        event.preventDefault();
    }

    render() {
        return <form className="flight-filter" onSubmit={this.handleSubmit}>
                <div className="form-group flight-filter__header">
                    <label>Name</label>
                </div>
                {this.flightFilterItems}
                <div className="form-group flight-filter__row">
                    <input type="submit" className="btn btn-default"
                        id="flight-filter__apply" value="Apply" />
                </div>
            </form>;
    }
}

module.exports = FlightFilter;
