import './toolbar.sass'

import React from 'react';

import FlightViewOptionsSwitch from 'controls/flight-view-options-switch/FlightViewOptionsSwitch';
import Print from 'components/chart/print/Print';
import FullSize from 'components/chart/full-size/FullSize';
import ParamsToggle from 'components/chart/params-toggle/ParamsToggle';

export default class Toolbar extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <nav className='flight-events-toolbar navbar navbar-default'>
                <div className='container-fluid'>
                    <div className='navbar-header'>
                      <button type='button' className='navbar-toggle collapsed' data-toggle='collapse' data-target='#bs-navbar-collapse' aria-expanded='false'>
                        <span className='sr-only'>Toggle navigation</span>
                        <span className='icon-bar'></span>
                        <span className='icon-bar'></span>
                        <span className='icon-bar'></span>
                      </button>
                    </div>

                    <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
                        <FlightViewOptionsSwitch
                            flightId={ this.props.flightId }
                        />
                        <Print
                            flightId={ this.props.flightId }
                        />
                        <FullSize/>
                        <ParamsToggle/>
                    </div>
                </div>
            </nav>
        );
    }
}
