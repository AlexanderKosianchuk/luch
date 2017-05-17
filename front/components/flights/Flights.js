import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import FlightsTopMenu from 'components/flights-top-menu/FlightsTopMenu';
import MainMenu from 'components/main-menu/MainMenu';

import showFlightsListAction from 'actions/showFlightsList';

class Flights extends React.Component {
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
        this.setState({ showMenu: false });
    }

    componentDidMount() {
        this.props.showFlightsList();
    }

    render () {
        return (
            <span>
                <FlightsTopMenu />
                <MainMenu
                    isShown={ this.state.showMenu }
                    toggleMenu={ this.handleToggleMenu.bind(this) }
                    handleMenuItemClick={ this.handleMenuItemClick.bind(this) }
                />
                <div id="flightsContainer"></div>
            </span>
        );
    }
}

function mapDispatchToProps(dispatch) {
    return {
        showFlightsList: bindActionCreators(showFlightsListAction, dispatch)
    }
}

export default connect(() => { return {} }, mapDispatchToProps)(Flights);
