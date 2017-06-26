import './flight-controls.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import redirect from 'actions/redirect';
import deleteFlight from 'actions/deleteFlight';

let buttons = [
    { key: 'events', classModifyer: 'glyphicon-flag', handler: 'handleClickShowFlightEvents' },
    { key: 'params', classModifyer: 'glyphicon-check', handler: 'handleClickShowFlightParams' },
    { key: 'templates', classModifyer: 'glyphicon-star', handler: 'handleClickShowFlightTemplates' },
    { key: 'view', classModifyer: 'glyphicon-picture', handler: 'handleClickShowChart' },
];

class FlightControls extends Component {
    handleClickTrash() {
        if (confirm(I18n.t('flightsTree.flightControls.confirm'))) {
            this.props.deleteFlight({ id: this.props.flightInfo.id });
        }
    }

    handleClickShowFlightEvents() {
        this.props.redirect('/flight-events/' + this.props.flightInfo.id);
    }

    handleClickShowFlightTemplates() {
        this.props.redirect('/flight-templates/' + this.props.flightInfo.id);
    }

    handleClickShowFlightParams() {
        this.props.redirect('/flight-params/' + this.props.flightInfo.id);
    }

    handleClickShowChart() {
        this.props.redirect('/chart'
            + '/flight-id/' + this.props.flightInfo.id
            +'/template-name/default'
            + '/from-frame/0'
            + '/to-frame/' + this.props.flightInfo.framesCount);
    }

    buildButtons() {
        let control = (
            <span
                className={ 'flights-tree-flight-controls__glyphicon glyphicon glyphicon-flag' }
                onClick={ this.handleClickShowFlightEvents.bind(this) }
            ></span>
        );

        if (this.props.pending !== false) {
            return control;
        }

        buttons.forEach(((item, index) => {
            if ((this.props.settings.flightShowAction === item.key)
                && (typeof this[item.handler] === 'function')
            ) {
                control = (
                    <span
                        className={ 'flights-tree-flight-controls__glyphicon glyphicon ' + item.classModifyer }
                        onClick={ this[item.handler].bind(this) }
                    ></span>
                );
            }
        }).bind(this));

        return control;
    }

    render() {
        return (
            <div className='flights-tree-flight-controls'>
                { this.buildButtons() }
                <span
                    className={
                        'flights-tree-flight-controls__glyphicon '
                        + 'flights-tree-flight-controls__glyphicon-danger '
                        + 'glyphicon '
                        + 'glyphicon-trash'
                    }
                    onClick={ this.handleClickTrash.bind(this) }
                ></span>
            </div>
        );
    }
}

function mapStateToProps (state) {
    return {
        pending: state.settings.pending,
        settings: state.settings.items
    };
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirect, dispatch),
        deleteFlight: bindActionCreators(deleteFlight, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightControls);
