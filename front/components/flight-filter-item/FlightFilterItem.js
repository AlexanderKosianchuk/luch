var React = require('react');

class FlightFilterItem extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        return <div className="form-group flight-filter__row">
                <label htmlFor={this.props.id}>{this.props.label}</label>
                <input type="text" className="form-control"
                    id={this.props.id} placeholder={this.props.placeholder} />
            </div>;
    }
}

module.exports = FlightFilterItem;
