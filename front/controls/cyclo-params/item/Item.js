import './item.sass'

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import ColorPicker from 'controls/cyclo-params/color-picker/ColorPicker';

import transmit from 'actions/transmit';
import request from 'actions/request';

class Item extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            colorpickerShown: false,
            paramColor: '#' + props.param.color
        }
    }

    select(event) {
        if (!event.target.classList.contains('cyclo-params-item__colorbox')) {
            let checkstate = !event.currentTarget.parentElement.classList.contains('is-chosen');
            event.currentTarget.parentElement.classList.toggle('is-chosen');

            this.props.transmit(
                'CHANGE_FLIGHT_PARAM_CHECKSTATE',
                {
                    id: this.props.param.id,
                    paramType: this.props.param.type,
                    state: checkstate
                }
            );
        }
    }

    applyColor(color) {
        this.props.request(
            ['fdr', 'setParamColor'],
            'CYCLO_PARAM_COLOR',
            'put',
            {
                flightId: this.props.flightId,
                paramCode: this.props.param.code,
                color: color.replace(/#/g, '')
            }
        ).then(() => {
            this.setState({
                colorpickerShown: !this.state.colorpickerShown,
                paramColor: color
            });
        });
    }

    toggleColorpicker() {
        this.setState({
            colorpickerShown: !this.state.colorpickerShown
        });
    }

    render() {
        return <div className={ 'cyclo-params-item' + (this.props.isChosen ? ' is-chosen' : '') }>
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
                isEnabled={ this.props.colorPickerEnabled }
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
        request: bindActionCreators(request, dispatch),
        transmit: bindActionCreators(transmit, dispatch)
    }
}

export default connect(() => { return {}; }, mapDispatchToProps)(Item);
