/*jslint browser: true*/

'use strict';

import 'jquery';
import facade from 'facade';

import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { AppContainer } from 'react-hot-loader';
import { createBrowserHistory } from 'history';
import { routerMiddleware } from 'react-router-redux';
import { setLocale, loadTranslations } from 'react-redux-i18n';

import App from 'containers/App'

import configureStore from 'store/configureStore';

import translationsEn from 'translations/translationsEn';
import translationsEs from 'translations/translationsEs';
import translationsRu from 'translations/translationsRu';

const translationsObject = {...translationsEn, ...translationsEs, ...translationsRu};
const history = createBrowserHistory({ queryKey: false });
const routerMiddlewareInstance = routerMiddleware(history);
const store = configureStore({}, routerMiddlewareInstance);

store.dispatch(loadTranslations(translationsObject));
store.dispatch(setLocale('ru'));

facade(store);

if (($('html').attr('login') !== '')
    && ($('html').attr('lang') !== '')
) {
    let login = $('html').attr('login');
    let role = $('html').attr('role');
    let lang = $('html').attr('lang');
    store.dispatch({
        type: 'USER_LOGGED_IN',
        payload: {
            login: login,
            role: role,
            lang: lang
        }
    });
    store.dispatch(setLocale(lang.toLowerCase()));
}

const render = () => {
    ReactDOM.render(
        <AppContainer>
            <Provider store={ store }>
                <App history={ history } />
            </Provider>
        </AppContainer>,
        document.getElementById('root')
    );
}

render();
