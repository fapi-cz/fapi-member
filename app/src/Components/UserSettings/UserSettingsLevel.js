import React, { useState} from 'react';
import Checkbox from "Components/Elements/Checkbox";
import UserSettingsInputs from "Components/UserSettings/UserSettingsInputs";

function UserSettingsLevel({level, membership, userId, sectionRegistrationDate}) {
	const [checked, setChecked] = useState(membership !== null);

    return (
		<div className='user-level'>
			<Checkbox
				key={level.id}
				id={'level-checkbox-' + level.id}
				className={'level-checkbox'}
				checked={checked}
				onClick={(e) => {setChecked(e.target.checked)}}
			/>
			<label
				className='user-level-name clickable-option'
				htmlFor={'level-checkbox-' + level.id}
			>
				{level.name}
			</label>
			<UserSettingsInputs
				level={level}
				membership={membership}
				userId={userId}
				checked={checked}
				sectionRegistrationDate={sectionRegistrationDate}
			/>
		</div>
	);
}

export default UserSettingsLevel;
