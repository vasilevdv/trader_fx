import React from 'react';
import ReactDOM from 'react-dom';

const Button = (props) => {
	return (
		<button 
			style={props.style}
			className={props.className}
			onClick={props.action}>
				{props.name}
		</button>
	);
}

export { Button };