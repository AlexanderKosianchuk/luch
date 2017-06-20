import './form-print.sass'

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { Translate } from 'react-redux-i18n';

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
            <ul className="flight-events-form-print nav navbar-nav navbar-right">
                <li><section className='flight-events-form-print'>
                    <div className='flight-events-form-print__container'>
                      <input id={ 'flight-events-form-print__input' }
                          type='checkbox'
                          value='None'
                          name='check'
                          checked={ this.state.checkstate }
                          onChange={ this.changeCheckState.bind(this) }
                       />
                      <label htmlFor={ 'flight-events-form-print__input' }></label>
                    </div>
                  </section>
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

function mapStateToProps (state) {
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
