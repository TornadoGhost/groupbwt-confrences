export function modal(modal, modalTitle, modalBody, modalFooter) {
  fillModalWindow(modal, modalTitle, modalBody, modalFooter);
  removeEvents(modal);
}

function fillModalWindow(modal, modalTitle, modalBody, modalFooter) {
  modal.querySelector('.modal-title').insertAdjacentText('afterbegin', modalTitle);
  modal.querySelector('.modal-body').insertAdjacentHTML('afterbegin', modalBody);
  modal.querySelector('.modal-footer').insertAdjacentHTML('afterbegin', modalFooter);
}

function removeEvents(modal) {
  document.addEventListener('click', function (event) {
    if (
      event.target.dataset.dismiss === 'modal' ||
      event.target.parentElement.dataset.dismiss === 'modal' ||
      event.target.id === 'modal'
    ) {
      removeModalData(modal);
    }
  });

  document.addEventListener('keyup', function (event) {
    if (event.key === 'Escape') {
      removeModalData(modal);
    }
  });
}

function removeModalData(modal) {
  setTimeout(function () {
    modal.querySelector('.modal-title').innerHTML = '';
    modal.querySelector('.modal-body').innerHTML = '';
    modal.querySelector('.modal-footer').innerHTML = '';
  }, 500);
}
