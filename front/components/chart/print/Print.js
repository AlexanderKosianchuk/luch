import './print.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { Translate } from 'react-redux-i18n';

import flightDataTablePrint from 'actions/flightDataTablePrint';

class Print extends React.Component {
    handleClick(event) {
        this.props.flightDataTablePrint({
            flightId: this.props.flightId,
            startFrame: this.props.startFrame,
            endFrame: this.props.endFrame,
            analogParams: this.props.templateInfo.ap || [],
            binaryParams: this.props.templateInfo.bp || [],
        });
    }

    render() {
        let modifyer = '';
        if (this.props.startFrame === this.props.endFrame) {
            modifyer = 'is-disabled';
        }

        return (
            <ul className={ 'chart-print nav navbar-nav navbar-right ' + modifyer }>
                <li><a href='#' onClick={ this.handleClick.bind(this) }>
                    <span
                      className='glyphicon glyphicon-print'
                      aria-hidden='true'>
                    </span>
                </a></li>
            </ul>
        );
    }
}

function mapStateToProps(state) {
    return {
        startFrame: state.flightInfo.selectedStartFrame,
        endFrame: state.flightInfo.selectedEndFrame,
        templateInfo: state.templateInfo || { ap: null, bp: null }
    };
}

function mapDispatchToProps(dispatch) {
    return {
        flightDataTablePrint: bindActionCreators(flightDataTablePrint, dispatch)
    };
}

export default connect(mapStateToProps, mapDispatchToProps)(Print);
