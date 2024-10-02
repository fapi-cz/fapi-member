import React, {useEffect, useState} from 'react';
import ReturnLink from "Components/Elements/ReturnLink";
import UserMembershipsForm from "Components/Elements/UserMembershipsForm";
import StatisticsClient from "Clients/StatisticsClient";
import Loading from "Components/Elements/Loading";
import MembershipChange from "Components/Content/Members/MembershipChange";
import {LicenceHelper} from "Helpers/LicenceHelper";

function Member({member, removeActiveMember}) {
    const [memberChanges, setMemberChanges] = useState([]);
    const statisticsClient = new StatisticsClient();
    const [load, setLoad] = useState(true);

	useEffect(() => {
        const reload = async () => {
		  await statisticsClient.getMembershipChangesForUser(member.id).then((data) => {
			  setMemberChanges(data);
		  });

          setLoad(false);
        }

        if (load === true) {
          reload();
        }
    }, [load]);

	if (load === true) {
		return <Loading/>
	}

  return (
      <div className="member-content">
		  <ReturnLink
		  	action={removeActiveMember}
		  />

		  <h1 style={{marginBottom: '20px'}}>
			  <strong>
					Člen:
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
		  <a href={'user-edit.php?user_id=' + member.id}>Nastavení uživatele</a>
		  <br/>
		  <br/>
		  <h1>Členské sekce</h1>
		  <br/>
		  <UserMembershipsForm
		  	userId={member.id}
			onSave={() => {setLoad(true)}}
		  />
		  {LicenceHelper.hasFmLicence()
			  ? (
				  <div>
					  <br/>
					  <h1>Historie Změn</h1>
					  <br/>
					  { memberChanges.map((change) => (
						  <MembershipChange
							  change={change}
						  />
					  ))}
				  </div>
			  ) : null
		  }
      </div>
  );
}

export default Member;
