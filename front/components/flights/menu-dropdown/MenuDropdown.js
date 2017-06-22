import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate, I18n } from 'react-redux-i18n';

import trigger from 'actions/trigger';

class MenuDropdown extends React.Component {
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
            return <li key={ item } >
                <a onClick={ this.handleMenuClick.bind(this) }
                    data-action={ item } href="#">{ I18n.t('flights.menuDropdown.'+item) }
                </a>
            </li>;
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
        this.props.trigger('flightMenuService:' + action);
    }

    render() {
        this.setMenu();
        return (
            <ul className="nav navbar-nav">
                <li className="dropdown">
                  <a href="#" className="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <Translate value='flights.menuDropdown.fileMenu' /><span className="caret"></span>
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
    return {
        trigger: bindActionCreators(trigger, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(MenuDropdown);
