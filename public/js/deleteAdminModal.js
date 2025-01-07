import {modal} from "./modal.js";
export function deleteAdminModal(modalId, url, csrfToken) {
  const getModal = document.getElementById(modalId);
  const modalTitle = 'Delete';
  const modalBody = '<p>Are you sure you want to delete?</p>';
  const modalFooter = `<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <form class="d-flex align-items-center"
                      action=${url}
                      method="post">
                    <input type="hidden" name="token" value="${csrfToken}">
                    <button class="btn btn-danger" type="submit">Delete</button>
                </form>`;
  modal(getModal, modalTitle, modalBody, modalFooter);
}
