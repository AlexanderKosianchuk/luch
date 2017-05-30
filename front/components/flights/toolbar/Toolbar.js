import './toolbar.sass'

import React from 'react';
import { Translate } from 'react-redux-i18n';

import TypeSwitch from 'components/flights/type-switch/TypeSwitch';
import MenuDropdown from 'components/flights/menu-dropdown/MenuDropdown';

export default class Toolbar extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            glyphicon: "glyphicon-unchecked"
        }
    }

    toggleFlightCheckboxes() {
        // temporary disabled
        /*this.props.toggleCheckboxes();
        let currentState = this.state.glyphicon === "glyphicon-remove"
            ? "glyphicon-unchecked"
            : "glyphicon-remove";
        this.setState(
            { glyphicon: currentState }
        );*/
    }

    render() {
        return (
            <nav className="flights-toolbar navbar navbar-default">
                <div className="container-fluid">
                    <div className="navbar-header">
                      <button type="button" className="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse" aria-expanded="false">
                        <span className="sr-only">Toggle navigation</span>
                        <span className="icon-bar"></span>
                        <span className="icon-bar"></span>
                        <span className="icon-bar"></span>
                      </button>
                      <a className="navbar-brand" href="#"><Translate value='flights.options.flightList' /></a>
                    </div>

                    <div className="collapse navbar-collapse" id="bs-navbar-collapse">

                      <TypeSwitch viewType={ this.props.viewType } />

                      <MenuDropdown/>

                      <ul className="nav navbar-nav navbar-right">
                        <li><a href="#">
                            <span
                                onClick={ this.toggleFlightCheckboxes.bind(this) }
                                className={ "toggle-flight-checkboxes glyphicon " + this.state.glyphicon }
                                aria-hidden="true">
                            </span>
                        </a></li>
                      </ul>
                    </div>
                </div>
            </nav>
        );
    }
}
