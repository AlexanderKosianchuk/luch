import './flight-template-edit-toolbar.sass'

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import SaveForm from 'controls/flight-template-edit-toolbar/save-form/SaveForm';

import redirect from 'actions/redirect';

class FlightTemplateEditToolbar extends React.Component {
    handleChangeView(event) {
        this.props.redirect('/flight-templates/' + this.props.flightId);
    }

    render() {
        return (
            <nav className='flight-template-edit-toolbar navbar navbar-default'>
                <div className='container-fluid'>
                    <div className='navbar-header'>
                      <button type='button' className='navbar-toggle collapsed' data-toggle='collapse' data-target='#bs-navbar-collapse' aria-expanded='false'>
                        <span className='sr-only'>Toggle navigation CreateFlightTemplateToolbar component</span>
                        <span className='icon-bar'></span>
                        <span className='icon-bar'></span>
                        <span className='icon-bar'></span>
                      </button>
                    </div>

                    <div className='collapse navbar-collapse' id='bs-navbar-collapse'>
                        <ul className='nav navbar-nav'>
                            <li onClick={ this.handleChangeView.bind(this) } >
                                <a href='#'>{ I18n.t('flightTemplateEdit.toolbar.templates') }</a>
                            </li>
                        </ul>

                        <SaveForm
                            flightId={ this.props.flightId }
                            templateName={ this.props.templateName }
                            servisePurpose={ this.props.servisePurpose }
                        />
                    </div>
                </div>
            </nav>
        );
    }
}

function mapStateToProps(state) {
    return {
        servisePurpose: state.templateInfo.servisePurpose
    }
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightTemplateEditToolbar);
