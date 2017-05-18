import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import FlightsTopMenu from 'components/flights-top-menu/FlightsTopMenu';
import MainMenu from 'components/main-menu/MainMenu';

import redirectAction from 'actions/redirect';

class MainPage extends React.Component {
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

    handleMenuItemClick (url) {
        this.setState({ showMenu: false });
        this.props.redirect(url);
    }

    render () {
        return (
            <div>
                <FlightsTopMenu
                    toggleMenu={ this.handleToggleMenu.bind(this) }
                />
                <MainMenu
                    isShown={ this.state.showMenu }
                    toggleMenu={ this.handleToggleMenu.bind(this) }
                    handleMenuItemClick={ this.handleMenuItemClick.bind(this) }
                />
            </div>
        );
    }
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirectAction, dispatch),
    }
}

export default connect(() => { return {} }, mapDispatchToProps)(MainPage);
