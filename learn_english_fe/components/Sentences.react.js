import React from 'react';
import { Grid, Row, Col } from 'react-bootstrap';
import { Sentence } from './Sentence.react.js'

class Sentences extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  _renderSentences() {
    let sentences = this.props.data_source;

    sentences = sentences.filter(sentence => sentence != null)
      .map((sentence, idx) => {
        return (<Sentence key={idx} data_source={sentence}/>);
      });

    return (
      <div>
        {sentences}
      </div>
    );
  }

  render() {
    return (
      <div>
        {this._renderSentences()}
      </div>
    );
  }
}

Sentences.propTypes = {
  data_source: React.PropTypes.array.isRequired,
};

export { Sentences };
