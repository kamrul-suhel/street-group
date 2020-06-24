import React, {Fragment, useState} from 'react';
import ReactDOM from 'react-dom';
import Grid from "@material-ui/core/Grid";
import Header from "./Header";
import Container from "@material-ui/core/Container";
import Typography from "@material-ui/core/Typography";
import Persons from "./Persons";

function UploadCSV() {

    const [persons, setPersons] = useState([])

    const handleUpdatePersons = (data) => {
        setPersons(pervState => [...data.persons])
    }

    return (
        <Fragment>
            <Header updatePersons={(persons) => handleUpdatePersons(persons)}/>

            {persons.length > 0 && <Container>
                <Grid container style={{marginTop: 20}}>
                    <Grid item xs={12}>
                        <Typography variant="h5">Uploaded person</Typography>
                    </Grid>

                    <Grid item xs={12}>
                        <Persons persons={persons}/>
                    </Grid>
                </Grid>
            </Container>}
        </Fragment>
    );
}

export default UploadCSV;

if (document.getElementById('streetGroup')) {
    ReactDOM.render(<UploadCSV />, document.getElementById('streetGroup'));
}
