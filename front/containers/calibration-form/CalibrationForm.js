import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import Menu from 'controls/menu/Menu';

import Toolbar from 'components/calibrations/toolbar/Toolbar';
import Table from 'components/calibrations/table/Table';
import { DEFAULT_PAGE_SIZE } from 'controls/table/Table';

function CalibrationForm (props) {
    return (
        <div>
            <Menu />
            <Toolbar
                fdrId={ props.fdrId }
            />
        </div>
    );
}

function mapStateToProps(state, ownProps) {
    return {
        fdrId: parseInt(ownProps.match.params.fdrId) || null,
    };
}

export default connect(mapStateToProps, () => { return {} })(CalibrationForm);
