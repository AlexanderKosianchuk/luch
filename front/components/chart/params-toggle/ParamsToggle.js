import './params-toggle.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import trigger from 'actions/trigger';

class ParamsToggle extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isShown: props.isShown || true
        };
    }


    handleClick(event) {
        this.props.trigger(this.state.isShown ? 'chart:hideParamsList' : 'chart:showParamsList');
        this.setState({
            isShown: !this.state.isShown
        })
    }

    render() {
        return (
            <ul className='chart-params-toggle nav navbar-nav navbar-right'>
                <li><a href='#' onClick={ this.handleClick.bind(this) }>
                    <span
                      className='glyphicon glyphicon-list-alt'
                      aria-hidden='true'
                    >
                        <span className={
                              this.state.isShown ? 'chart-params-toggle__strikethrough' : ''
                          }>
                        </span>
                    </span>
                </a></li>
            </ul>
        );
    }
}

function mapStateToProps(state) {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        trigger: bindActionCreators(trigger, dispatch)
    };
}

export default connect(mapStateToProps, mapDispatchToProps)(ParamsToggle);
