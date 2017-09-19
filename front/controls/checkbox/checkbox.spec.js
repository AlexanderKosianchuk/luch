import React from 'react';
import { shallow } from 'enzyme';
import expect from 'expect';
import expectJSX from 'expect-jsx';

expect.extend(expectJSX);

import Checkbox from './Checkbox';

describe('controls/Checkbox', () => {
    it('should render Checkbox control', () => {
      const wrapper = shallow(<Checkbox />);
      expect(wrapper.find('input')).toHaveLength(1);;
    });
})
