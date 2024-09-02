import 'Styles/overview.css';

import React, {useEffect, useState, useRef} from 'react';
import UserClient from "Clients/UserClient";
import Loading from "Components/Elements/Loading";
import SubmitButton from "Components/Elements/SubmitButton";

function MemberList({level}) {
    const [loading, setLoading] = useState(true);
    const [users, setUsers] = useState(null);
    const table = useRef();

    const userClient = new UserClient();

    useEffect(() => {
         const reloadData = async () => {
            var updatedUsers = await userClient.getByLevel(level.id);
            setUsers(updatedUsers);
            setLoading(false);
        }

        if(loading === true) {
           reloadData();
        }
     }, [loading]);

    const exportCsv = () => {
        const rows = Array.from(table.current.querySelectorAll('tr'));
        const csvContent = rows.map(row => {
          const cols = Array.from(row.querySelectorAll('th, td'));
          return cols.map(col => col.innerText).join(',');
        }).join('\n');
    
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = level.name.replace(/ /g, '_') + '_members' +'.csv';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }



    if (users === null) {
        return (<Loading/>)
    }

     return (
          <div className="member-list">
              <h2>Seznam členů</h2>
              <table ref={table}>
                <thead>
                    <tr>
                        <th>Uživatelské jméno</th>
                        <th>Jméno a příjmení</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                  {users.map((user) => (
                      <tr key={user.id}>
                        <td>
                            <a href={'user-edit.php?user_id=' + user.id}>{user.loginName}</a>
                        </td>
                        <td>
                            {(user.firstName === null && user.lastName === null)
                                ? ('---')
                                : ((user.firstName ?? '') + ' ' + (user.lastName ?? ''))
                            }
                        </td>
                        <td>{user.email}</td>
                      </tr>
                  ))}
                </tbody>
              </table>
              <br/>
              <SubmitButton
                  text={'Exportovat'}
                  centered={true}
                  big={true}
                  onClick={exportCsv}
              />
          </div>
    );
}

export default MemberList;
