import './flight-uploader-progress-indicator.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import CircularProgressbar from 'react-circular-progressbar';
import _constant from 'lodash.constant';

class FlightUploadingProgressIndicator extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isShown: false,
            progress: 0,
            itemsCount: 0
        }
    }

    componentWillReceiveProps (nextProps) {
        if (nextProps.flightUploads.length === 1) {
            this.setState({
                isShown: true,
                progress: nextProps.flightUploads[0].progress,
                itemsCount: 1
            });
        } else if (nextProps.flightUploads.length > 1) {
            let totalProgress = 0;
            totalProgress += nextProps.flightUploads.map((item, index) => {
                return item.progress;
            });

            totalProgress = totalProgress / nextProps.flightUploads.length;

            this.setState({
                isShown: true,
                progress: totalProgress,
                itemsCount: nextProps.flightUploads.length
            });
        } else {
            this.setState({isShown: false});
        }
    }

    render() {
        return (
            <li className="flight-uploader-progress-indicator" >
                <div className={
                        "circular-progressbar "
                        + ( this.state.isShown ? 'is-shown' : '' )
                    }>
                    <CircularProgressbar
                        percentage={ this.state.progress }
                        strokeWidth={ 5 }
                    />
                </div>
            </li>
        );
    }
}

function mapStateToProps (state) {
    return {
        flightUploads: state.flightUploadingState
    }
}

export default connect(mapStateToProps, _constant({}))(FlightUploadingProgressIndicator);
