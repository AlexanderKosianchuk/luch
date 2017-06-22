import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate } from 'react-redux-i18n';

import trigger from 'actions/trigger';
import redirect from 'actions/redirect';

class Toolbar extends React.Component {
    handleUploadClick() {
        this.props.trigger('uploadPreviewedFlight');
        this.props.redirect('/');
    }

    render() {
        return (
            <nav className="navbar navbar-default">
                <div className="container-fluid">
                    <div className="navbar-header">
                      <button type="button" className="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse" aria-expanded="false">
                        <span className="sr-only">Toggle navigation</span>
                        <span className="icon-bar"></span>
                        <span className="icon-bar"></span>
                        <span className="icon-bar"></span>
                      </button>
                      <a className="navbar-brand" href="#"><Translate value='uploadingPreview.toolbar.preview' /></a>
                    </div>

                    <div className="collapse navbar-collapse" id="bs-navbar-collapse">
                      <ul className="nav navbar-nav navbar-right">
                        <li><a href="#" onClick={ this.handleUploadClick.bind(this) }>
                            <span
                                className={ "glyphicon glyphicon-upload" }
                                aria-hidden="true">
                            </span>
                        </a></li>
                      </ul>
                    </div>
                </div>
            </nav>
        );
    }
}

function mapDispatchToProps(dispatch) {
    return {
        trigger: bindActionCreators(trigger, dispatch),
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(() => { return {} }, mapDispatchToProps)(Toolbar);
