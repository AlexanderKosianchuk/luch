import './main-menu.sass';

import React from 'react';
import { Translate } from 'react-redux-i18n';
import onClickOutside from 'react-onclickoutside';
import { connect } from 'react-redux';

class MainMenu extends React.Component {
    handleClickOutside(event) {
        this.props.toggleMenu(event.target);
    }

    render() {
        return (
            <div className={ 'main-menu fluid-grid ' + ( this.props.isShown ? '' : 'is-hidden' ) } >
                <div className='main-menu__row'
                        onClick={ this.props.handleMenuItemClick.bind(null, '/') }>
                    <span className='main-menu__glyphicon glyphicon glyphicon-send'></span>
                    <span className='main-menu__label'><Translate value='mainMenu.flights'/></span>
                </div>
                <div className='main-menu__row'
                        onClick={ this.props.handleMenuItemClick.bind(null, '/results') }>
                    <span className='main-menu__glyphicon glyphicon glyphicon-stats'></span>
                    <span className='main-menu__label'><Translate value='mainMenu.results'/></span>
                </div>
                <div className='main-menu__row'
                        onClick={ this.props.handleMenuItemClick.bind(null, '/calibrations') }>
                    <span className='main-menu__glyphicon glyphicon glyphicon-screenshot'></span>
                    <span className='main-menu__label'><Translate value='mainMenu.calibration'/></span>
                </div>
                <div className={ 'main-menu__row' + ((this.props.userRole !== 'admin') ? ' is-hidden' : '') }
                        onClick={ this.props.handleMenuItemClick.bind(null, '/users') }>
                    <span className='main-menu__glyphicon glyphicon glyphicon-user'></span>
                    <span className='main-menu__label'><Translate value='mainMenu.users'/></span>
                </div>
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {
        userRole: state.user.role
    }
}

export default connect(mapStateToProps, () => { return {} })(onClickOutside(MainMenu));
