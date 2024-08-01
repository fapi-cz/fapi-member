import React, {useEffect, useState} from 'react';
import Checkbox from "Components/Elements/Checkbox";
import SubmitButton from "Components/Elements/SubmitButton";
import Loading from "Components/Elements/Loading";
import MemberSectionClient from "Clients/MemberSectionClient";
import {UnlockingType} from "Enums/UnlockingType";
import HourPicker from "Components/Elements/HourPicker";

function Unlocking({level}) {
    if (level.parentId === null) {
        return (<p className='levels-content level-not-selected'>Zvolili jste členskou sekci, prosím zvolte úroveň.</p>);
    }

    const [buttonUnlock, setButtonUnlock] = useState(null);
    const [timeUnlockType, setTimeUnlockType] = useState(null);
    const [daysUnlock, setDaysUnlock] = useState(0);
    const [dateUnlock, setDateUnlock] = useState(null);
    const [hourUnlock, setHourUnlock] = useState(null);
    const [load, setLoad] = useState(true);

    const sectionClient = new MemberSectionClient();

    useEffect(() => {
        setTimeUnlockType(null);
        setLoad(true);
    }, [level.id])

    useEffect(() => {
    const reloadUnlocking = async () => {
      await sectionClient.getUnlocking(level.id).then((data) => {
        setButtonUnlock(data[UnlockingType.BUTTON_UNLOCK]);
        setTimeUnlockType(data[UnlockingType.TIME_UNLOCK]);
        setDateUnlock(data[UnlockingType.DATE_UNLOCK]);
        setDaysUnlock(data[UnlockingType.DAYS_UNLOCK]);
        setHourUnlock(data[UnlockingType.HOUR_UNLOCK]);
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
        setTimeUnlockType(
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

    const handleChangeHourUnlock = (event) => {
        setHourUnlock(
            parseInt(event.target.value),
        );
    }

    const handleUpdateUnlocking = async (event) => {
        event.preventDefault();
        await sectionClient.updateUnlocking(
            level.id,
            buttonUnlock,
            timeUnlockType,
            daysUnlock,
            dateUnlock,
            hourUnlock,
        )

        setLoad(true);
    }

    if (timeUnlockType === null || buttonUnlock === null) {
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
                    defaultChecked={timeUnlockType === 'disallow'}
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
                    defaultChecked={timeUnlockType === 'date'}
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
                    defaultChecked={timeUnlockType === 'days'}
                />
                <label htmlFor="days">Počet dní od registrace</label>
            </div>

            <div id="date-settings-content" hidden={timeUnlockType !== 'date'}>
                <p>Datum kdy bude sekce/úroveň odemčena pro všechny uživatele.</p>
                <input
                    className='fm-input'
                    type="date"
                    name="unlock-date"
                    defaultValue={dateUnlock}
                    onInput={handleChangeDateUnlock}
                />
                <span style={{margin: '0px 5px'}}>v</span>
                {timeUnlockType === 'date'
                    ? <HourPicker id={'unlock-hour'} onChange={handleChangeHourUnlock} defaultValue={hourUnlock}/>
                    : null}
            </div>
            <div id="days-settings-content" hidden={timeUnlockType !== 'days'}>
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
                <span style={{margin: '0px 5px'}}>dní po registraci v</span>
                {timeUnlockType === 'days'
                    ? <HourPicker id={'unlock-hour'} onChange={handleChangeHourUnlock} defaultValue={hourUnlock}/>
                    : null}
                <p>0 dní po registraci v 0:00 = Sekce bude přístupná ihned po registraci</p>
                <p>5 dní po registraci v 8:00 = Sekce bude přístupná 5. den po registraci v 8 hodin ráno</p>
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
