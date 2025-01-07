import {modal} from "./modal.js";
export function cancelParticipationModal(modalId, url, csrfToken) {
  const getModal = document.getElementById(modalId);
  const modalTitle = 'Cancel participation';
  const modalBody = '<p>Are you sure you want to cancel participation?</p>';
  const modalFooter = `<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <form class="d-flex align-items-center"
                      action=${url}
                      method="post">
                    <input type="hidden" name="token" value="${csrfToken}">
                    <button class="btn btn-danger" type="submit">Cancel</button>
                </form>`;

  modal(getModal, modalTitle, modalBody, modalFooter);
}
