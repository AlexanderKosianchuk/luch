import './item-checkbox.sass'

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import chooseFlightTemplate from 'actions/chooseFlightTemplate';

class ItemCheckbox extends React.Component {
    constructor(props)
    {
        super(props);

        this.state = {
            checkstate: props.checkstate || ''
        };
    }

    componentDidMount()
    {
        if (this.state.checkstate === 'checked') {
            this.props.chooseFlightTemplate({
                checkstate: this.state.checkstate,
                name: this.props.name
            });
        }
    }

    changeCheckState()
    {
        let newCheckstate = 'checked';
        if (this.state.checkstate === 'checked') {
            newCheckstate = '';
        }

        this.props.chooseFlightTemplate({
            checkstate: newCheckstate,
            name: this.props.name
        });

        this.setState({ checkstate: newCheckstate });
    }

    render ()
    {
        return (
            <section className='flight-templates-item-checkbox'>
                <div className='flight-templates-item-checkbox__container'>
                  <input id={ 'flight-templates-item-checkbox_' + this.props.name }
                      type='checkbox'
                      value='None'
                      name='check'
                      checked={ this.state.checkstate }
                      onChange={ this.changeCheckState.bind(this) }
                   />
                  <label htmlFor={ 'flight-templates-item-checkbox_' + this.props.name }></label>
                </div>
              </section>
        );
    }
}

function mapStateToProps(state) {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        chooseFlightTemplate: bindActionCreators(chooseFlightTemplate, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(ItemCheckbox);
