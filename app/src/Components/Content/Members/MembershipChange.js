import React, {useEffect, useState} from 'react';

function MembershipChange({change}) {

	const untilMessage = change.until === null ? '- <strong>bez expirace</strong>' : 'do <strong>' + change.until.getDateCzech() + '</strong>';
	const dateMessage = 'od <strong>' + change.registered.getDateCzech() + '</strong> ' + untilMessage;
	const levelMessage = change.level.parentId === null ? 'sekci' : 'úrovni';

	const message = {
		created: 'Bylo vytvořeno členství v ' + levelMessage + ' <strong>' + change.level.name + '</strong> - ' + dateMessage,
		updated: 'Bylo upraveno členství v ' + levelMessage + ' <strong>' + change.level.name + '</strong> - ' + dateMessage,
		extended: 'Členství v ' + levelMessage + ' <strong>' + change.level.name + '</strong> bylo prodlouženo ' + untilMessage,
		expired: 'Vypršelo členství v ' + levelMessage + ' <strong>' + change.level.name + '</strong>',
		deleted: 'Bylo odstraněno členství v ' + levelMessage + ' <strong>' + change.level.name + '</strong>',
	}

  return (
      <div className={"membership-change " + change.type}>
	  	<span dangerouslySetInnerHTML={{ __html: message[change.type] }} />
		<span>{change.timestamp.getDateCzech() + ' v ' + change.timestamp.getHoursAndMinutes()}</span>
      </div>
  );
}

export default MembershipChange;
