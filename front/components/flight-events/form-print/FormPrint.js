import './print-form.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import { Translate } from 'react-redux-i18n';

import Checkbox from 'controls/checkbox/Checkbox';

import eventsFormPrint from 'actions/eventsFormPrint';

class FormPrint extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            checkstate: props.checkstate || ''
        };
    }

    handleClick(event) {
        this.props.eventsFormPrint({
            flightId: this.props.flightId,
            grayscale: (this.state.checkstate === 'checked') ? true : false,
            sections: this.props.flightEvents.expandedSections
        });
    }

    changeCheckState(event) {
        let newCheckstate = 'checked';
        if (this.state.checkstate === 'checked') {
            newCheckstate = '';
        }

        this.setState({ checkstate: newCheckstate });
    }

    render() {
        return (
            <ul className="flight-events-print-form nav navbar-nav navbar-right">
                <li>
                    <Checkbox
                        checkstate={ this.state.checkstate }
                        changeCheckState={ this.changeCheckState.bind(this) }
                    />
                </li>
                <li><a href="#"
                      onClick={ this.changeCheckState.bind(this) }
                    ><Translate value='flightEvents.formPrint.grayscale'/></a>
                </li>
                <li><a href="#" onClick={ this.handleClick.bind(this) }>
                    <span
                      className="glyphicon glyphicon-print"
                      aria-hidden="true">
                    </span>
                </a></li>
            </ul>
        );
    }
}

FormPrint.propTypes = {
    flightId: PropTypes.number.isRequired
};

function mapStateToProps(state) {
    return {
        flightEvents: state.flightEvents
    };
}

function mapDispatchToProps(dispatch) {
    return {
        eventsFormPrint: bindActionCreators(eventsFormPrint, dispatch)
    };
}

export default connect(mapStateToProps, mapDispatchToProps)(FormPrint);
