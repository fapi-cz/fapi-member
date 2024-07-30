import React, {useEffect, useState} from 'react';

import 'Styles/userSettings.css';
import 'Styles/global.css';
import MemberSectionClient from "Clients/MemberSectionClient";
import MembershipClient from "Clients/MembershipClient";
import Loading from "Components/Elements/Loading";
import UserSettingsSection from "Components/UserSettings/UserSettingsSection";
import {InputHelper} from "Helpers/InputHelper";
import DateTimeHelper from "Helpers/DateTimeHelper";

function UserSettings() {
    const [sectionsLoading, setSectionsLoading] = useState(true);
    const [items, setItems] = useState(null);
    const form = document.getElementById('your-profile');
    const submitButton = document.getElementById('submit');
    const memberSectionClient = new MemberSectionClient();
    const membershipClient = new MembershipClient();
    var userId = new URLSearchParams(window.location.search).get('user_id');


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
           const until = untilDate !== null ? untilDate + 'T00:00:00' : null;

           return {
                level_id: levelId,
                registered: registeredDate + 'T' + registeredTime,
                until: until,
                is_unlimited: untilDate === null,
            };
        }).filter(element => element !== undefined);
    }

     const handleFormSubmit = async () => {
      const memberships = prepareSubmitData();

      if(memberships !== null){
          await membershipClient.update(userId, memberships);
      }
    }

    form.addEventListener('submit', handleFormSubmit);


     if (items === null) {
         return (<div style={{padding: '50px'}}><Loading/></div>);
     }

    return (
        <div className='user-settings'>
            <h1>Členské sekce</h1>
            <br/>
            {items.map((item) => (
                <UserSettingsSection
                    key={item.section.id}
                    section={item.section}
                    membership={item.membership}
                    levelItems={item.levelItems}
                />
            ))}
        </div>
    );
}

export default UserSettings;
