import React from 'react';
import { InputGroup, FormControl } from 'react-bootstrap';
import { Button } from 'react-bootstrap';
import { Utilities } from '../utilities/Utilities.js'

class SentenceInput extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    return (
      <InputGroup>
        <FormControl id="new_sentence" type="text" />
        <InputGroup.Button>
          <Button onClick={this.handleSaveBtnClicked}>Save</Button>
        </InputGroup.Button>
      </InputGroup>
    );
  }

  handleSaveBtnClicked() {
    var utilities = new Utilities();

    utilities.post(
  		'http://192.168.1.111:10002/ci/index.php?c=sentences&m=saveSentence',
      {
        input: $("#new_sentence").val(),
      },
  		(response) => {
        if (response.error_code == 0) {
          alert('saved!');
        } else {
          alert(response);
        }
      },
      (error) => {
        alert('error');
        console.log(error);
      },
  	);
  }
}

SentenceInput.propTypes = {
};

export { SentenceInput };
