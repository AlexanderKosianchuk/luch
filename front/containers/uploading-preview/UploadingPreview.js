import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'controls/main-page/MainPage';
import Toolbar from 'components/uploading-preview/toolbar/Toolbar';

import showPage from 'actions/showPage';

class UploadingPreview extends React.Component {
    componentDidMount() {
        this.props.showPage('uploadWithPreview',
            [this.props.uploadingUid, this.props.fdrId, this.props.calibrationId]
        );
    }

    render () {
        return (
            <div>
                <MainPage/>
                <Toolbar/>
                <div id='container'></div>
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        uploadingUid: ownProps.match.params.uploadingUid,
        fdrId: ownProps.match.params.fdrId,
        calibrationId: ownProps.match.params.calibrationId
    };
}

function mapDispatchToProps(dispatch) {
    return {
        showPage: bindActionCreators(showPage, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(UploadingPreview);
