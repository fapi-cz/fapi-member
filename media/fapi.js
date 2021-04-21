// polyfill
if (!Element.prototype.matches) {
    Element.prototype.matches =
        Element.prototype.msMatchesSelector ||
        Element.prototype.webkitMatchesSelector;
}

if (!Element.prototype.closest) {
    Element.prototype.closest = function(s) {
        var el = this;

        do {
            if (Element.prototype.matches.call(el, s)) return el;
            el = el.parentElement || el.parentNode;
        } while (el !== null && el.nodeType === 1);
        return null;
    };
}

document.addEventListener('click', (event) => {
    if (event.target.matches('.levels .remove')) {
        let id = event.target.parentNode.getAttribute('data-id');
        Swal.fire({
            html: '<strong>Opravdu si přejete odstranit členskou sekci/úroveň?</strong><br><br>Smazáním sekce/úrovně nedojde ke smazání stránek v sekci/úrovni.',
            showDenyButton: true,
            confirmButtonText: `Smazat`,
            denyButtonText: `Ponechat`,
            customClass: {
                confirmButton: 'removeConfirmButton',
                denyButton: 'removeDenyButton',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.getElementById('LevelRemoveForm')
                form.querySelector('[name="level_id"]').setAttribute('value', id)
                form.submit()
            } else if (result.isDenied) {
                // none
            }
        })
    }
})

document.addEventListener('click', (event) => {
    if (event.target.matches('.levels .edit')) {
        let name = event.target.parentNode.querySelector('span').innerText;
        let id = event.target.parentNode.getAttribute('data-id');
        Swal.fire({
            input: 'text',
            inputLabel: 'Nový název',
            inputValue: name,
            showDenyButton: true,
            confirmButtonText: `Přejmenovat`,
            denyButtonText: `Ponechat`,
            customClass: {
                confirmButton: 'renameConfirmButton',
                denyButton: 'renameDenyButton',
            }

        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.getElementById('LevelEditForm')
                form.querySelector('[name="level_id"]').setAttribute('value', id)
                form.querySelector('[name="name"]').setAttribute('value', result.value)
                form.submit()
            } else if (result.isDenied) {
                // none
            }
        })
    }
})

document.addEventListener('click', (event) => {
    if (event.target.matches('form.pages button')) {
        event.preventDefault()
        let id = findSelectedLevel()
        let form = event.target.closest('form');
        form.querySelector('[name="level_id"]').value = id
        form.submit()
    }
})

document.addEventListener('click', (event) => {
    if (event.target.matches('.levels a')) {
        event.preventDefault()
        let li = event.target.parentNode
        Array.from(document.querySelectorAll('.levels li.selected')).forEach((one) => {
            one.classList.remove('selected')
        })
        li.classList.add('selected')
        reloadPagesToRemove()
        recheckPagesToAdd()
        reenableAddRemovePagesButton()
        changeSubSubMenuLinks()
    }
})

document.addEventListener('DOMContentLoaded', (event) => {

    if (findSelectedLevel()) {
        reloadPagesToRemove()
    }
    recheckPagesToAdd()
    disableAddRemovePagesButton()
    reenableAddRemovePagesButton()
})

document.addEventListener('click', (event) => {
    if (event.target.matches('.oneEmail .carret') || event.target.matches('.oneEmail .header h3')) {
        event.target.closest('.oneEmail').classList.toggle('open');
    }
})

document.addEventListener('click', (event) => {
    if (event.target.matches('.specifyLevelEmailCheckbox')) {
        let label = event.target.closest('.oneEmail').querySelector('.body > p')
        let subj = event.target.closest('.oneEmail').querySelector('#mail_subject')
        let body = event.target.closest('.oneEmail').querySelector('#mail_body')
        let inputs = event.target.closest('.oneEmail').querySelector('.inputs')
        if(event.target.checked) {
            label.classList.add('hidden')
            subj.removeAttribute('readonly')
            body.removeAttribute('readonly')
            inputs.classList.remove('collapsed')
        } else {
            label.classList.remove('hidden')
            subj.value = ''
            body.value = ''
            subj.setAttribute('readonly', true)
            body.setAttribute('readonly', true)
            inputs.classList.add('collapsed')
        }
    }
})

document.addEventListener('click', (event) => {
    if (event.target.matches('.shortcodes h3') || event.target.matches('.shortcodes h3 .carret')) {
        event.target.closest('.shortcodes').classList.toggle('open');
    }
})

const changeSubSubMenuLinks = () => {
    let lvl = findSelectedLevel()
    Array.from(document.querySelectorAll('.subsubmenuitem')).forEach((one) => {
        let url = one.getAttribute('href')
        let lvlR = new RegExp('&level=');
        if (lvlR.test(url)) {
            one.setAttribute(
                'href',
                url.replace(/(&level=[0-9]*)/, `&level=${lvl}`)
                )
        } else {
            one.setAttribute('href', `${url}&level=${lvl}`)
        }
    })
}

const levelToPages = (lvl) => {
    if (!window.hasOwnProperty('LevelToPage')) {
        let jsonEl = document.getElementById('LevelToPage');
        if (jsonEl) {
            window.LevelToPage = JSON.parse(jsonEl.innerText);
        }
    }
    if (window.hasOwnProperty('LevelToPage')) {
        return (window['LevelToPage'].hasOwnProperty(lvl)) ? window['LevelToPage'][lvl] : []
    }
    return []
}

const disableAddRemovePagesButton = () => {
    let r = document.querySelector('.removePagesForm .danger');
    if (r) {
        r.disabled = true
    }
    let a = document.querySelector('.addPagesForm .btn')
    if (a) {
        a.disabled = true
    }
}

const reenableAddRemovePagesButton = () => {
    if (findSelectedLevel()) {
        let r = document.querySelector('.removePagesForm .danger')
        if (r && document.querySelector('.removePagesForm .onePage') !== null) {
            r.disabled = false
        }
        let a = document.querySelector('.addPagesForm .btn')
        if (a && document.querySelector('.addPagesForm .onePage') !== null) {
            a.disabled = false
        }
    }
}

const reloadPagesToRemove = () => {

    let removeList = document.querySelector('.removePagesForm');
    if (!removeList) {
        return
    }
    let pages = levelToPages(findSelectedLevel())
    let tail = pages.reduce((a, one) => {
        return a + '&include[]=' + one;
    }, '');
    let inner = removeList.querySelector('.inner');
    inner.innerHTML = ''
    if (pages.length <= 0) {
        inner.insertAdjacentHTML('afterbegin','<p>Sekce/úroveň nemá přiřazené stránky.</p>')
        return
    }
    insertLoader(removeList)
    fetch('/?rest_route=/wp/v2/pages&per_page=100&context=embed' + tail).then(res => res.json()).then(items => {
        renderPagesForRemoval(inner, items)
        removeLoader(removeList)
        reenableAddRemovePagesButton()
    });
}

const recheckPagesToAdd = () => {

    let addList = document.querySelector('.addPagesForm');
    if (!addList) {
        return
    }
    let disable = levelToPages(findSelectedLevel())
    Array.from(addList.querySelectorAll('input[type="checkbox"]')).forEach((one) => {
        let id = parseInt(one.value)
        if (disable.indexOf(id) >= 0) {
            one.disabled = true
        } else {
            one.disabled = false
        }
    })

}

const renderPagesForRemoval = (el, items) => {
    let h = items.map((one) => {
        return `<div class="onePage"><input type="checkbox" name="toRemove[]" value="${one.id}"> ${one.title.rendered}</div>`
    })
    el.innerHTML = ''
    el.insertAdjacentHTML("beforeend", h.join(''))
    //el.insertAdjacentHTML("beforeend", ``)

}

const findSelectedLevel = () => {
    let sLi = document.querySelector('.levels li.selected')
    if (sLi) {
        return parseInt(sLi.getAttribute('data-id'))
    } else {
        return null
    }
}

const insertLoader = (el) => {
    el.classList.add('loading')
}

const removeLoader = (el) => {
    el.classList.remove('loading')
}

const doHideMembershipUntil = () => {
    let tables = document.querySelectorAll('.fapiMembership')
    if (tables.length <= 0) {
        return
    }
    Array.from(tables).forEach((table) => {
        let unlimitedInputs = table.querySelectorAll('.isUnlimitedInput')
        Array.from(unlimitedInputs).forEach((one) => {
            let name = one.getAttribute('name')
            let membershipDateName = name.replace('isUnlimited', 'membershipUntil')
            let mu = table.querySelector('[name="'+membershipDateName+'"]')
            let muLabel = table.querySelector('[data-for="'+membershipDateName+'"]')
            if (one.checked) {
                mu.classList.add('contentHidden')
                muLabel.classList.add('contentHidden')
            } else {
                mu.classList.remove('contentHidden')
                muLabel.classList.remove('contentHidden')
            }

        })
    })
}

document.addEventListener('click', (event) => {
    if (event.target.matches('.fapiMembership .isUnlimitedInput')) {
        doHideMembershipUntil()
    }
})

document.addEventListener('DOMContentLoaded', doHideMembershipUntil)

document.addEventListener('click', (event) => {
    if (event.target.matches('.levels .up') || event.target.matches('.levels .down')) {
        let direction = (event.target.matches('.levels .up')) ? 'up' : 'down';
        let id = event.target.parentNode.getAttribute('data-id');
        console.log(id)
        console.log(direction)
        let form = document.getElementById('LevelOrderForm')
        form.querySelector('[name="id"]').setAttribute('value', id)
        form.querySelector('[name="direction"]').setAttribute('value', direction)
        form.submit()
    }
})