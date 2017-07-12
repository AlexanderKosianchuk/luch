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
import UsersTable from 'containers/users-table/UsersTable';
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

const App = ({ history }) => (
    <ConnectedRouter history={ history }>
          <div>
            <Route path='/login' component={ Login } />
            <Route exact path='/' component={ UserIsAuthenticated(FlightsTree) } />
            <Route path='/flights/tree' component={ UserIsAuthenticated(FlightsTree) } />
            <Route path='/flights/table' component={ UserIsAuthenticated(FlightsTable) } />
            <Route path='/user-options' component={ UserIsAuthenticated(Settings) } />
            <Route path='/results' component={ UserIsAuthenticated(Results) } />
            <Route path='/calibrations' component={ UserIsAuthenticated(Calibrations) } />
            <Route path='/users' component={ UserIsAuthenticated(UsersTable) } />
            <Route path='/flight-events/:flightId' component={ UserIsAuthenticated(FlightEvents) } />
            <Route exact path='/flight-templates/:flightId' component={ UserIsAuthenticated(FlightTemplates) } />
            <Route path='/flight-template-edit/create/flight-id/:flightId/' component={ UserIsAuthenticated(FlightTemplateCreate) } />
            <Route path='/flight-template-edit/update/flight-id/:flightId/template-name/:templateName' component={ UserIsAuthenticated(FlightTemplateUpdate) } />
            <Route path='/flight-params/:id' component={ UserIsAuthenticated(FlightParams) } />
            <Route path='/uploading/:uploadingUid/fdr-id/:fdrId' /*calibration-id/:calibrationId possible*/
                component={ UserIsAuthenticated(UploadingPreview) }
            />
            <Route path='/chart/flight-id/:flightId/template-name/:templateName/from-frame/:fromFrame/to-frame/:toFrame'
                component={ UserIsAuthenticated(Chart) }
            />
          </div>
    </ConnectedRouter>
);

export default App;
