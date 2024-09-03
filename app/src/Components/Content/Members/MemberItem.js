import React, {useState} from 'react';
import SubmitButton from "Components/Elements/SubmitButton";

function MemberItem({member, setActiveMember, hidden = false}) {
  return (
      <tr
           className="member-item"
           key={member.id}
           style={{display: hidden ? 'none' : 'table-row'}}
        >
            <td
                dangerouslySetInnerHTML={{ __html: member.picture}}
                style={{padding: '5px', paddingTop: '6px'}}
            />
            <td>
                {member.email}
            </td>
            <td>
                {(member.firstName === null && member.lastName === null)
                    ? ('---')
                    : ((member.firstName ?? '') + ' ' + (member.lastName ?? ''))
                }
            </td>
            <td>
                {member.levelIds.length}
            </td>
            <td>
                {member.createDate.getDateCzech()}
            </td>
            <td>
                <SubmitButton
                    text={'Upravit'}
                    onClick={() => {setActiveMember(member)}}
                />
            </td>
        </tr>
  );
}

export default MemberItem;
