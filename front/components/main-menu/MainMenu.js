import './main-menu.sass';

import React from 'react';
import onClickOutside from 'react-onclickoutside';

class MainMenu extends React.Component {
    handleClickOutside(event) {
        this.props.toggleMenu(event.target);
    }

    render() {
        return (
            <div className={ "main-menu fluid-grid " + ( this.props.isShown ? '' : 'is-hidden' ) } >
                <div className="main-menu__row"
                        onClick={ this.props.handleMenuItemClick.bind(null, "showFlightsList") }>
                    <span className="main-menu__glyphicon glyphicon glyphicon-send"></span>
                    <span className="main-menu__label">{ this.props.i18n.flights } </span>
                </div>
                <div className="main-menu__row"
                        onClick={ this.props.handleMenuItemClick.bind(null, "showFlightSearch") }>
                    <span className="main-menu__glyphicon glyphicon glyphicon-search"></span>
                    <span className="main-menu__label">{ this.props.i18n.search }</span>
                </div>
                <div className="main-menu__row"
                        onClick={ this.props.handleMenuItemClick.bind(null, "showResults") }>
                    <span className="main-menu__glyphicon glyphicon glyphicon-stats"></span>
                    <span className="main-menu__label">{ this.props.i18n.results }</span>
                </div>
                <div className="main-menu__row"
                        onClick={ this.props.handleMenuItemClick.bind(null, "showCalibrations") }>
                    <span className="main-menu__glyphicon glyphicon glyphicon-screenshot"></span>
                    <span className="main-menu__label">{ this.props.i18n.calibrations }</span>
                </div>
                <div className="main-menu__row"
                        onClick={ this.props.handleMenuItemClick.bind(null, "showUsers") }>
                    <span className="main-menu__glyphicon glyphicon glyphicon-user"></span>
                    <span className="main-menu__label">{ this.props.i18n.users }</span>
                </div>
            </div>
        );
    }
}

export default onClickOutside(MainMenu);
