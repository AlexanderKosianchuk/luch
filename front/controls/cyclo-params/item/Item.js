import './item.sass'

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import ColorPicker from 'controls/cyclo-params/color-picker/ColorPicker';

import setParamColor from 'actions/setParamColor';

class Item extends React.Component {
    constructor(props)
    {
        super(props);

        this.state = {
            colorpickerShown: false,
            paramColor: '#' + props.param.color
        }
    }

    select(event)
    {
        if (!event.target.classList.contains('cyclo-params-item__colorbox')) {
            event.currentTarget.parentElement.classList.toggle('is-chosen');
        }
    }

    applyColor(color)
    {
        this.props.setParamColor({
            fdrId: this.props.fdrId,
            paramCode: this.props.param.code,
            color: color.replace(/#/g, '')
        }).then(() => {
            this.setState({
                colorpickerShown: !this.state.colorpickerShown,
                paramColor: color
            });
        });
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
                    style={{ backgroundColor: this.state.paramColor }}
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
                isDisabled={ false }
                isShown={ this.state.colorpickerShown }
                color={ this.state.paramColor }
                toggleColorPicker={ this.toggleColorpicker.bind(this) }
                applyColor={ this.applyColor.bind(this) }
            />
        </div>;
    }
}

function mapDispatchToProps(dispatch) {
    return {
        setParamColor: bindActionCreators(setParamColor, dispatch)
    }
}

export default connect(() => { return {}; }, mapDispatchToProps)(Item);
