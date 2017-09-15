import React, { Component } from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import ContentLoader from 'controls/content-loader/ContentLoader';

import request from 'actions/request';

class AvaliableFdrsSelector extends Component {
    componentWillMount() {
        if (this.props.fdrsPending !== false) {
            this.props.request(
                ['fdr', 'getFdrs'],
                'get',
                'FDRS'
            );
        }
    }

    buildList() {
        let fdrs = this.props.fdrs.items;

        if (!fdrs || fdrs.length === 0) {
            return [];
        }

        return fdrs.map((item) => {
            return <option key={ item.id } value={ item.id }>{ item.name }</option>
        });
    }

    render() {
        if (this.props.fdrsPending !== false) {
            return <ContentLoader margin={ 5 } size={ 75 } />
        }

        return (
            <select className="form-control" name="avaliableFdrs[]" multiple="multiple">
                { this.buildList() }
            </select>
        );
    }
}

function mapStateToProps(state) {
    return {
        fdrsPending: state.fdrs.pending,
        fdrs: state.fdrs,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        request: bindActionCreators(request, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(AvaliableFdrsSelector);
