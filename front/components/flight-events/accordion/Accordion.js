import 'rc-collapse/assets/index.css';

import React from 'react';
import { I18n } from 'react-redux-i18n';
import Collapse, { Panel } from 'rc-collapse';

import ContentHeader from 'components/flight-events/content-header/ContentHeader';
import Content from 'components/flight-events/content/Content';

export default function Accordion (props) {
    let codes = Object.keys(props.items);

    return <Collapse accordion={ false } defaultActiveKey={ codes }>
        {
            codes.map((code) => {
                return (
                    <Panel header={ I18n.t('flightEvents.collapse.eventCodeMask' + code) + ' - ' + code }
                        key={ code }
                        className='container-fluid'
                    >
                        <ContentHeader />
                        <Content rows={ props.items[code] } />
                    </Panel>
                );
            })
        }
    </Collapse>;
}
