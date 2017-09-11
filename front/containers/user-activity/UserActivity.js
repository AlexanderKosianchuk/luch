import React from 'react';
import { connect } from 'react-redux';

import Menu from 'controls/menu/Menu';

import Toolbar from 'components/user-activity/toolbar/Toolbar';
import Table from 'components/user-activity/table/Table';
import { DEFAULT_PAGE_SIZE } from 'controls/table/Table';

function UserActivity(props) {
    return (
        <div>
            <Menu/>
            <Toolbar/>
            <Table
                userId={ props.userId }
                page={ props.page }
                pageSize={ props.pageSize }
            />
        </div>
    );
}

function mapStateToProps(state, ownProps) {
    return {
        userId: parseInt(ownProps.match.params.userId) || null,
        page: parseInt(ownProps.match.params.page) || 1,
        pageSize: parseInt(ownProps.match.params.pageSize) || DEFAULT_PAGE_SIZE
    };
}

export default connect(mapStateToProps, () => { return {} })(UserActivity);
