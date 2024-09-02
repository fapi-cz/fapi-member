import 'Styles/userSettings.css';

import React, {useEffect, useState} from 'react';

import UserSettingsSection from "Components/UserSettings/UserSettingsSection";
import MemberSectionClient from "Clients/MemberSectionClient";
import MembershipClient from "Clients/MembershipClient";
import {InputHelper} from "Helpers/InputHelper";
import DateTimeHelper from "Helpers/DateTimeHelper";
import Loading from "Components/Elements/Loading";
import SubmitButton from "Components/Elements/SubmitButton";

function UserMembershipsForm({userId, form = null}) {
	const [sectionsLoading, setSectionsLoading] = useState(true);
    const [items, setItems] = useState(null);
    const memberSectionClient = new MemberSectionClient();
    const membershipClient = new MembershipClient();

     useEffect(() => {
         const reloadSectionsData = async () => {
             var sections = await memberSectionClient.getAll();
             var memberships = await membershipClient.getAllForUser(userId);

             var updatedItems = sections.map((section) => {
                 return {
                     section: {
                         id: section.id,
                         name: section.name,
                         parentId: section.parentId,
                     },
                     levelItems: section.levels.map((level) => {
                         return {level: level, membership: memberships[level.id] ?? null};
                     }),
                     membership: memberships[section.id] ?? null,
                 }
             });

             setItems(updatedItems);
             setSectionsLoading(false);
        }

        if(sectionsLoading === true) {
           reloadSectionsData();
        }
     }, [sectionsLoading]);

    const prepareSubmitData = () => {
        if(items === null) {
            return null;
        }

        const levelIds = items.map((item) => {
            var ids = item.levelItems.map((levelItem) => {
                return levelItem.level.id;
            });
            ids.push(item.section.id);

            return ids;
        }).flat();

       return levelIds.map((levelId) => {
           const ownsMembership = InputHelper.getCheckboxValue('level-checkbox-' + levelId);

           if (!ownsMembership) {
               return;
           }

           const registeredDate = InputHelper.getValue(
               'registered-date-input-' + levelId,
               DateTimeHelper.getCurrentDateTime().getDate(),
           );
           var registeredTime = InputHelper.getValue(
               'registered-time-input-' + levelId,
               DateTimeHelper.getCurrentDateTime().getTime(),
           );

           if (registeredTime?.length === 5) {
               registeredTime += ':00';
           }

           const untilDate = InputHelper.getValue('until-date-input-' + levelId);
           const until = untilDate !== null ? untilDate + 'T23:59:59' : null;

           return {
                level_id: levelId,
                registered: registeredDate + 'T' + registeredTime,
                until: until,
                is_unlimited: untilDate === null,
            };
        }).filter(element => element !== undefined);
    }

     const handleUserSettingsFormSubmit = async (e) => {
        e.preventDefault();
        const memberships = prepareSubmitData();

        await membershipClient.update(userId, memberships).then((data) => {
          if (data !== undefined) {
            HTMLFormElement.prototype.submit.call(form);
          }
        });
    }

    if (form !== null) {
        document.getElementById('submit').addEventListener('click', handleUserSettingsFormSubmit);
    }

    const handleUpdate = async () => {
        const memberships = prepareSubmitData();

        await membershipClient.update(userId, memberships).then((data) => {
          if (data !== undefined) {
            setSectionsLoading(true);
          }
        });
    }

     if (items === null) {
         return (<div style={{padding: '50px'}}><Loading/></div>);
     }

     if (sectionsLoading) {
        const element = document.querySelector('.fm-user-settings');

        if (element) {
          return (<Loading height={element.offsetHeight}/>);
        }

         return (<Loading/>);
     }

    return (
		<div
			className={'fm-user-settings'}
		>
            {items.map((item) => (
                <UserSettingsSection
                    key={item.section.id}
                    section={item.section}
                    membership={item.membership}
                    levelItems={item.levelItems}
                    userId={userId}
                />
            ))}

            {form === null
                ? (
                    <SubmitButton
                        text={'Uložit'}
                        onClick={handleUpdate}
                        show={!sectionsLoading}
                        big={true}
                        centered={true}
                    />
                ) : null
            }
		</div>
    );
}

export default UserMembershipsForm;
