import React from 'react';

import FlightsTopMenu from 'components/flights-top-menu/FlightsTopMenu';
import MainMenu from 'components/main-menu/MainMenu';

export default class Flights extends React.Component {
    constructor(props) {
        super (props);
        this.state = {
            showMenu: false
        };
    }

    handleToggleMenu (target) {
        if ((target.className.includes('main-menu-toggle'))
            && !this.state.showMenu
        ) {
            this.setState({ showMenu: true });
        } else {
            this.setState({ showMenu: false });
        }
    }

    handleMenuItemClick (action) {
        this.props.flightsServise[action]();
        this.setState({ showMenu: false });
    }

    componentDidMount() {
        this.props.flightsServise.showFlightsList();
    }

    render () {
        return (
            <span>
                <FlightsTopMenu
                    i18n={ this.props.i18n }
                    userLogin={ this.props.userLogin }
                    userLang={ this.props.userLang }
                    avaliableLanguages={ this.props.avaliableLanguages }
                    topMenuService={ this.props.topMenuService }
                />
                <MainMenu
                    i18n={ this.props.i18n }
                    isShown={ this.state.showMenu }
                    toggleMenu={ this.handleToggleMenu.bind(this) }
                    handleMenuItemClick={ this.handleMenuItemClick.bind(this) }
                />
                <div id="flightsContainer"></div>
            </span>
        );
    }
}
