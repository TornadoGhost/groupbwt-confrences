import {modal} from "./modal.js";
export function modalDeleteComment(modalId, conferenceId, reportId, commentId, csrfToken) {
  const getModal = document.getElementById(modalId);
  const modalTitle = 'Delete comment';
  const modalBody = '<p>Are you sure you want to delete this comment?</p>';
  const modalFooter = `<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <form class="d-inline" method="post"
                    action="/conferences/${conferenceId}/reports/${reportId}/comments/${commentId}/delete">
                    <input type="hidden" name="_token"
                           value="${csrfToken}">
                    <button class="btn btn-danger" type="submit">Delete</button>
                </form>`;

  modal(getModal, modalTitle, modalBody, modalFooter);
}
