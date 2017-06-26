import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import PropTypes from 'prop-types';

import CreateFolderButton from 'components/flights-tree/create-folder-button/CreateFolderButton'
import CreateFolderForm from 'components/flights-tree/create-folder-form/CreateFolderForm'

import createFolder from 'actions/createFolder';

class CreateFolder extends Component {
    constructor(props) {
        super(props);

        this.state = {
            isInputShown: false
        }
    }

    handleSaveFolderClick(event, folderName) {
        this.setState({
            isInputShown: false
        });

        this.props.createFolder({
            name: folderName
        });
    }

    handleCreateFolderClick () {
        this.setState({
            isInputShown: true
        });
    }

    render() {
        return this.state.isInputShown
            ? <CreateFolderForm handleSaveFolderClick={ this.handleSaveFolderClick.bind(this) } />
            : <CreateFolderButton handleCreateFolderClick={ this.handleCreateFolderClick.bind(this) } />
    }
}

function mapDispatchToProps(dispatch) {
    return {
        createFolder: bindActionCreators(createFolder, dispatch)
    }
}

export default connect(() => { return {} }, mapDispatchToProps)(CreateFolder);
