import React from 'react';
import { Grid, Row, Col } from 'react-bootstrap';
import { ButtonToolbar, ButtonGroup, Button } from 'react-bootstrap';
import { DropdownButton, MenuItem } from 'react-bootstrap';


import { SentenceContainer } from './components/SentenceContainer.react.js'
import { SentenceInput } from './components/SentenceInput.react.js'

class App extends React.Component {
  render() {
    return (
      <Grid>
        <Row className="show-border">
          <ButtonToolbar>
            <ButtonGroup>
              <Button>1</Button>
              <Button>2</Button>
              <Button>3</Button>
              <Button>4</Button>
            </ButtonGroup>

            <ButtonGroup>
              <Button>5</Button>
              <Button>6</Button>
              <Button>7</Button>
            </ButtonGroup>

            <ButtonGroup>
              <Button>8</Button>
            </ButtonGroup>

            <DropdownButton bsStyle={'info'} title={'drop down test'} key={1} id={`dropdown-basic-3`}>
              <MenuItem eventKey="1">Action</MenuItem>
              <MenuItem eventKey="2">Another action</MenuItem>
              <MenuItem eventKey="3" active>Active Item</MenuItem>
              <MenuItem divider />
              <MenuItem eventKey="4">Separated link</MenuItem>
            </DropdownButton>

          </ButtonToolbar>
        </Row>
        <Row className="show-border">
          <Col lg={20} className="show-border">
            <SentenceContainer></SentenceContainer>
          </Col>
        </Row>
        <Row className="show-border">
          <Col lg={20} className="show-border">
            <SentenceInput></SentenceInput>
          </Col>
        </Row>
      </Grid>
    );
  }
}


export default App;
