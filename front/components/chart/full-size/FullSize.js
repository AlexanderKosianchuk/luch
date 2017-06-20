import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import trigger from 'actions/trigger';

class Print extends React.Component {
    handleClick(event) {
        this.props.trigger('chart:fullSize');
    }

    render() {
        return (
            <ul className="nav navbar-nav navbar-right">
                <li><a href="#" onClick={ this.handleClick.bind(this) }>
                    <span
                      className="glyphicon glyphicon-resize-full"
                      aria-hidden="true">
                    </span>
                </a></li>
            </ul>
        );
    }
}

function mapStateToProps (state) {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        trigger: bindActionCreators(trigger, dispatch)
    };
}

export default connect(mapStateToProps, mapDispatchToProps)(Print);
