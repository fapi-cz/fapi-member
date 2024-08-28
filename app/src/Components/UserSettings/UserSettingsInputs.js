import React, {useEffect, useState} from 'react';
import DateTimeHelper from "Helpers/DateTimeHelper";
import Checkbox from "Components/Elements/Checkbox";
import MembershipClient from "Clients/MembershipClient";
import Loading from "Components/Elements/Loading";

function UserSettingsInputs({level, membership, checked, sectionRegistrationDate = null, setSectionRegistrationDate = () => {}}) {
	const today = DateTimeHelper.getCurrentDateTime();
	const [showUntilInput, setShowUntilInput] = useState((membership?.until?.getDate() ?? null) !== null)
	const [unlockDate, setUnlockDate] = useState(null)
	const membershipClient = new MembershipClient();

	if(sectionRegistrationDate === null) {
		setSectionRegistrationDate(today.getDate());
	}

	useEffect(() => {
		const loadData = async () => {
			var newUnlockDate = await membershipClient.getUnlockDate(
				level.id,
				new URLSearchParams(window.location.search).get('user_id'),
				sectionRegistrationDate,
			)
			setUnlockDate(newUnlockDate);
		}

		if (
			level.parentId !== null
			&& level.unlockType !== 'disallow'
			&& level.unlockType !== null
		) {
			setUnlockDate(null);
			loadData();
		}

	}, [sectionRegistrationDate]);


	if (!checked) {
		if (level.unlockType === 'disallow' || level.unlockType === null || level.parentId === null) {
			return null;
		}

		if (unlockDate === null) {
			return <Loading/>;
		}

		return (
			<div className='user-inputs'>
				<div className='user-settings-automatic-unlocking-overlay'>
					{'Úroveň bude odemčena ' + unlockDate?.getDateCzech() + ' v ' + unlockDate?.getHoursAndMinutes()}
				</div>
			</div>
		);
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
					onChange={(e) => {if (level.parentId === null) {
						setSectionRegistrationDate(e.target.value);
					}}}
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
