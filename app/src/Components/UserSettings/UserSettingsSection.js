import React, {useState} from 'react';
import Checkbox from "Components/Elements/Checkbox";
import UserSettingsLevel from "Components/UserSettings/UserSettingsLevel";
import UserSettingsInputs from "Components/UserSettings/UserSettingsInputs";
import DateTimeHelper from "Helpers/DateTimeHelper";

function UserSettingsSection({section, membership, levelItems}) {

	const [checked, setChecked] = useState(membership !== null);
	const [registrationDate, setRegistrationDate] = useState(null);
	const newMembership = {
		registered: DateTimeHelper.getCurrentDateTime(),
		until: null,
		isUnlimited: true,
	}

    return (
		<div className='user-section'>
			<div className='user-section-settings'>
				<Checkbox
					key={section.id}
					id={'level-checkbox-' + section.id}
					className={'section-checkbox'}
					checked={checked}
					onClick={(e) => {setChecked(e.target.checked)}}
				/>
				<label
					className='user-section-name clickable-option'
					htmlFor={'level-checkbox-' + section.id}
				>
					{section.name}
				</label>
				<UserSettingsInputs
					level={section}
					membership={membership}
					checked={checked}
					setSectionRegistrationDate={setRegistrationDate}
					sectionRegistrationDate={registrationDate}
				/>
			</div>
			{ checked
				? (
					<div className='user-levels'>
						{levelItems.map((levelItem) => (
							<UserSettingsLevel
								key={levelItem.level.id}
								level={levelItem.level}
								membership={levelItem.membership}
								sectionRegistrationDate={registrationDate}
							/>
						))}
					</div>
				) : (null)
			}
		</div>
	);
}

export default UserSettingsSection;
