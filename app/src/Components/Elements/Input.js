import React from 'react';

function Input({id, type, label, required = false, big = true}) {

    return (
		<div style={{width: 'fit-content'}}>
			<label>{label}</label>
			{required
				? (<label style={{color: 'red'}}> *</label>)
				: null
			}
			<input
				className={'fm-input ' + (big ? 'big' : '')}
				type={type}
				placeholder={label}
				name={id}
				id={id}
			/>
		</div>
    );
}

export default Input;
