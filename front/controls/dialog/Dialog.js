import './dialog.sass'

import React from 'react';
import Guid from 'guid';

export default function Dialog (props) {
    let id = "dialog-" + Guid.create();

    if (props.hasOwnProperty('id')) {
        id = props.id;
    }

    function buildBody() {
        if (props.isShown ) {
            return props.buildBody();
        }

        return null;
    }

    return (
        <div id={ id }  className={ "dialog modal " + (props.isShown ? 'is-shown' : '') } role="dialog">
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <button
                    type="button"
                    className="close"
                    data-dismiss="modal"
                    onClick={ props.handleClose }
                >
                    &times;
                </button>
                <h4 className="modal-title">Modal Header</h4>
              </div>
              <div className="modal-body">
                { buildBody() }
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
    );
}
