import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';

import Item from 'controls/cyclo-params/item/Item';

class Tile extends React.Component {
    buildParams(params)
    {
        let items = [];

        if (params
            && (params.length > 0)
        ) {
            params.forEach((item, index) => {
                items.push(<Item
                    key={ index }
                    param={ item }
                />);
            });
        }

        return items;
    }

    render() {
        return <div className='cyclo-params-tile container-fluid'>
            <div className='row'>
                <div className='col-xs-6'>
                    { this.buildParams(this.props.analogParams) }
                </div>
                <div className='col-xs-6'>
                    { this.buildParams(this.props.binaryParams) }
                </div>
            </div>
        </div>;
    }
}

function mapStateToProps (state) {
    return { };
}

function mapDispatchToProps(dispatch) {
    return { };
}

export default connect(mapStateToProps, mapDispatchToProps)(Tile);
