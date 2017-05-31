import './item.sass'

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import ColorPicker from 'controls/cyclo-params/color-picker/ColorPicker';

class Item extends React.Component {
    constructor(props)
    {
        super(props);

        this.state = {
            colorpickerShown: false
        }
    }

    select(event)
    {
        if (!event.target.classList.contains('cyclo-params-item__colorbox')) {
            event.currentTarget.classList.toggle('is-chosen');
        }
    }

    toggleColorpicker()
    {
        this.setState({
            colorpickerShown: !this.state.colorpickerShown
        });
    }

    render() {
        return <div className='cyclo-params-item'>
            <div className='cyclo-params-item__box' onClick={ this.select.bind(this) }>
                <div className='cyclo-params-item__colorbox'
                    style={{ backgroundColor: '#'+this.props.param.color }}
                    onClick={ this.toggleColorpicker.bind(this) }
                >
                </div>
                <div className='cyclo-params-item__label'>
                    <div className='cyclo-params-item__code'>
                        { this.props.param.code }
                    </div>
                    <div className='cyclo-params-item__name'>
                        { this.props.param.name }
                    </div>
                </div>
            </div>
            <ColorPicker
                isShown={ this.state.colorpickerShown }
                color={ this.props.param.color }
                toggleColorPicker={ this.toggleColorpicker.bind(this) }
            />
        </div>;
    }
}

function mapStateToProps (state) {
    return {}
}

function mapDispatchToProps(dispatch) {
    return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(Item);
