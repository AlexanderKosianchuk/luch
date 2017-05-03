import React from 'react';

export default class SettlementsFilterItem extends React.Component {
    static checkbox = null;

    constructor(props) {
        super(props);
        this.state = {
            isChecked: true
        };
    }

    changeCheckstate(event) {
        this.props.changeCheckstate({
            id: this.props.id,
            state: this.checkbox.checked
        });

        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        this.setState({
            isChecked: value
        });
    }

    render() {
        return (
            <div className="checkbox">
                <label>
                    <input type="checkbox" checked={this.state.isChecked}
                        id={this.props.id} onChange={this.changeCheckstate.bind(this)}
                        ref={(input) => { this.checkbox = input; }} />
                    <span>{this.props.label}</span>
                </label>
            </div>
        );
    }
}
