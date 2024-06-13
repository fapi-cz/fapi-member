import React, {useEffect, useState} from 'react';
import {EmailType} from "Enums/EmailType";
import EmailContainer from "Components/Content/Levels/Levels/LevelsContent/EmailContainer";
import EmailClient from "Clients/EmailClient";
import Loading from "Components/Elements/Loading";
import SubmitButton from "Components/Elements/SubmitButton";

function Emails({level}) {
  const [emails, setEmails] = useState(null);
  const [loadEmails, setLoadEmails] = useState(true);

  const emailTypes = [
    EmailType.AFTER_REGISTRATION,
    EmailType.AFTER_MEMBERSHIP_PROLONGED,
    EmailType.AFTER_ADDING,
  ];

  const containersOpened = emailTypes.reduce((acc, type) => {
    const [isOpened, setIsOpened] = useState(false);
    acc[type] = [isOpened, setIsOpened];
    return acc;
  }, {});

  const emailClient = new EmailClient();

  useEffect(() => {
    setEmails(null);
    setLoadEmails(true);
  }, [level.id])

  useEffect(() => {
    const reloadEmails = async () => {
      await emailClient.getForLevel(level.id).then((data) => {
        setEmails(data);
      });

      setLoadEmails(false);
    }

    if (loadEmails === true) {
      reloadEmails();
    }
  }, [loadEmails]);

  var isSection = level.parentId === null;
  var emailTitles = {
    [EmailType.AFTER_REGISTRATION]: 'E-mail po registraci do ' + (isSection ? 'sekce' : 'úrovně'),
    [EmailType.AFTER_MEMBERSHIP_PROLONGED]: 'E-mail po prodloužení členství v ' + (isSection ? 'sekci' : 'úrovni'),
    [EmailType.AFTER_ADDING]: 'E-mail po přidání do ' + (isSection ? 'sekce' : 'úrovně'),
  };

  const handleSaveEmails = async (event) => {
    event.preventDefault();
    const form = event.target;

    const afterRegistration = getEmailDataFromInput(EmailType.AFTER_REGISTRATION, form);
    const afterMembershipProlonged = getEmailDataFromInput(EmailType.AFTER_MEMBERSHIP_PROLONGED, form);
    const afterAdding = getEmailDataFromInput(EmailType.AFTER_ADDING, form);

    await emailClient.updateForLevel(
        level.id,
        afterRegistration,
        afterMembershipProlonged,
        afterAdding,
    );
    setLoadEmails(true);
  }

  const getEmailDataFromInput = (type, form) => {
    var subject = form.querySelector('#email-subject-' + type).value;
    var body = form.querySelector('#email-body-' + type).value;

    return {subject: subject, body: body};
  }

  if (emails === null) {
    return (<Loading height={'346px'}/>);
  }

  return (
    <form
        className="levels-content levels-emails"
        onSubmit={handleSaveEmails}
    >
      {Object.entries(emailTitles).map(([type, title]) => (
          <EmailContainer
              key={type}
              type={type}
              title={title}
              subject={emails[type].s}
              body={emails[type].b}
              isOpened={containersOpened[type][0]}
              setIsOpened={containersOpened[type][1]}
          />
      ))}

      <SubmitButton
          text={'Uložit'}
          show={!loadEmails}
          centered={true}
          big={true}
      />
    </form>
  );
}

export default Emails;
