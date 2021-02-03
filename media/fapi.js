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
                console.log('removing', id)
                let form = document.getElementById('LevelRemoveForm')
                form.querySelector('[name="level_id"]').setAttribute('value', id)
                form.submit()
            } else if (result.isDenied) {
                // none
            }
        })
    }
})