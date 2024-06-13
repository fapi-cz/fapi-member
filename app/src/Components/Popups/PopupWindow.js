import React, {useEffect, useState} from 'react';

function PopupWindow({clickEvent, Component, componentData, onClose}) {
    if (clickEvent === null || clickEvent === undefined) {
        return null;
    }

    const [close, setClose] = useState(false);
    const contentArea = document.querySelector('#wpcontent');
    const rect = contentArea.getBoundingClientRect();
    const style = window.getComputedStyle(contentArea);

    const upperLeftCorner = {
        x: clickEvent.clientX - rect.left - parseFloat(style.paddingLeft) - 5,
        y: clickEvent.clientY - rect.top - 5,
    }

    componentData.event = clickEvent;

    useEffect(() => {
        const handleClick = (event) => {
            const popupWindow = document.querySelector('.popup-window');
            if (popupWindow) {
                const popUpRect = popupWindow.getBoundingClientRect();
                const bottomRightCorner = {
                    x: popUpRect.right - rect.left - parseFloat(style.paddingLeft),
                    y: popUpRect.bottom - rect.top,
                };

                const clickPosition = {
                    x: event.clientX - rect.left - parseFloat(style.paddingLeft),
                    y: event.clientY - rect.top,
                }

                if (clickPosition.x > bottomRightCorner.x || clickPosition.x < upperLeftCorner.x ||
                    clickPosition.y > bottomRightCorner.y || clickPosition.y < upperLeftCorner.y) {
                    document.removeEventListener('mousedown', handleClick);
                    onClose();
                }
            }

        };

        if (close === true) {
            document.removeEventListener('mousedown', handleClick);
            onClose();
        } else {
            document.addEventListener('mousedown', handleClick);
        }

        return () => {
            document.removeEventListener('mousedown', handleClick);
        };
    }, [close]);

  return (
    <div className="popup-window" style={{ left: upperLeftCorner.x, top: upperLeftCorner.y}}>
        {<Component
            data={componentData}
            setClose={setClose}
        />}
    </div>
  );
}

export default PopupWindow;
