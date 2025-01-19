import React, {useEffect, useState} from 'react';
import ReturnLink from "Components/Elements/ReturnLink";
import UserMembershipsForm from "Components/Elements/UserMembershipsForm";
import StatisticsClient from "Clients/StatisticsClient";
import Loading from "Components/Elements/Loading";
import MembershipChange from "Components/Content/Members/MembershipChange";
import {LicenceHelper} from "Helpers/LicenceHelper";
import userChangesExample from 'Images/FM-user-changes-example.png';
import ApiConnectionClient from "Clients/ApiConnectionClient";


function Member({member, removeActiveMember}) {
    const connectionClient = new ApiConnectionClient();
	const statisticsClient = new StatisticsClient();

	const [memberChanges, setMemberChanges] = useState([]);
    const [lastActivityDate, setLastActivityDate] = useState(null);
    const [load, setLoad] = useState(true);
	const [urlObject, setUrlObject] = useState(null);

	useEffect(() => {
        const reload = async () => {
		  await statisticsClient.getMembershipChangesForUser(member.id).then((data) => {
			  setMemberChanges(data);
		  });

			await statisticsClient.getLastActivityForUser(member.id).then((data) => {
				setLastActivityDate(data);
			});

          setLoad(false);
        }

		const fetchInitialData = async () => {
            const connections = await connectionClient.list();
            const connection = connections[0] || {};
            const urlObject = new URL('https://page.fapi.cz/10559/fapi-member-pro');

            const data = {
                "fapi-form-email": connection.billing?.email,
                "fapi-form-mobil": connection.billing?.phone,
                "fapi-form-company": connection.billing?.name,
                "fapi-form-ic": connection.billing?.ic,
                "fapi-form-dic": connection.billing?.dic,
                "fapi-form-ic-dph": connection.billing?.['ic_dph'],
                "fapi-form-street": connection.billing?.address?.street,
                "fapi-form-city": connection.billing?.address?.city,
                "fapi-form-postcode": connection.billing?.address?.zip,
                "fapi-form-state": connection.billing?.address?.country,
            };

            const jsonData = JSON.stringify(data);
            const base64EncodedData = btoa(encodeURIComponent(jsonData));
            urlObject.search += `fapi-form-data=${base64EncodedData}`;

            setUrlObject(urlObject);
        };

        if (load === true) {
          reload();
		  fetchInitialData()
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
		  <div style={{fontSize: '13px', marginTop: '-20px'}}>
			  <strong>Poslední přihlášení: </strong>
			  {LicenceHelper.hasFmLicence()
				  ? (
					  lastActivityDate !== null
						  ? lastActivityDate.getDateCzech() + ' v ' + lastActivityDate.getHoursAndMinutes()
						  : 'Nebyla zaznamenána žádná aktivita'
				  ) : <a target="_blank" href={urlObject}
				  >Získat FAPI Member Pro</a>
			  }
			  <br/>
			  <br/>
		  </div>

		  <a href={'user-edit.php?user_id=' + member.id}>Nastavení uživatele</a>
		  <br/>
		  <br/>
		  <h1>Členské sekce</h1>
		  <br/>
		  <UserMembershipsForm
		  	userId={member.id}
			onSave={() => {setLoad(true)}}
		  />

		  <div>
			  <br/>
			  <h1>Historie Změn</h1>
			  <br/>
			  {LicenceHelper.hasFmLicence()
				  ? (
					  memberChanges.length === 0 ?
						  <div className={"membership-change success"}>
							  <span>Nebyla nalezena žádná historie.</span>
							  <span/>
						  </div>
						  :
						  memberChanges.map((change, index) => (
							  <MembershipChange
								  key={index}
								  change={change}
							  />
						  ))

				  ) :
				  <div className="fm-no-licence">
					  <a className={"fm-link-button"} target="_blank" href={urlObject}
					  >Získat FAPI Member Pro</a>
					  <div className={"image"} style={{backgroundImage: `url(${userChangesExample})`}}>
					  </div>
				  </div>
			  }
		  </div>
</div>
);
}

export default Member;
