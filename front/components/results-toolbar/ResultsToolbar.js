require('./results-toolbar.sass');

const React = require('react');

class ResultsToolbar extends React.Component {
    render() {
        return <div className="results-toolbar">
            {this.props.i18n.options}
        </div>;
    }
}

module.exports = ResultsToolbar;
