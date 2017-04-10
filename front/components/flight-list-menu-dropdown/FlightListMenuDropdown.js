import React from 'react';
import { connect } from 'react-redux';

class FlightListMenuDropdown extends React.Component {
    buildMenu(type) {
        const menuItems = {
            noSelection: [
                "selectAll"
            ],
            oneFlight: [
                "deleteItem",
                "selectAll",
                "exportItem",
                "processItem",
                "exportCoordinates",
                "removeSelection"
            ],
            oneFolder: [
                "openItem",
                "renameItem",
                "deleteItem",
                "selectAll",
                "removeSelection"
            ],
            manyItems: [
                "deleteItem",
                "selectAll",
                "removeSelection"
            ],
        };

        let currentMenuItems = menuItems[type];

        return currentMenuItems.map(item => {
            if (this.props.i18n.hasOwnProperty(item)) {
                return <li key={ item } ><a onClick={ this.handleMenuClick.bind(this) } data-action={ item } href="#">{ this.props.i18n[item] }</a></li>
            }
        });
    }

    setMenu() {
        let flightsCount = this.props.selectedFlights.length;
        let foldersCount = this.props.selectedFolders.length;

        this.menu = this.buildMenu("manyItems");

        if ((flightsCount === 0) && (foldersCount === 0)) {
            this.menu = this.buildMenu("noSelection");
        } else if ((flightsCount === 1) && (foldersCount === 0)) {
            this.menu = this.buildMenu("oneFlight");
        } else if((flightsCount === 0) && (foldersCount === 1)) {
            this.menu = this.buildMenu("oneFolder");
        }
    }

    handleMenuClick(event) {
        let action = event.target.getAttribute("data-action")

        if (!this.props.flightMenuService.hasOwnProperty(action)) {
            throw new Error("Unknown flightMenuService action. Passed: " + action)
        }

        this.props.flightMenuService[action]();
    }

    render() {
        this.setMenu();
        return (
            <ul className="nav navbar-nav">
                <li className="dropdown">
                  <a href="#" className="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    { this.props.i18n.fileMenu } <span className="caret"></span>
                  </a>
                  <ul className="dropdown-menu">
                    { this.menu }
                  </ul>
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
