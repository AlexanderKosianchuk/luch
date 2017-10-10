import './chart.sass'

import React from 'react';
import PropTypes from 'prop-types';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, ReferenceLine } from 'recharts';

export default function Chart(props) {
    function buildReferenceLines(minValue, maxValue) {
        if ((minValue === maxValue)
            || !Number.isInteger(maxValue)
            || !Number.isInteger(minValue)
        ){
            return null;
        }

        return [
            <ReferenceLine key='min' x={ minValue } stroke='#c9302c'/>,
            <ReferenceLine key='max' x={ maxValue } stroke='#ac2925'/>
        ];
    }

    return (
        <div className='calibration-form-chart'>
            <LineChart layout='vertical'
                width={ props.width || 0 }
                height={ props.height || 0}
                data={ props.data }
            >
               <XAxis type='number'/>
               <YAxis dataKey='y' type='number' reversed={ true } />
               { buildReferenceLines(props.minValue, props.maxValue) }
               <CartesianGrid strokeDasharray='3 3'/>
               <Line dataKey='x' stroke='#8884d8' />
            </LineChart>
        </div>
    );
}

Chart.propTypes = {
    data: PropTypes.array.isRequired,
    minValue: PropTypes.number.isRequired,
    maxValue: PropTypes.number.isRequired
};
