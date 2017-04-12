import './flight-uploader-progress-indicator.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import CircularProgressbar from 'react-circular-progressbar';

class FlightUploadingProgressIndicator extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isShown: false
        }
    }

    render() {
        return (
            <li><div className={
                    "flight-uploader-progress-indicator"
                    + ( this.state.isShown ? 'is-shown' : '' )
                }>
                <CircularProgressbar
                    percentage={60}
                    strokeWidth={5}
                />
            </div></li>
        );
    }
}

function mapStateToProps (state) {
    return {
        uploads: state.flightUploadingState.uploads
    }
}

export default connect(mapStateToProps)(FlightUploadingProgressIndicator);
