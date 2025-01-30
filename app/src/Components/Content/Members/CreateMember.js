import 'Styles/members.css';
import 'Styles/userSettings.css';

import React, {useEffect, useState} from 'react';
import Input from "Components/Elements/Input";
import Checkbox from "Components/Elements/Checkbox";
import SubmitButton from "Components/Elements/SubmitButton";
import UserSettingsSection from "Components/UserSettings/UserSettingsSection";
import MemberSectionClient from "Clients/MemberSectionClient";
import Loading from "Components/Elements/Loading";
import Select from "Components/Elements/Select";
import AlertService from "Services/AlertService";
import UserClient from "Clients/UserClient";
import MembershipClient from "Clients/MembershipClient";
import {InputHelper} from "Helpers/InputHelper";
import DateTimeHelper from "Helpers/DateTimeHelper";

function CreateMember({member, setActiveMember, hidden = false}) {
    const memberSectionClient = new MemberSectionClient();
    const userClient = new UserClient();
    const membershipClient = new MembershipClient();

    const [loading, setLoading] = useState(true);
    const [processing, setProcessing] = useState(false);
    const [levels, setLevels] = useState(null)
    const [levelToAdd, setLevelToAdd] = useState(null);
    const [formData, setFormData] = useState({});

    useEffect(() => {
         const reloadData = async () => {
           await memberSectionClient.getAllAsLevels().then((updatedLevels) => {
               setLevelToAdd(updatedLevels[0]);
               setLevels(updatedLevels);
               setLoading(false);
           });
        }

        if(loading === true) {
           reloadData();
        }
     }, [loading]);

    const handleCreateMember = async (e) => {
        setProcessing(true);

        var user = await userClient.getByEmail(formData.email)

        if (user?.id) {
            AlertService.showAlert('Uživatel s tímto e-mailem již existuje.', 'error');
            setProcessing(false);

            return;
        }

        if (!formData.add_level) {
            await userClient.create(formData.email, formData.first_name, formData.last_name)
        } else {
            const registeredDate = InputHelper.getValue(
               'registered-date-input-' + formData.level,
               DateTimeHelper.getCurrentDateTime().getDate(),
           );
           var registeredTime = InputHelper.getValue(
               'registered-time-input-' + formData.level,
               DateTimeHelper.getCurrentDateTime().getTime(),
           );

           if (registeredTime?.length === 5) {
               registeredTime += ':00';
           }

           const untilDate = InputHelper.getValue('until-date-input-' + formData.level);
           const until = untilDate !== null ? untilDate : null;

           formData.registered = registeredDate + ' ' + registeredTime;
           formData.until = until;


            await membershipClient.create(formData);
        }

        user = await userClient.getByEmail(formData.email)

        if (user?.id) {
            window.location.href = '/wp-admin/admin.php?page=fapi-member-settings&fm-page=members&member=' + user.id
        } else {
            setProcessing(false);
        }
    }

    const handleUpdateLevelToAdd = (id) => {
        levels.forEach((level) => {
            if (level.id == id) {
                setLevelToAdd(level);
                formData.level = level.id;
            }
        })
    }

    const handleFormChange = (e) => {
        setFormData({
          ...formData,
          level: levelToAdd?.id || null,
          [e.target.name]: e.target.value !== 'on' ? e.target.value : e.target.checked,
        });
    };

    if (levels === null || loading === true) {
        return (<Loading/>);
    }

  return (
      <div>
        <h1 style={{marginBottom: '20px'}}>
                <strong>Vytvořit člena</strong>
        </h1>
          <form
              onSubmit={handleCreateMember}
              onChange={handleFormChange}
          >
              <div
                  style={{display: 'block', margin: '0px auto', width: 'fit-content'}}
              >
                <Input
                  id={'email'}
                  label={'E-mail'}
                  type={'text'}
                  required={true}
                />
                <Input
                  id={'first_name'}
                  label={'Jméno'}
                  type={'text'}
                />
                <Input
                  id={'last_name'}
                  label={'Příjmení'}
                  type={'text'}
                />
                  {
                      levelToAdd
                          ? (
                            <div className={'fm-checkbox-container'}>
                              <Checkbox
                                id={'add_level'}
                                small={true}
                              />
                              <label htmlFor={'add_level'}>Vytvořit členství</label>
                            </div>
                          )
                          :null
                  }
              </div>
              <br/>
              {formData?.add_level
                  ? (
                      <div>
                          <h2>Přidat člena do sekce/úrovně</h2>
                          <div className='vertical-divider'/>
                          <br/>
                              <label>Sekce/úroveň: </label>
                              <Select
                                  id='level'
                                  includeEmptyOption={false}
                                  options={
                                    levels.map((section) =>  {
                                        return {text: section.name, value: section.id}
                                    })
                                  }
                                  onChangeUpdateFunction={handleUpdateLevelToAdd}
                              />
                          <br/>
                          <br/>
                          {levelToAdd !== null
                              ? (
                                  <div className='fm-user-settings'>
                                      <UserSettingsSection
                                          section={levelToAdd}
                                          membership={null}
                                          levelItems={[]}
                                          userId={null}
                                          showCheckbox={false}
                                      />
                                  </div>
                              )
                              : null
                          }
                          <div className={'fm-checkbox-container'}>
                              <Checkbox
                                id={'send_email'}
                                small={true}
                              />
                              <label htmlFor={'send_email'}>Odeslat e-mail po registraci</label>
                          </div>
                      <br/>
                      </div>
                  )
                  : null
              }
          </form>

          <SubmitButton
              text={'Vytvořit'}
              centered={true}
              big={true}
              onClick={handleCreateMember}
              show={!processing}
          />
      </div>
  );
}

export default CreateMember;
