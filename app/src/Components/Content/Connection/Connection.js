import React, {useEffect, useState} from 'react';

import 'Styles/connection.css'

import Help from "Components/Content/Overview/Help";
import SubmitButton from "Components/Elements/SubmitButton";
import Loading from "Components/Elements/Loading";
import ApiConnectionClient from "Clients/ApiConnectionClient";
import {InputHelper} from "Helpers/InputHelper";
import HiddenText from "Components/Elements/HiddenText";

function Connection() {
    const [loading, setLoading] = useState(true);
    const [connections, setConnections] = useState(null);
    const [connectionStatuses, setConnectionStatuses] = useState(null);
    const [apiToken, setApiToken] = useState(null)
    
    const connectionClient = new ApiConnectionClient();

    useEffect(() => {
         const reloadData = async () => {
            var updatedConnections = await connectionClient.list();
            setConnections(updatedConnections);

            var updatedIsConnected = await connectionClient.getStatusForAll();
            setConnectionStatuses(updatedIsConnected);

            var updatedApiToken = await connectionClient.getApiToken();
            setApiToken(updatedApiToken?.apiToken);

            setLoading(false);
        }

        if(loading === true) {
           reloadData();
        }
     }, [loading]);

    const handleConnectionRemove = async (apiKey) => {
        await connectionClient.remove(apiKey);
        setLoading(true);
    }

    const handleConnectionCreate = async () => {
        var apiUser = InputHelper.getValue('api-user');
        var apiKey = InputHelper.getValue('api-key');
        await connectionClient.create(apiUser , apiKey);

        setLoading(true);
    }

    if (connections === null || connectionStatuses === null) {
        return (<Loading/>);
    }

  return (
    <div className='content-connection'>
        <div>
            <h3>Propojené účty FAPI:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Uživatelské jméno (email)</th>
                        <th>API klíč</th>
                        <th>Stav</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                  {connections.map((connection) => (
                      <tr key={connection.apiKey}>
                        <td>{connection.apiUser}</td>
                        <td><HiddenText value={connection.apiKey}/></td>
                        <td>
                            {
                                connectionStatuses[connection.apiKey]
                                    ? (<span className='connection-status connected'>Připojeno</span>)
                                    : (<span className='connection-status error'>Nepřipojeno</span>)
                            }
                        </td>
                        <td>
                            <SubmitButton
                                text={'Odstranit'}
                                onClick={() => {handleConnectionRemove(connection.apiKey)}}
                            />
                        </td>
                      </tr>
                  ))}
                </tbody>
            </table>

            <br/><br/>

            <h3>Propojit účet FAPI (max. 5)</h3>

            <br/>

            <label className='big' htmlFor="api-user">Uživatelské jméno (e-mail)</label>
            <input className='fm-input big' type='text' id='api-user' placeholder='me@example.com'/>

            <label className='big' htmlFor="api-key">API klíč</label>
            <input className='fm-input big' type='text' id='api-key'/>

            <SubmitButton
                text={'Propojit s FAPI'}
                big={true}
                onClick={handleConnectionCreate}
            />

           <br/><br/><div className='vertical-divider'/>

            {apiToken === null
                ? (<Loading height={'30px'}/>)
                : (
                    <div>
                        <br/>
                        <span>FAPI Member API Token: </span>
                        <HiddenText value={apiToken}/>
                    </div>
                )
            }
        </div>
        <Help/>
    </div>
  );
}

export default Connection;
