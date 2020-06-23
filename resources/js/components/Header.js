import React, {useRef} from 'react';
import { makeStyles } from '@material-ui/core/styles';
import AppBar from '@material-ui/core/AppBar';
import Toolbar from '@material-ui/core/Toolbar';
import Typography from '@material-ui/core/Typography';
import Button from '@material-ui/core/Button';
import IconButton from '@material-ui/core/IconButton';
import MenuIcon from '@material-ui/icons/Menu';

const useStyles = makeStyles((theme) => ({
    root: {
        flexGrow: 1,
    },
    menuButton: {
        marginRight: theme.spacing(2),
    },
    title: {
        flexGrow: 1,
    },
}));

const Header = (props) => {
    const {updatePersons} = props

    const classes = useStyles();
    const csvUploadRef = useRef()
    const handelUploadCsv = async () => {
        const file = csvUploadRef.current.files[0]
        if(file === 'undefined'){
            // error
            return
        }
        let uploadForm = new FormData()
        uploadForm.append('file', file)

        const response = await axios.post('/api/v1/uploadcsv', uploadForm)

        if(response.data.success){
            console.log('respon is:', response.data)
            updatePersons(response.data.persons)
        }else{
            // error
        }
    }

    return (
        <div className={classes.root}>
            <AppBar position="static">
                <Toolbar>
                    <IconButton edge="start" className={classes.menuButton} color="inherit" aria-label="menu">
                        <MenuIcon />
                    </IconButton>
                    <Typography variant="h6" className={classes.title}>
                        Upload csv
                    </Typography>
                    <input type="file"
                           accept="text/csv"
                            onChange={handelUploadCsv}
                           ref={csvUploadRef}
                        style={{display: 'none'}}/>
                    <Button color="inherit" onClick={() => csvUploadRef.current.click()}>Upload</Button>
                </Toolbar>
            </AppBar>
        </div>
    );
}

export default Header
