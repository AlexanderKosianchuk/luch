import './flight-uploader-progress-indicator.sass';

import React from 'react';
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
        let setIndicatorState = function(totalProgress, flightUploadsCount) {
            let currentProgress = Math.max(
                Math.round(totalProgress / flightUploadsCount),
                this.state.progress
            );

            this.setState({
                isShown: true,
                progress: currentProgress,
                itemsCount: flightUploadsCount
            });
        }

        if (nextProps.flightUploads.length === 1) {
            setIndicatorState.call(
                this, nextProps.flightUploads[0].progress, 1
            );
        } else if (nextProps.flightUploads.length > 1) {
            let totalProgress = nextProps.flightUploads.reduce(
                (previousValue, currentValue) => {
                    return previousValue.progress + currentValue.progress;
                }
            );

            setIndicatorState.call(
                this, totalProgress, nextProps.flightUploads.length
            );
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
