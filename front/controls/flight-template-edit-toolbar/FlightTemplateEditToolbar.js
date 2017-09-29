import './flight-template-edit-toolbar.sass'

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import NavbarToggle from 'controls/navbar-toggle/NavbarToggle';
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
                      <NavbarToggle/>
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
        servisePurpose: state.template.servisePurpose
    }
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightTemplateEditToolbar);
