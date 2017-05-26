import { createStore, applyMiddleware } from 'redux';
import thunk from 'redux-thunk';
import { composeWithDevTools } from 'redux-devtools-extension';
import { syncTranslationWithStore} from 'react-redux-i18n';

import rootReducer from 'reducers/rootReducer';

export default function configureStore(initialState, routerMiddleware) {
    const store = createStore(
        rootReducer,
        initialState,
        composeWithDevTools(applyMiddleware(thunk, routerMiddleware))
    );

    syncTranslationWithStore(store);

    if (module.hot) {
        module.hot.accept('components/App', () => { render() });
    }

    return store;
}
