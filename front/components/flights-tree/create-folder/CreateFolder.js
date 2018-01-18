import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import PropTypes from 'prop-types';

import CreateFolderButton from 'components/flights-tree/create-folder-button/CreateFolderButton';
import ToolbarInput from 'controls/toolbar-input/ToolbarInput';

import request from 'actions/request';

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

    this.props.request(
      ['folder', 'createFolder'],
      'post',
      'FOLDER',
      { folderName: folderName }
    );
  }

  handleCreateFolderClick () {
    this.setState({
      isInputShown: true
    });
  }

  render() {
    return this.state.isInputShown
      ? <ToolbarInput handleSaveClick={ this.handleSaveFolderClick.bind(this) } />
      : <CreateFolderButton handleClick={ this.handleCreateFolderClick.bind(this) } />
  }
}

function mapDispatchToProps(dispatch) {
  return {
    request: bindActionCreators(request, dispatch)
  }
}

export default connect(() => { return {} }, mapDispatchToProps)(CreateFolder);
