import './folder-controls.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { I18n } from 'react-redux-i18n';

import deleteFolder from 'actions/deleteFolder';
import renameFolder from 'actions/renameFolder';

class FolderControls extends Component {
    constructor (props) {
        super (props);

        this.state = {
            isFormHidden: true,
            name: props.folderInfo.name
        };
    }

    handleClickTrash () {
        if (confirm(I18n.t('flightsTree.folderControls.confirm'))) {
            this.props.deleteFolder({ id: this.props.folderInfo.id });
        }
    }

    handleClickRename () {
        if (this.state.isFormHidden === true) {
            this.resize ();
            this.setState({
                isFormHidden: false
            });
        } else if ((this.state.isFormHidden === false)
            && (this.input && this.input.value.length >= 3)) {
            this.setState({
                isFormHidden: true
            });

            this.props.renameFolder({
                id: this.props.folderInfo.id,
                name: this.state.name
            });
        }
    }

    handleSubmit (event) {
        this.setState({
            isFormHidden: true
        });

        this.props.renameFolder({
            id: this.props.folderInfo.id,
            name: this.state.name
        });

        event.preventDefault();
        return false;
    }

    handleChange () {
        this.setState({
            name: this.input.value
        });
    }

    resize () {
        function findAncestor (el, cls) {
            while ((el = el.parentElement) && !el.classList.contains(cls));
            return el;
        }

        let row = findAncestor(this.form, 'rst__rowContents')

        if (row) {
            this.form.style.width = row.clientWidth - 55 + 'px';
        }
    }

    render () {
        return (
            <div className='flights-tree-folder-controls'>
                <form ref={ (form) => { this.form = form }}
                    className={
                        'flights-tree-folder-controls__form '
                        + (this.state.isFormHidden ? 'is-hidden' : '')
                    }
                    onSubmit={ this.handleSubmit.bind(this) }
                >
                    <input className='form-control' type='text'
                        value={ this.state.name }
                        ref={ (input) => { this.input = input }}
                        onChange={ this.handleChange.bind(this) }
                    />
                </form>
                <span
                    className={ 'flights-tree-folder-controls__glyphicon '
                        + 'glyphicon '
                        + (this.state.isFormHidden
                            ? 'glyphicon-pencil' : 'glyphicon-floppy-disk')
                    }
                    onClick={ this.handleClickRename.bind(this) }
                >
                </span>
                <span
                    className={
                        'flights-tree-folder-controls__glyphicon '
                        + 'flights-tree-folder-controls__glyphicon-danger '
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
        renameFolder: bindActionCreators(renameFolder, dispatch),
    }
}

export default connect(() => { return {}; }, mapDispatchToProps)(FolderControls);
