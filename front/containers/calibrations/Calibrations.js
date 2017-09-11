import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import Menu from 'controls/menu/Menu';

import Toolbar from 'components/calibrations/toolbar/Toolbar';
import Table from 'components/calibrations/table/Table';
import { DEFAULT_PAGE_SIZE } from 'controls/table/Table';

function Calibrations (props) {
    return (
        <div>
            <Menu />
            <Toolbar/>
            <Table
                fdrId={ props.fdrId }
                page={ props.page }
                pageSize={ props.pageSize }
            />
        </div>
    );
}

function mapStateToProps(state, ownProps) {
    return {
        fdrId: parseInt(ownProps.match.params.fdrId) || null,
        page: parseInt(ownProps.match.params.page) || 1,
        pageSize: parseInt(ownProps.match.params.pageSize) || DEFAULT_PAGE_SIZE
    };
}

export default connect(mapStateToProps, () => { return {} })(Calibrations);
