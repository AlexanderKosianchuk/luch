import './flight-templates-options.sass'

import React from 'react';
import { Translate, I18n } from 'react-redux-i18n';

import FlightViewOptionsSwitch from 'components/flight-view-options-switch/FlightViewOptionsSwitch';
import FlightViewOptionsSlider from 'components/flight-view-options-slider/FlightViewOptionsSlider';

export default class FlightTemplatesOptions extends React.Component {
    render() {
        return (
            <nav className="flight-templates-options navbar navbar-default">
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
                            view={ 'templates' }
                            flightId={ this.props.flightId }
                        />
                        <FlightViewOptionsSlider
                            flightId={ this.props.flightId }
                        />
                    </div>
                </div>
            </nav>
        );
    }
}
