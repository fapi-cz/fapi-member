import React, {useState} from 'react';
import upIcon from 'Images/up.svg';
import downIcon from 'Images/down.svg';

function EmailContainer({type, title, subject, body, isOpened, setIsOpened}) {
    return (
      <div className="email-container">
          <div
              className="email-title"
               onClick={() => {setIsOpened(!isOpened)}}
          >
              <span>{title}</span>
              <img
                  src={isOpened ? upIcon : downIcon}
              />
          </div>
          <div hidden={!isOpened}>
              <div className="vertical-divider"/>
              <label>Předmět e-mailu</label>
              <input
                  className="email-subject-input fm-input"
                  name={'email-subject-' + type}
                  id={'email-subject-' + type}
                  type="text"
                  defaultValue={subject}
              />
              <label>Obsah e-mailu</label>
              <textarea
                className="email-content-input fm-input"
                name={'email-body-' + type}
                id={'email-body-' + type}
                defaultValue={body}
              />
          </div>
      </div>
    );
}

export default EmailContainer;
