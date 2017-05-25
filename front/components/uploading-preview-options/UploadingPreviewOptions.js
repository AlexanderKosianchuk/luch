import React from 'react';
import { Translate } from 'react-redux-i18n';

import trigger from 'actions/redirect';

class UploadingPreviewOptions extends React.Component {
    handleUploadClick()
    {
        this.props.trigger('uploadPreviewedFlight');
    }

    render()
    {
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
                      <a className="navbar-brand" href="#"><Translate value='uploadingPreviewOptions.preview' /></a>
                    </div>

                    <div className="collapse navbar-collapse" id="bs-navbar-collapse">
                      <ul className="nav navbar-nav navbar-right">
                        <li><a href="#">
                            <span
                                onClick={ this.handleUploadClick.bind(this) }
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
        trigger: bindActionCreators(trigger, dispatch)
    }
}

export default connect(() => { return {} }, mapDispatchToProps)(UploadingPreviewOptions);
