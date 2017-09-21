import { createStore, applyMiddleware } from 'redux';
import thunk from 'redux-thunk';
import { composeWithDevTools } from 'redux-devtools-extension';
import { syncTranslationWithStore } from 'react-redux-i18n';

import rootReducer from 'reducers/rootReducer';

export default function configureStore(
    initialState,
    routerMiddleware
) {

    let enhancer = applyMiddleware(thunk, routerMiddleware);

    if (NODE_ENV === 'dev') {
        enhancer = composeWithDevTools(applyMiddleware(thunk, routerMiddleware));
    }

    const store = createStore(
        rootReducer,
        initialState,
        enhancer
    );

    if (NODE_ENV !== 'test') {
        syncTranslationWithStore(store);
    }

    if (module.hot && (NODE_ENV === 'dev')) {
        module.hot.accept('components/App', () => { render() });
    }

    return store;
}
