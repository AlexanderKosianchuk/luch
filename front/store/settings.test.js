import expect from 'expect';
import configureStore from './configureStore';

const store = configureStore({}, (...args) => (args) => { return args; });

describe('store settings reducer', () => {
  it('set settings start should set pending true', () => {
  store.dispatch({ type: 'GET_USER_SETTINGS_START' });

  const state = store.getState();
  const settings = state.settings;

  expect(settings.pending).toEqual(true);
  });
});
