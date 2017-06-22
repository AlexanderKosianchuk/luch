import './folder-controls.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import deleteFolder from 'actions/deleteFolder';

class FolderControls extends Component {
    constructor (props) {
        super (props);

        this.state = {
            isFormHidden: true,
            name: props.folderInfo.name
        };
    }

    handleClickTrash () {
        if (confirm(I18n.t('flights.folderControls.confirm'))) {
            this.props.deleteFolder({ id: this.props.folderInfo.id });
        }
    }

    handleClickRename () {
        this.resize ();
        this.setState({
            isFormHidden: false
        });
    }

    handleSubmit () {
        this.setState({
            isFormHidden: true
        });
        return false;
    }

    resize () {
        console.log(this.form);
    }

    render () {
        return (
            <div className='flights-folder-controls'>
                <form ref={ (form) => { this.form = form }}
                    className={
                        'flights-folder-controls__form '
                        + (this.state.isFormHidden ? 'is-hidden' : '')
                    }
                    onSubmit={ this.handleSubmit.bind(this) }
                >
                    <input className='form-control' type='text' />
                </form>
                <span
                    className={ 'flights-folder-controls__glyphicon '
                        + 'glyphicon glyphicon-pencil'
                    }
                    onClick={ this.handleClickRename.bind(this) }
                >
                </span>
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
