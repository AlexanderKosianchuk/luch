import './cyclo-params.sass'

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import Tile from 'controls/cyclo-params/tile/Tile';
import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';

class CycloParams extends React.Component {
    componentWillMount() {
        this.props.request(
            ['fdr', 'getCyclo'],
            'get',
            'FDR_CYCLO',
            {
                flightId: this.props.flightId || null,
                fdrId: this.props.fdrId || null
            }
        );
    }

    buildBody() {
        if (this.props.cycloFetching !== false) {
            return <ContentLoader/>
        } else {
            return <Tile
                analogParams={ this.props.fdrCyclo.analogParams }
                binaryParams={ this.props.fdrCyclo.binaryParams }
                chosenAnalogParams={ this.props.fdrCyclo.chosenAnalogParams || [] }
                chosenBinaryParams={ this.props.fdrCyclo.chosenBinaryParams || [] }
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

function mapStateToProps(state) {
    return {
        cycloFetching: state.fdrCyclo.pending,
        fdrCyclo: state.fdrCyclo
    }
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(CycloParams);
