var React = require('react');

class ResultSettlementsFilter extends React.Component {
    constructor(props) {
        super(props);

        this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleSubmit(event) {
        alert('A name was submitted: ');
        event.preventDefault();
        //event.stopPropagation();
    }

    render() {
        return <form className="result-settlements-filter" onSubmit={this.handleSubmit}>
                <div className="form-group result-settlements-filter__header">
                    <label htmlFor="result-settlements-filter-fdr-type">Name</label>
                    <input type="text" className="form-control"
                        id="result-settlements-filter-fdr-type" placeholder="FDR type" />
                </div>

                <div className="form-group result-settlements-filter__row">
                    <input type="submit" className="btn btn-default"
                        id="result-settlements-filter__apply" value="Apply" />
                </div>
            </form>;
    }
}

module.exports = ResultSettlementsFilter;
