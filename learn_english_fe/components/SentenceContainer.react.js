import React from 'react';
import { Utilities } from '../utilities/Utilities.js'
import { Sentences } from './Sentences.react.js'

class SentenceContainer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      is_loading: true,
      content: (
        <div>
  			   Loading...
        </div>
      ),
    };
  }

  componentWillMount() {
    this.loadSentences();
  }

  loadSentences() {
  	var utilities = new Utilities();

  	// utilities.post(
  	// 	window.location.origin + '/index.php?c=sentences&m=saveSentence',
  	// 	{input: $("#new_sentence").val()},
  	// 	this.onSentenceSaved,
  	// 	this.onSaveSentenceError,
  	// );

    utilities.get(
  		'http://192.168.1.111:10002/ci/index.php?c=sentences&m=getSentences',
  		(response) => {
        this.setState({
          is_loading: false,
          content: (<Sentences data_source={response.sentences}/>),
        });
      },
      (error) => {
        alert('error');
        console.log(error);
      },
  	);
  }

  render() {
    return (
      <div>
        {this.state.content}
      </div>
    );
  }
}


export { SentenceContainer };
