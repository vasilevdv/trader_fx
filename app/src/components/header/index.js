import React from 'react';
import ReactDOM from 'react-dom';
import { NavLink } from 'react-router-dom';
import './style.scss';

const Header = () => (
	<div id="nav">
		<div className="inner_block">
			<div className="logo">Trader-fx.ru<small>Дневник трейдера</small></div>
			<ul>
				<li><NavLink activeClassName="current" to="/deals">Сделки</NavLink></li>
				<li><NavLink activeClassName="current" to="/settings">Настройки</NavLink></li>
				<li><NavLink activeClassName="current" to="/unloading">Выгрузка</NavLink></li>
				<li><NavLink activeClassName="current" to="/statistic">Статистика</NavLink></li>
				<li><a href="/?logout">Выйти</a></li>
			</ul>
		</div>
	</div>
)

export { Header };