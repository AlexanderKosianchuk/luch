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
        this.props.getFdrCyclo({ fdrId: this.props.fdrId });
    }

    buildBody()
    {
        if (this.props.cycloFetching !== false) {
            return <ContentLoader/>
        } else {
            return <Tile
                analogParams={ this.props.fdrCyclo.analogParams }
                binaryParams={ this.props.fdrCyclo.binaryParams }
                fdrId={ this.props.fdrId }
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
