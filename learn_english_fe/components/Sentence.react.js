import React from 'react';
import { Grid, Row, Col } from 'react-bootstrap';

class Sentence extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  _renderSentence() {
    return (
      <div className="show-border">
        {this.props.data_source}
      </div>
    );
  }

  render() {
    return (
      <div>
        {this._renderSentence()}
      </div>
    );
  }
}

Sentence.propTypes = {
  data_source: React.PropTypes.string.isRequired,
};

export { Sentence };
