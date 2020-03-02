import React, {Fragment} from 'react';
import ReactDOM from 'react-dom';
import Axios from 'axios';
import PropTypes from 'prop-types';
import { App } from './components';
import { Route, BrowserRouter, Link } from 'react-router-dom';
import './index.scss';

ReactDOM.render(
	<App/>, 
	document.getElementById('page')
);