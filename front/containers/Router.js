import React from 'react'
import { Route } from 'react-router';
import { UserAuthWrapper } from 'redux-auth-wrapper';
import { ConnectedRouter, routerActions } from 'react-router-redux';

import Login from 'containers/login/Login';
import Results from 'containers/results/Results';
import FlightsTree from 'containers/flights-tree/FlightsTree';
import FlightsTable from 'containers/flights-table/FlightsTable';
import Settings from 'containers/settings/Settings';
import Calibrations from 'containers/calibrations/Calibrations';
import CalibrationForm from 'containers/calibration-form/CalibrationForm';
import RealTimeCalibration from 'containers/realtime-calibration/RealTimeCalibration';
import UsersTable from 'containers/users-table/UsersTable';
import UserActivity from 'containers/user-activity/UserActivity';
import UserForm from 'containers/user-form/UserForm';
import FlightEvents from 'containers/flight-events/FlightEvents';
import FlightTemplates from 'containers/flight-templates/FlightTemplates';
import FlightTemplateCreate from 'containers/flight-template-create/FlightTemplateCreate';
import FlightTemplateUpdate from 'containers/flight-template-update/FlightTemplateUpdate';
import FlightParams from 'containers/flight-params/FlightParams';
import UploadingPreview from 'containers/uploading-preview/UploadingPreview';
import Chart from 'containers/chart/Chart';

// Redirects to /login by default
const UserIsAuthenticated = UserAuthWrapper({
  authSelector: state => state.user, // how to get the user state
  redirectAction: routerActions.replace, // the redux action to dispatch for redirect
  wrapperDisplayName: 'UserIsAuthenticated' // a nice name for this auth check
});

export default function Router ({ history }) {
  return (<ConnectedRouter history={ history }>
      <div>
      <Route path='/login' component={ Login } />
      <Route exact path='/' component={ UserIsAuthenticated(FlightsTree) } />
      <Route path='/flights/tree' component={ UserIsAuthenticated(FlightsTree) } />
      <Route path='/flights/table' component={ UserIsAuthenticated(FlightsTable) } />
      <Route path='/user-settings' component={ UserIsAuthenticated(Settings) } />
      <Route path='/results' component={ UserIsAuthenticated(Results) } />

      <Route exact path='/calibrations/fdr-id/:fdrId/page/:page/page-size/:pageSize' component={ UserIsAuthenticated(Calibrations) } />
      <Route exact path='/calibrations/fdr-id/:fdrId/page/:page' component={ UserIsAuthenticated(Calibrations) } />
      <Route exact path='/calibrations/page/:page/page-size/:pageSize' component={ UserIsAuthenticated(Calibrations) } />
      <Route exact path='/calibrations/fdr-id/:fdrId/page/:page' component={ UserIsAuthenticated(Calibrations) } />
      <Route exact path='/calibrations/fdr-id/:fdrId' component={ UserIsAuthenticated(Calibrations) } />
      <Route exact path='/calibrations' component={ UserIsAuthenticated(Calibrations) } />

      <Route exact path='/calibration/update/:calibrationId' component={ UserIsAuthenticated(CalibrationForm) } />
      <Route exact path='/calibration/create/fdr-id/:fdrId' component={ UserIsAuthenticated(CalibrationForm) } />
      <Route exact path='/realtime-calibration/fdr-id/:fdrId/params-sourse/:paramsSource/fdr-template-id/:fdrTemplateId' component={ UserIsAuthenticated(RealTimeCalibration) } />
      <Route exact path='/realtime-calibration/fdr-id/:fdrId/params-sourse/:paramsSource/' component={ UserIsAuthenticated(RealTimeCalibration) } />
      <Route exact path='/realtime-calibration/fdr-id/:fdrId/' component={ UserIsAuthenticated(RealTimeCalibration) } />
      <Route exact path='/realtime-calibration/' component={ UserIsAuthenticated(RealTimeCalibration) } />

      <Route exact path='/users' component={ UserIsAuthenticated(UsersTable) } />
      <Route exact path='/user/create/' component={ UserIsAuthenticated(UserForm) } />
      <Route exact path='/user/:type/:userId' component={ UserIsAuthenticated(UserForm) } />
      <Route exact path='/user-activity/:userId/page/:page/page-size/:pageSize' component={ UserIsAuthenticated(UserActivity) } />
      <Route exact path='/user-activity/:userId/page/:page' component={ UserIsAuthenticated(UserActivity) } />
      <Route exact path='/user-activity/:userId' component={ UserIsAuthenticated(UserActivity) } />

      <Route path='/flight-events/:flightId' component={ UserIsAuthenticated(FlightEvents) } />
      <Route exact path='/flight-templates/:flightId' component={ UserIsAuthenticated(FlightTemplates) } />
      <Route path='/flight-template-edit/create/flight-id/:flightId/' component={ UserIsAuthenticated(FlightTemplateCreate) } />
      <Route path='/flight-template-edit/update/flight-id/:flightId/template-id/:templateId' component={ UserIsAuthenticated(FlightTemplateUpdate) } />
      <Route path='/flight-params/:id' component={ UserIsAuthenticated(FlightParams) } />
      <Route exact path='/uploading/:uploadingUid/fdr-id/:fdrId' component={ UserIsAuthenticated(UploadingPreview) }/>
      <Route path='/uploading/:uploadingUid/fdr-id/:fdrId/calibration-id/:calibrationId' component={ UserIsAuthenticated(UploadingPreview) } />
      <Route path='/chart/flight-id/:flightId/template-id/:templateId/from-frame/:fromFrame/to-frame/:toFrame' component={ UserIsAuthenticated(Chart) } />
      </div>
    </ConnectedRouter>
  )
};
