import React from 'react';
import ReactDOM from 'react-dom';

const Textarea = (props) => {
	return (
		<textarea 
			style={props.style} 
			name={props.name} 
			value={props.value} 
			placeholder={props.placeholder} 
			onChange={props.handleChange}
		/>
	);
}

export { Textarea };