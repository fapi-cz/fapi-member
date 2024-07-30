import React, {useState} from 'react';
import DateTimeHelper from "Helpers/DateTimeHelper";
import Checkbox from "Components/Elements/Checkbox";

function UserSettingsInputs({level, membership, checked}) {
	const today = DateTimeHelper.getCurrentDateTime();
	const [showUntilInput, setShowUntilInput] = useState((membership?.until?.getDate() ?? null) !== null)

	if (!checked) {
		return null;
	}

	return (
		<div className='user-inputs'>
			<div className='user-input-container'>
				<label htmlFor={'registered-date-input-' + level.id}>Datum Registrace</label>
				<input
					className='fm-input registered-date-input'
					id={'registered-date-input-' + level.id}
					type='date'
					defaultValue={membership?.registered?.getDate() ?? today.getDate()}
				/>
			</div>
			<div className='user-input-container'>
				<label htmlFor={'registered-time-input-' + level.id}>Čas Registrace</label>
				<input
					className='fm-input registered-time-input'
					id={'registered-time-input-' + level.id}
					type='time'
					step='60'
					defaultValue={membership?.registered?.getTime() ?? today.getTime()}
				/>
			</div>
			<div
				className='user-input-container'
			>
				<label htmlFor={'until-date-input-' + level.id}>Datum Expirace</label>
				{showUntilInput
					? (
						<input
							className='fm-input intil-date-input'
							id={'until-date-input-' + level.id}
							type='date'
							defaultValue={membership?.until?.getDate()}
							onBlur={(e) => (setShowUntilInput(e.target.value !== ''))}
							autoFocus={true}
						/>
					) : (
						<div className='is-unlimited'>
							<Checkbox
								id={'is-unlimited-input-' + level.id}
								small={true}
								onClick={() => {setShowUntilInput(!showUntilInput)}}
								checked={!showUntilInput}
							/>
							<label
								className='clickable-option'
								htmlFor={'is-unlimited-input-' + level.id}
							>
								Bez expirace
							</label>
						</div>
					)
				}
			</div>
		</div>
	);
}

export default UserSettingsInputs;
