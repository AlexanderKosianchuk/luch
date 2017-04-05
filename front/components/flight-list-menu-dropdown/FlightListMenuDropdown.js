import React from 'react';
import { connect } from 'react-redux';

import FlightListMenuNoSelection from 'components/flight-list-menu-dropdown/FlightListMenuNoSelection';
import FlightListMenuOneFlight from 'components/flight-list-menu-dropdown/FlightListMenuOneFlight';
import FlightListMenuOneFolder from 'components/flight-list-menu-dropdown/FlightListMenuOneFolder';
import FlightListMenuManyItems from 'components/flight-list-menu-dropdown/FlightListMenuManyItems';

class FlightListMenuDropdown extends React.Component {
    setMenu() {
        let flightsCount = this.props.selectedFlights.length;
        let foldersCount = this.props.selectedFolders.length;

        this.menu = <FlightListMenuNoSelection i18n={ this.props.i18n }/>

        if ((flightsCount === 1) && (foldersCount === 0)) {
            this.menu = <FlightListMenuOneFlight i18n={ this.props.i18n }/>
        } else if((flightsCount === 0) && (foldersCount === 1)) {
            this.menu = <FlightListMenuOneFolder i18n={ this.props.i18n }/>
        } else if(((flightsCount === 0) && (foldersCount > 1))
            || ((flightsCount > 1) && (foldersCount === 0))
            || ((flightsCount > 1) && (foldersCount > 1))
        ) {
            this.menu = <FlightListMenuManyItems i18n={ this.props.i18n }/>
        }

    }

    handleChangeView(event) {

    }

    render() {
        this.setMenu();
        return (
            <ul className="nav navbar-nav">
                <li className="dropdown">
                  <a href="#" className="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    { this.props.i18n.fileMenu } <span className="caret"></span>
                  </a>
                  { this.menu }
                </li>
            </ul>
        );
    }
}

function mapStateToProps (state) {
    return { ... state.chosenFlightListItems };
}

function mapDispatchToProps(dispatch) {
    return {};
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightListMenuDropdown);
