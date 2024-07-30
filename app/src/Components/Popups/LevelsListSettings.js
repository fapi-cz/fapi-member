import React, {useState} from 'react';
import UpIcon from 'Images/up.svg'
import DownIcon from 'Images/down.svg'
import EditIcon from 'Images/edit.svg'
import MemberSectionClient from "Clients/MemberSectionClient";
import SubmitButton from "Components/Elements/SubmitButton";

function LevelsListSettings({data, setClose}) {
    const memberSectionClient = new MemberSectionClient();
    const [isEditingName, setIsEditingName] = useState(false);
    const [name, setName] = useState(data.level.name);
    const [isDeletingLevel, setIsDeletingLevel] = useState(false);

    const handleRenameLevel = async (event) => {
        event.preventDefault()
        setClose(true);

        await memberSectionClient.updateName(data.level.id, name);
        data.reloadSections();
    }

    const handleDeleteLevel = async () => {
        setClose(true);

        await memberSectionClient.delete(data.level.id);
        data.reloadSections();
    }

    const handleReorder = async (direction) => {
        setClose(true);

        await memberSectionClient.reorder(data.level.id, direction);
        data.reloadSections();
    }

    if (isDeletingLevel) {
        return (
            <div
                className="levels-list-settings"
            >
                <div className="popup-title" style={{color: 'red'}}>
                    Opravdu chcete odstranit {data.level.parentId === null ? 'sekci' : 'úroveň'}: <strong>{data.level.name}</strong>
                </div>
                <div className="actions">
                    <span className='inline-actions'>
                        <SubmitButton
                            className='inline-action'
                            text={'Smazat'}
                            type={'delete'}
                            onClick={handleDeleteLevel}
                        />
                        <SubmitButton
                            className='inline-action'
                            text={'Zrušit'}
                            onClick={()=> {setIsDeletingLevel(false)}}
                        />
                    </span>
                </div>
            </div>
        )
    }

  return (
    <div className="levels-list-settings">
        <div className="level-order">
          <img
              className="order-up clickable-icon"
              src={UpIcon}
              onClick={() => {handleReorder(-1)}}
          />
          <img
              className="order-down clickable-icon"
              src={DownIcon}
              onClick={() => {handleReorder(1)}}
          />
        </div>
        {isEditingName
            ? (
                <form
                    onSubmit={handleRenameLevel}
                >
                    <input
                        type="text"
                        className="fm-input"
                        autoFocus
                        defaultValue={name}
                        onChange={(e) => {setName(e.target.value)}}
                        style={{marginRight: '5px', marginBottom: '2px'}}
                    />
                    <SubmitButton text="Upravit"/>
                </form>
            )
            : (
                <div
                    className="level-name"
                >
                   <span className='level-id'>#{data.level.id}</span>   {data.level.name}
                </div>
            )
        }

        {!isEditingName ? (
                <img
                    className="order-down clickable-icon"
                    src={EditIcon}
                    onClick={(e) => {e.preventDefault(); setIsEditingName(true);}}
                    style={{
                        height: '15px',
                        width: '15px',
                        gridColumn: '3/4',
                        gridRow: '1/2',
                    }}
                />
            ) : ''
        }

        <div className="actions">
            <div
                className="action delete clickable-option"
                onClick={() => setIsDeletingLevel(true)}
            >
                Smazat
            </div>
        </div>
    </div>
  );
}

export default LevelsListSettings;
