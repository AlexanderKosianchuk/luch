import './folder-controls.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import deleteFolder from 'actions/deleteFolder';

class FolderControls extends Component {
    handleClickTrash() {
        if (confirm(I18n.t('flights.folderControls.confirm'))) {
            this.props.deleteFolder({ id: this.props.folderInfo.id });
        }
    }

    render () {
        return (
            <div className='flights-folder-controls'>
                <span className='flights-folder-controls__glyphicon glyphicon glyphicon-pencil'></span>
                <span
                    className={
                        'flights-folder-controls__glyphicon '
                        + 'flights-folder-controls__glyphicon-danger '
                        + 'glyphicon glyphicon-trash'
                    }
                    onClick={ this.handleClickTrash.bind(this) }
                >
                </span>
            </div>
        );
    }
}

function mapDispatchToProps(dispatch) {
    return {
        deleteFolder: bindActionCreators(deleteFolder, dispatch),
    }
}

export default connect(() => { return {}; }, mapDispatchToProps)(FolderControls);
