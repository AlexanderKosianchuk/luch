require('./results.sass');

const React = require('react');

let ResultsToolbar = require(COMPONENTS_PATH + '/results-toolbar/ResultsToolbar');
let FlightFilter = require(COMPONENTS_PATH + '/flight-filter/FlightFilter');
let ResultSettlementsFilter = require(COMPONENTS_PATH + '/result-settlements-filter/ResultSettlementsFilter');

class Results extends React.Component {
    render() {
        return <div className="results fluid-grid">
                <div className="row">
                    <div className="col-sm-12">
                        <ResultsToolbar i18n={this.props.i18n} />
                    </div>
                </div>
                <div className="row">
                    <div className="col-sm-3">
                        <FlightFilter i18n={this.props.i18n} />
                    </div>
                    <div className="col-sm-3">
                        <ResultSettlementsFilter i18n={this.props.i18n} />
                    </div>
                    <div className="col-sm-6">
                        &nbsp;
                    </div>
                </div>
            </div>;
    }
}

module.exports = Results;
