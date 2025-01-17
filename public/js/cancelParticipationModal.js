import {modal} from "./modal.js";
export function cancelParticipationModal(
  modalId,
  url,
  csrfToken,
  titleName = 'Cancel participation',
  body = 'Are you sure you want to cancel participation?',
  buttonName = 'Cancel'
  ) {
  const getModal = document.getElementById(modalId);
  const modalTitle = titleName.trim();
  const modalBody = `<p>${body.trim()}</p>`;
  const modalFooter = `<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <form class="d-flex align-items-center"
                      action=${url}
                      method="post">
                    <input type="hidden" name="token" value="${csrfToken}">
                    <button class="btn btn-danger" type="submit">${buttonName.trim()}</button>
                </form>`;

  modal(getModal, modalTitle, modalBody, modalFooter);
}
