import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import TopMenu from 'controls/top-menu/TopMenu';
import MainMenu from 'controls/main-menu/MainMenu';

import redirect from 'actions/redirect';

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
                <TopMenu
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
        redirect: bindActionCreators(redirect, dispatch),
    }
}

export default connect(() => { return {} }, mapDispatchToProps)(MainPage);
