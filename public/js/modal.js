export function modal(modal, modalTitle, modalBody, modalFooter) {
  removeEvents(modal);
  fillModalWindow(modal, modalTitle, modalBody, modalFooter);
}

function fillModalWindow(modal, modalTitle, modalBody, modalFooter) {
  modal.querySelector('.modal-title').insertAdjacentText('afterbegin', modalTitle);
  modal.querySelector('.modal-body').insertAdjacentHTML('afterbegin', modalBody);
  modal.querySelector('.modal-footer').insertAdjacentHTML('afterbegin', modalFooter);
}

function removeEvents(modal) {
  function clickHandler(event) {
    if (
      event.target.dataset.dismiss === 'modal' ||
      event.target.parentElement.dataset.dismiss === 'modal' ||
      event.target.id === 'modal'
    ) {
      removeModalData(modal);
    }

    document.removeEventListener('click', clickHandler);
  }

  function keyupHandler(event) {
    if (event.key === 'Escape') {
      removeModalData(modal);
    }

    document.removeEventListener('keyup', keyupHandler);
  }

  document.addEventListener('click', clickHandler);
  document.addEventListener('keyup', keyupHandler);
}

function removeModalData(modal) {
  modal.querySelector('.modal-title').innerHTML = '';
  modal.querySelector('.modal-body').innerHTML = '';
  modal.querySelector('.modal-footer').innerHTML = '';
}
