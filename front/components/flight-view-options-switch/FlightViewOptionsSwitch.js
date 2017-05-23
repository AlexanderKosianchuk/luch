import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import redirectAction from 'actions/redirect';

const views = [
    'events', 'params', 'templates'
]

class FlightViewOptionsSwitch extends React.Component {
    handleChangeView(event) {
        let viewState = event.target.getAttribute("data");

        this.props.redirect('/flight-' + viewState + '/' + this.props.flightId);
    }

    buildMenu()
    {
        let menu = [];
        let that = this;
        views.forEach(function(item) {
            menu.push(
                <li key={ item } onClick={ that.handleChangeView.bind(that) } className={ (item  === that.props.view) ? 'active' : '' }>
                    <a data={ item } href="#">{ I18n.t('flightViewOptionsSwitch.' + item) }</a>
                </li>
            );
        });
        return menu;
    }

    render() {
        return (
            <ul className="nav navbar-nav">
                { this.buildMenu() }
            </ul>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirectAction, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightViewOptionsSwitch);
