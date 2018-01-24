import './item.sass'

import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Collapse } from 'react-collapse';
import { I18n } from 'react-redux-i18n';

import ItemControls from 'components/flight-templates/item-controls/ItemControls';
import ItemCheckbox from 'components/flight-templates/item-checkbox/ItemCheckbox';
import ItemDescription from 'components/flight-templates/item-description/ItemDescription';

export default class Item extends React.Component {
  constructor(props) {
    super(props);
    this.state = { isOpened: false };
  }

  checkServicePurpose(servicePurpose) {
    if (!servicePurpose) {
      return '';
    }

    let glyphicon = '';
    if (servicePurpose.isDefault) {
      glyphicon = 'glyphicon-home';
    } else if (servicePurpose.isEvents) {
      glyphicon = 'glyphicon-flag';
    } else if (servicePurpose.isLast) {
      glyphicon = 'glyphicon-retweet';
    }

    return <span className={ 'glyphicon flight-templates-item__glyphicon ' + glyphicon }></span>;
  }

  getCheckstate(servicePurpose) {
    if (servicePurpose && servicePurpose.isDefault) {
      return 'checked';
    }

    return '';
  }

  render () {
    return (
      <div className='flight-templates-item'>
        <div className='row'>
          <div className='col-sm-1 flight-templates-item__service-purpose-col'>
            { this.checkServicePurpose(this.props.servicePurpose) }
          </div>

          <div className='col-sm-1'>
            <ItemCheckbox
              id={ this.props.id }
              name={ this.props.name }
              checkstate={ this.getCheckstate(this.props.servicePurpose) }
            />
          </div>

          <div className='col-sm-2'>
            <span className='flight-templates-item__title'>
              { I18n.t('flightTemplates.item.'
                + ((this.props.servicePurpose && this.props.servicePurpose.isDefault)
                  ? 'default'
                  : this.props.name
                )
              ) }
            </span>
          </div>

          <div className='col-sm-2'>
            <ItemControls
              servicePurpose={ this.props.servicePurpose }
              flightId={ this.props.flightId }
              templateId={ this.props.id }
            />
          </div>

          <div className='col-sm-5'>
            <span className='flight-templates-item__body'>{ this.props.paramCodes }</span>
          </div>

          <div className='col-sm-1'>
            <span className={ 'glyphicon flight-templates-item__glyphicon '
                + (this.state.isOpened
                ? 'glyphicon-chevron-up'
                : 'glyphicon-chevron-down')
              }
              onClick={() => { this.setState({isOpened: !this.state.isOpened}) }}
            >
            </span>
          </div>

          <div className='row'>
            <div className='col-sm-12'>
              <Collapse isOpened={ this.state.isOpened }>
                <ItemDescription params={ this.props.params }/>
              </Collapse>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
