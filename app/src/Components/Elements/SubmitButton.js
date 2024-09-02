import React from "react";
import Loading from "Components/Elements/Loading";

function SubmitButton({text, show = true, centered = false, big = false, type= 'default', onClick = () => {}}) {
    if (!show) {
        return (<Loading height={big ? '34px' :'29px'}/>);
    }

    return (
        <button
            className={'fm-submit-button ' + type + ' ' + (centered ? 'center ' : '') + (big ? 'big ' : '')}
            type="submit"
            onClick={onClick}
        >
            {text}
        </button>
    );
}

export default SubmitButton;
