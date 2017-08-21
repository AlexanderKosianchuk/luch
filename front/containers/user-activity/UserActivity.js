import React from 'react';
import { connect } from 'react-redux';

import Menu from 'controls/menu/Menu';

import Toolbar from 'components/user-activity/toolbar/Toolbar';
import Table from 'components/user-activity/table/Table';

function UserActivity(props) {
    return (
        <div>
            <Menu/>
            <Toolbar/>
            <Table
                userId={ props.userId }
            />
        </div>
    );
}

function mapStateToProps(state, ownProps) {
    return {
        userId: parseInt(ownProps.match.params.userId) || null
    };
}

export default connect(mapStateToProps, () => { return {} })(UserActivity);
