import React, {useState} from 'react';
import ReturnLink from "Components/Elements/ReturnLink";
import UserMembershipsForm from "Components/Elements/UserMembershipsForm";

function Member({member, removeActiveMember}) {
  return (
      <div className="member-content">
		  <ReturnLink
		  	action={removeActiveMember}
		  />

		  <h1 style={{marginBottom: '20px'}}>
			  <strong>
					Uživatel:
			  </strong>
			  {' ' + member.loginName}

			  <span style={{float: 'right', display: 'flex', alignItems: 'center', justifyItems: 'center', gap: '10px'}}>
				  <span
					  style={{color: '$grey', fontSize: '13px', height: 'max-content'}}
				  >
					  {member.email}
				  </span>
				  <span
					  dangerouslySetInnerHTML={{ __html: member.picture}}
					  style={{height: '25px'}}

				  />
			  </span>
		  </h1>

		  <h1>Členské sekce</h1>
		  <br/>
		  <UserMembershipsForm
		  	userId={member.id}
		  />

      </div>
  );
}

export default Member;
