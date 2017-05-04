import './settlements-report-row.sass';

import React from 'react';

export default function SettlementsReportRow (props) {
    return (
        <div className="settlements-report-row row">
            <div className="settlements-report-row__col-title">
                { props.title ? props.title : <b> { props.i18n.settlementsReportRowTitle } </b> }
            </div>
            <div className="settlements-report-row__col-value">
                { props.count ? props.count : <b> { props.i18n.settlementsReportRowCount } </b> }
            </div>
            <div className="settlements-report-row__col-value">
                { props.min ? props.min : <b> { props.i18n.settlementsReportRowMin } </b> }
            </div>
            <div className="settlements-report-row__col-value">
                { props.avg ? props.avg : <b> { props.i18n.settlementsReportRowAvg } </b> }
            </div>
            <div className="settlements-report-row__col-value">
                { props.sum ? props.sum : <b> { props.i18n.settlementsReportRowSum } </b> }
            </div>
            <div className="settlements-report-row__col-value">
                { props.max ? props.max : <b> { props.i18n.settlementsReportRowMax } </b> }
            </div>
        </div>
    );
}
