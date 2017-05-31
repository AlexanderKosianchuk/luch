import './toolbar.sass'

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import SaveButton from 'components/create-flight-template/save-button/SaveButton';

import redirect from 'actions/redirect';

class Toolbar extends React.Component {
    handleChangeView(event) {
        this.props.redirect('/flight-templates/' + this.props.flightId);
    }

    render() {
        return (
            <nav className="create-flight-template-toolbar navbar navbar-default">
                <div className="container-fluid">
                    <div className="navbar-header">
                      <button type="button" className="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse" aria-expanded="false">
                        <span className="sr-only">Toggle navigation CreateFlightTemplateToolbar component</span>
                        <span className="icon-bar"></span>
                        <span className="icon-bar"></span>
                        <span className="icon-bar"></span>
                      </button>
                    </div>

                    <div className="collapse navbar-collapse" id="bs-navbar-collapse">
                        <ul className="nav navbar-nav">
                            <li onClick={ this.handleChangeView.bind(this) } >
                                <a href="#">{ I18n.t('createFlightTemplate.toolbar.templates') }</a>
                            </li>
                        </ul>

                        <SaveButton
                            flightId={ this.props.flightId }
                        />
                    </div>
                </div>
            </nav>
        );
    }
}

function mapStateToProps (state) {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(Toolbar);
