import React, { Fragment } from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';
import Rodal from 'rodal';
import PropTypes from 'prop-types';
import { Select } from './select.js';
import { Button } from './button.js';
import { Textarea } from './textarea.js';

import './style.scss';
import 'rodal/lib/rodal.css';

let request = {
	"deals": {
		"count": 39,
		"summa_deals": 49200.00,
		"turnover": 37313.75,
		"profit": 7667.00,
		"results": {
			"plus": 17,
			"minus": 20,
			"returned": 2,
			"sold": 0,
		},
		"deposit": 7668.00,
		"deals": [
			{
				"id": 1,
				"date": "2019-07-08",
				"time": "14:28:15",
				"type": 1, // 0 - режим онлайн, 1 - выгрузка сделок
				"broker": 2,
				"pair": "USDCAD",
				"percent": 76,
				"summa": 300,
				"currency": 1,
				"currency_symbol": "₽",
				"income": 528.00,
				"profit": -300.00,
				"result": 0, // -1 - возврат, 0 - минус, 1 - плюс, 2 - продажа
				"images": [
					"/uploads/0c226ec40f5b469b0013.jpg",
					"/uploads/10ecd961d93a76a00591.jpg",
				],
				"reason": "",
				"comment": "",
			}
		],
		"last_deal": {
			"id": 39,
			"broker": 2,
			"pair": "USDCAD",
			"percent": 76,
			"summa": 300,
			"currency": 1,
			"currency_symbol": "₽",
			"income": 528.00,
		}
	},

	"settings": {

		"data_info": {
			"user_id": 1,
			"deposit": 5000,
			"min_summa": 500,
			"percent": 82,
			"draw": 20,
			"currency": 1,
			"currency_symbol": "₽",
		},

		"stages": [
			{
				"id": 1,
				"percent_deposit": 10.00,
				"percent": 82,
				"factor": 1,
				"summa": 500,
				"summa_deals": 500,
				"percent_summa_deals": -10.00,
				"percent_profit": 8.20,
				"profit": 410,
			},{
				"id": 2,
				"percent_deposit": 15.00,
				"percent": 82,
				"factor": 1.5,
				"summa": 750,
				"summa_deals": 1250,
				"percent_summa_deals": -25.00,
				"percent_profit": 2.30,
				"profit": 115,
			},{
				"id": 3,
				"percent_deposit": 33.00,
				"percent": 82,
				"factor": 2.2,
				"summa": 1650,
				"summa_deals": 2900,
				"percent_summa_deals": -58.00,
				"percent_profit": 2.06,
				"profit": 103,
			},
		],

		"pairs": [
			{
				"id": 1,
				"name": "AUDCAD",
				"active": true,
				"default": false,
			},{
				"id": 2,
				"name": "AUDCHF",
				"active": false,
				"default": false,
			},{
				"id": 3,
				"name": "AUDJPY",
				"active": false,
				"default": false,
			},{
				"id": 34,
				"name": "AUDNZD",
				"active": false,
				"default": false,
			},
		],

		"brokers": [
			{
				"id": 1,
				"name": "24option",
				"active": false,
				"default": false,
			},
		],

		"deposits": [
			{
				"id": 1,
				"broker": 2,
				"summa": 1900,
				"summa_bonus": 0,
				"date": "2019-07-30",
				"time": "14:42:07",
				"credit": 0,
			},{
				"id": 2,
				"broker": 2,
				"summa": 1750,
				"summa_bonus": 0,
				"date": "2019-07-30",
				"time": "17:17:08",
				"credit": 0,
			},
		],

		"deposit": {
			"summa": 0.19,
			"date": "2019-08-14",
		}

	},

	"unloading": {
		"brokers": [
			{
				"id": 2,
				"name": "Binomo",
			},{
				"id": 3,
				"name": "Finmax",
			},{
				"id": 9,
				"name": "Intrade.Bar",
			},{
				"id": 5,
				"name": "Olymp Trade",
			},{
				"id": 6,
				"name": "Pocket Option",
			},
		]
	}
};

const textareaStyle = {
	height: '250px',
	textAlign: 'left',
};

const selectStyle = {
	width: '100%',
	display: 'none'
}

const API_URL = 'http://trader';

class Unloading extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			brokers: [],
			data: {
				broker: 9,
				deals: '',
			},
			dialog: {
				visible: false,
				title: '',
				text: '',
			},
			currentValue: '',
		};
		const full_datas = localStorage.getItem('full_datas');
		this.handleSubmit = this.handleSubmit.bind(this);
		this.handleInput = this.handleInput.bind(this);

		/*
		*  Если есть данные в localStorage используем их
		*/
		if (full_datas) {
			request = JSON.parse(full_datas);
		}	else {
			localStorage.setItem('full_datas',JSON.stringify(request));
		}
	}

	showDialog(title, text) {
    this.setState({
    	dialog: {
	  		visible: true,
	  		title: title,
	  		text: text
	  	}
  	});
  }

  hideDialog() {
    this.setState({
    	dialog: {
	    	visible: false 
	    }
   	});
  }

	componentDidMount() {
		const brokers = request.unloading.brokers;
		let brokerValues = [];

		// Формируем массив объектов для модуля Select
		if (request.unloading.brokers) {
			request.unloading.brokers.forEach((broker, index) => {
				let valSingleBroker = {};
				valSingleBroker.value = {};
				valSingleBroker.value.value = broker.id;
				valSingleBroker.value.name = broker.name;
				valSingleBroker.label = broker.name;
				brokerValues.push(valSingleBroker);
			});
		}
		this.setState({brokers});

		brokers.find(option => {
			if (option.id == this.state.data.broker) {
				this.setState({
					currentValue: option.name
				});
			}
		});
	};

	// Отправка сделок на обработку
	handleSubmit(e) {
		e.preventDefault();
		let data = this.state.data;
		console.log(data);
		axios.post(`${API_URL}/api/unloading/`, data )
		.then((response) => {
			console.log(response.data);
			if (response.data.success) {
				this.showDialog(response.data.title, response.data.text);
			}
		})
		.catch(e => {
			console.log(e);
		})
	};

	// Обработка событий select и input
	handleInput(e) {
		let value = e.target.tagName == 'SELECT' ? parseInt(e.target.value) : e.target.value;
		let name = e.target.name;
		this.setState( prevState => {
		  return { 
		    data: {
		    	...prevState.data, [name]: value
		    }
		  }
		}) // , () => console.log(this.state)
	};

	// Обработка кастомного селекта
	onChangeSelect(e) {
    alert('adss');
  }

	render() {
		// let searchResult = nameValue.name;

		return (
			<Fragment>
				<div id="calc_main" className="not_deals">
						<div className="title">Выгрузка сделок</div>
						<form onSubmit={this.handleSubmit} className="form_reload">
							<div className="reload_block">
								<table className="main">
									<tbody>
										<tr>
											<th className="fone">Выберите вашего брокера из списка и вставьте скопированные сделки</th>
										</tr>
										<tr>
											<td>
												<div id="use_pairs">

													<Select 
														name={'broker'}
														style={selectStyle}
														options={this.state.brokers}
														value={this.state.data.broker}
														placeholder={'Выберите брокера'}
														nameValue={this.state.currentValue}
														handleChange={this.onChangeSelect}
													/>

													<Textarea 
														name={'deals'}
														style={textareaStyle}
														placeholder={'Введите свои сделки сюда'}
														value={this.state.data.deals}
														handleChange={this.handleInput}
													/>

												</div>
											</td>
										</tr>
										<tr>
											<td>

												<Button 
													name={'Сохранить'}
													className={'button-submit'}
													action={this.handleSubmit}
												/>
											
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</form>
					</div>
					<Rodal visible={this.state.dialog.visible} onClose={this.hideDialog.bind(this)}>
            <div className="title" dangerouslySetInnerHTML={{__html: this.state.dialog.title}}/>
            <div className="body" dangerouslySetInnerHTML={{__html: this.state.dialog.text}}/>
          </Rodal>
			</Fragment>
		);
	}
};

export { Unloading }