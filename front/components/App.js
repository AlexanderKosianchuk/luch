import React from 'react'
import { Route } from 'react-router';
import { UserAuthWrapper } from 'redux-auth-wrapper';
import { ConnectedRouter, routerActions } from 'react-router-redux';

import Results from 'components/results/Results';
import Flights from 'components/flights/Flights';
import UserOptions from 'components/user-options/UserOptions';
import UserLogin from 'components/user-login/UserLogin';
import FlightsSearch from 'components/flights-search/FlightsSearch';
import Calibrations from 'components/calibrations/Calibrations';
import Users from 'components/users/Users';
import FlightEvents from 'components/flight-events/FlightEvents';
import FlightTemplates from 'components/flight-templates/FlightTemplates';
import FlightParams from 'components/flight-params/FlightParams';
import UploadingPreview from 'components/uploading-preview/UploadingPreview';
import Chart from 'components/chart/Chart';

// Redirects to /login by default
const UserIsAuthenticated = UserAuthWrapper({
    authSelector: state => state.user, // how to get the user state
    redirectAction: routerActions.replace, // the redux action to dispatch for redirect
    wrapperDisplayName: 'UserIsAuthenticated' // a nice name for this auth check
});

const App = ({ history }) => (
    <ConnectedRouter history={ history }>
          <div>
            <Route exact path='/login' component={ UserLogin } />
            <Route exact path='/' component={ UserIsAuthenticated(Flights) } />
            <Route exact path='/flights/:viewType' component={ UserIsAuthenticated(Flights) } />
            <Route exact path='/user-options' component={ UserIsAuthenticated(UserOptions) } />
            <Route exact path='/flights-search' component={ UserIsAuthenticated(FlightsSearch) } />
            <Route exact path='/results' component={ UserIsAuthenticated(Results) } />
            <Route exact path='/calibrations' component={ UserIsAuthenticated(Calibrations) } />
            <Route exact path='/users' component={ UserIsAuthenticated(Users) } />
            <Route path='/flight-events/:id' component={ UserIsAuthenticated(FlightEvents) } />
            <Route path='/flight-templates/:id' component={ UserIsAuthenticated(FlightTemplates) } />
            <Route path='/flight-params/:id' component={ UserIsAuthenticated(FlightParams) } />
            <Route path='/uploading/:uploadingUid/fdr-id/:fdrId' /*calibration-id/:calibrationId possible*/
                component={ UserIsAuthenticated(UploadingPreview) }
            />
            <Route path='/chart/flight-id/:id/template-name/:templateName/from-frame/:fromFrame/to-frame/:toFrame'
                component={ UserIsAuthenticated(Chart) }
            />
          </div>
    </ConnectedRouter>
);

export default App;
