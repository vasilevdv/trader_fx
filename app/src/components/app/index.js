import React, { Fragment } from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter, Route } from 'react-router-dom';
import './style.css';
import { Header, Footer, Deals, Settings, Unloading, Statistic } from '../../components';

class App extends React.Component {
	constructor(props) {
		super(props);
		this.state = {

		}
	}

	render() {
		return (
			<Fragment>
				<BrowserRouter>
					<Header/>
					<Route exact path="/deals" component={Deals} />
					<Route path="/settings" component={Settings} />
					<Route path="/unloading" component={Unloading} />
					<Route path="/statistic" component={Statistic} />
					<Footer/>
				</BrowserRouter>
			</Fragment>
		)
	}
}

export { App }