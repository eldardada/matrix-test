import {Modal as BsModal} from 'bootstrap';

window.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#form-text');
    const modal = document.querySelector('#modal-response');
    const bsModal = new BsModal(modal);

    form.addEventListener('submit', formOnClick);

    function formOnClick(event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch('/form-text/create', {
            method: 'post',
            body: formData
        })
            .then(res => {
                return res.json()
            })
            .then(({data}) => {
                const modalBody = modal.querySelector('#modal-body');
                const modalTitle = modal.querySelector('#modal-title');

                modalTitle.textContent = data.error ? 'Ошибка' : 'Успех!';
                modalBody.textContent = data.message;

                bsModal.show();
            })
            .catch(e => {
                console.log(e)
            })
    }


    function printError() {

    }
});
