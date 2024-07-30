import {useState} from "react";
import SubmitButton from "Components/Elements/SubmitButton";
import MemberSectionClient from "Clients/MemberSectionClient";

function NewLevel({data, setClose}) {
    const [name, setName] = useState('');

    const memberSectionClient = new MemberSectionClient();

    const handleCreateLevel = async (event) => {
        event.preventDefault()

        setClose(true);

        await memberSectionClient.create(name, data.parent?.id);
        data.reloadSections();
    }

    var message = 'Vytvořit novou sekci';

    if (data.parent !== null) {
        message = "Vytvořit úroveň v sekci '" + data.parent.name + "'";
    }

    return (
        <form
            className="new-level-window"
            onSubmit={handleCreateLevel}
        >
            <div className="popup-title">
                {message}
            </div>
            <input
                className="fm-input"
                style={{marginRight: '5px'}}
                type="text"
                name="name"
                placeholder="Název"
                onChange={(event) => {setName(event.target.value);}}
                autoFocus
            />

            <SubmitButton
                text="Vytvořit"
            />
        </form>
    );
}

export default NewLevel;
