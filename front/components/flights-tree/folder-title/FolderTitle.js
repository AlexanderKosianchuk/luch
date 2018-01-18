import './folder-title.sass';

import React from 'react';

export default function FolderTitle(props) {
  return (
    <div className='flights-tree-folder-title'>
      { props.folderInfo.name }
    </div>
  );
}
