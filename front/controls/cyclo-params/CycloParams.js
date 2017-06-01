import './cyclo-params.sass'

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import Tile from 'controls/cyclo-params/tile/Tile';
import ContentLoader from 'controls/content-loader/ContentLoader';
import getFdrCyclo from 'actions/getFdrCyclo';

class CycloParams extends React.Component {
    componentWillMount()
    {
        if (this.props.flightId) {
            this.props.getFdrCyclo({ flightId: this.props.flightId });
        } else {
            throw new Error('Invalid component configuretion. flightId not passed.')
        }
    }

    buildBody()
    {
        if (this.props.cycloFetching !== false) {
            return <ContentLoader/>
        } else {
            return <Tile
                analogParams={ this.props.fdrCyclo.analogParams }
                binaryParams={ this.props.fdrCyclo.binaryParams }
                checkedAnalogParams={ this.props.checkedAnalogParams || [] }
                checkedBinaryParams={ this.props.checkedBinaryParams || [] }
                flightId={ this.props.flightId }
                colorPickerEnabled={ this.props.colorPickerEnabled }
            />
        }
    }

    render() {
        return <div className='cyclo-params'>
            { this.buildBody() }
        </div>;
    }
}

function mapStateToProps (state) {
    return {
        cycloFetching: state.fdrCyclo.pending,
        fdrCyclo: state.fdrCyclo
    }
}

function mapDispatchToProps(dispatch) {
    return {
        getFdrCyclo: bindActionCreators(getFdrCyclo, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(CycloParams);
