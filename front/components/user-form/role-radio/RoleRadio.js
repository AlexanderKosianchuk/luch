import React, { Component } from 'react';
import { Translate } from 'react-redux-i18n';

export default class RoleRadio extends Component {
    constructor (props) {
        super(props);

        this.state = {
            adminCheckstate: (props.role === 'admin'),
            moderatorCheckstate: (props.role === 'moderator'),
            userCheckstate: (props.role === 'user') || (props.role === '')
        }
    }

    componentWillReceiveProps(nextProps) {
        this.setState({
            adminCheckstate: (nextProps.role === 'admin'),
            moderatorCheckstate: (nextProps.role === 'moderator'),
            userCheckstate: (nextProps.role === 'user') || (nextProps.role === '')
        });
    }

    handleChange(newVal) {
        this.setNewRole(newVal);
    }

    setNewRole(newVal) {
        let attr = newVal + 'Checkstate';
        let newState = { ...{
                adminCheckstate: false,
                moderatorCheckstate: false,
                userCheckstate: false,
            }, ... {
                [attr]: true
            }
        }

        this.setState(newState);
    }

    render() {
        return (
            <div className='checkbox'>
              <label>
                  <input type='radio' name='role' value='admin'
                      checked={ !!this.state.adminCheckstate }
                      onChange={ this.handleChange.bind(this, 'admin') }
                  />
                  <Translate value='userForm.roleRadio.admin'/>
              </label>
              <label>
                  <input type='radio' name='role' value='moderator'
                      checked={ !!this.state.moderatorCheckstate }
                      onChange={ this.handleChange.bind(this, 'moderator') }
                  />
                  <Translate value='userForm.roleRadio.moderator'/>
              </label>
              <label>
                  <input type='radio' name='role' value='user'
                      checked={ !!this.state.userCheckstate }
                      onChange={ this.handleChange.bind(this, 'user') }
                  />
                  <Translate value='userForm.roleRadio.user'/>
              </label>
            </div>
        );
    }
}
