import './flight-importer-dropdown.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import onClickOutside from 'react-onclickoutside';

class FlightImporterDropdown extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isShown: false
        };
    }

    handleClickOutside(event) {
        if ((event.target.className.includes('flight-importer-dropdown-toggle'))
            && !this.state.isShown
        ) {
            this.setState({ isShown: true });
        } else {
            this.setState({ isShown: false });
        }
    }

    render() {
        return (
            <ul className={ "flight-importer-dropdown dropdown-menu " + ( this.state.isShown ? 'is-shown' : '' ) }>
              <li><a href="#">1</a></li>
              <li><a href="#">One more separated link</a></li>
            </ul>
        );
    }
}

export default onClickOutside(FlightImporterDropdown);
