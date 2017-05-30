import './flight-params-options.sass'

import React from 'react';

import FlightViewOptionsSwitch from 'components/flight-view-options-switch/FlightViewOptionsSwitch';
import FlightViewOptionsSlider from 'components/flight-view-options-slider/FlightViewOptionsSlider';
import ShowChartByParams from 'components/show-chart-by-params/ShowChartByParams';

export default class FlightParamsOptions extends React.Component {
    render() {
        return (
            <nav className="flight-params-options navbar navbar-default">
                <div className="container-fluid">
                    <div className="navbar-header">
                      <button type="button" className="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse" aria-expanded="false">
                        <span className="sr-only">Toggle navigation</span>
                        <span className="icon-bar"></span>
                        <span className="icon-bar"></span>
                        <span className="icon-bar"></span>
                      </button>
                    </div>

                    <div className="collapse navbar-collapse" id="bs-navbar-collapse">
                        <FlightViewOptionsSwitch
                            view={ 'params' }
                            flightId={ this.props.flightId }
                        />
                        <FlightViewOptionsSlider
                            flightId={ this.props.flightId }
                        />
                        <ShowChartByParams
                            flightId={ this.props.flightId }
                        />
                    </div>
                </div>
            </nav>
        );
    }
}
