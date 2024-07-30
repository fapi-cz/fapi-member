import React, {useEffect, useState} from 'react';
import Checkbox from "Components/Elements/Checkbox";
import SubmitButton from "Components/Elements/SubmitButton";
import Loading from "Components/Elements/Loading";
import MemberSectionClient from "Clients/MemberSectionClient";
import {UnlockingType} from "Enums/UnlockingType";

function Unlocking({level}) {
    if (level.parentId === null) {
        return (<p className='levels-content level-not-selected'>Zvolili jste členskou sekci, prosím zvolte úroveň.</p>);
    }

    const [buttonUnlock, setButtonUnlock] = useState(null);
    const [timeUnlock, setTimeUnlock] = useState(null);
    const [daysUnlock, setDaysUnlock] = useState(0);
    const [dateUnlock, setDateUnlock] = useState(null);
    const [load, setLoad] = useState(true);

    const sectionClient = new MemberSectionClient();

    useEffect(() => {
        setTimeUnlock(null);
        setLoad(true);
    }, [level.id])

    useEffect(() => {
    const reloadUnlocking = async () => {
      await sectionClient.getUnlocking(level.id).then((data) => {
        setButtonUnlock(data[UnlockingType.BUTTON_UNLOCK]);
        setTimeUnlock(data[UnlockingType.TIME_UNLOCK]);
        setDateUnlock(data[UnlockingType.DATE_UNLOCK]);
        setDaysUnlock(data[UnlockingType.DAYS_UNLOCK]);
      });

      setLoad(false);
    }

    if (load === true) {
      reloadUnlocking();
    }
    }, [load]);

    const handleChangeButtonUnlock = (event) => {
        setButtonUnlock(event.target.checked);
    }

    const handleChangeTimeUnlock = (event) => {
        setTimeUnlock(
            event.target.value,
        );
    }

    const handleChangeDaysUnlock = (event) => {
        setDaysUnlock(
            parseInt(event.target.value),
        );
    }

    const handleChangeDateUnlock = (event) => {
        setDateUnlock(
            event.target.value,
        );
    }

    const handleUpdateUnlocking = async (event) => {
        event.preventDefault();
        await sectionClient.updateUnlocking(
            level.id,
            buttonUnlock,
            timeUnlock,
            daysUnlock,
            dateUnlock,
        )

        setLoad(true);
    }

    if (timeUnlock === null || buttonUnlock === null) {
        return (<Loading/>);
    }

    return (
        <form className="levels-content levels-unlocking" onSubmit={handleUpdateUnlocking}>
            <h4>Odemknutí tlačítkem</h4>
            <div className='button-unlock-container'>
                <label htmlFor='is-button-unlock'>Povolit:</label>
                <Checkbox
                    checked={buttonUnlock}
                    id='is-button-unlock'
                    onClick={handleChangeButtonUnlock}
                />
            </div>

            <div id="button_unlock_settings" hidden={!buttonUnlock}>
                <p>K odemčení úrovně musí uživatel již mít přístup do dané sekce.</p>
                <label>Shortcode tlačítka pro uvolnění obsahu: </label>
                <br/>
                <code>{'[fapi-member-unlock-level level=' + level.id + ']'}</code>
            </div>


            <div className='vertical-divider'/>
            <br/>

            <h4>Časově omezené odemykání úrovně</h4>
            <div>
                <input
                    className='fm-input'
                    type="radio"
                    name="time_unlock"
                    value="disallow"
                    id="disallow"
                    onClick={handleChangeTimeUnlock}
                    defaultChecked={timeUnlock === 'disallow'}
                />
                <label htmlFor="disallow">Nepovolovat</label>
            </div>
            <div>
                <input
                    className='fm-input'
                    type="radio"
                    name="time_unlock"
                    value="date"
                    id="date"
                    onClick={handleChangeTimeUnlock}
                    defaultChecked={timeUnlock === 'date'}
                />
                <label htmlFor="date">Od pevného data</label>
            </div>
            <div>
                <input
                    className='fm-input'
                    type="radio"
                    name="time_unlock"
                    value="days"
                    id="days"
                    onClick={handleChangeTimeUnlock}
                    defaultChecked={timeUnlock === 'days'}
                />
                <label htmlFor="days">Počet dní od registrace</label>
            </div>

            <div id="date-settings-content" hidden={timeUnlock !== 'date'}>
                <p>Datum kdy bude sekce/úroveň odemčena pro všechny uživatele.</p>
                <input
                    className='fm-input'
                    type="date"
                    name="unlock-date"
                    defaultValue={dateUnlock}
                    onInput={handleChangeDateUnlock}
                />
            </div>
            <div id="days-settings-content" hidden={timeUnlock !== 'days'}>
                <p>Počet dní od registrace uživatele do členské sekce, po kterých má být vybraná sekce/úroveň zpřístupněna.</p>
                <input
                    className='fm-input'
                    type="number"
                    min="0"
                    max="100"
                    name="days-to-unlock"
                    defaultValue={daysUnlock}
                    onInput={(e) => {
                        e.target.value = Math.abs(e.target.value);
                        handleChangeDaysUnlock(e);
                    }}
                />
                <p>0 = Sekce bude přístupná ihned po registraci</p>
                <p>3 = Sekce bude přístupná 3 den po registraci</p>
            </div>

            <div className='vertical-divider'/>

            <SubmitButton
                text={'Uložit'}
                show={!load}
                centered={true}
                big={true}
            />
        </form>
    );
}

export default Unlocking;
