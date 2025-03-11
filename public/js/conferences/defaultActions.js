import {cancelParticipationModal} from "../cancelParticipationModal.js";
import {getConferenceId} from "../getConferenceId.js";
import {deleteAdminModal} from "../deleteAdminModal.js";

export function defaultActions(deleteCsrfToken, cancelParticipationCsrfToken) {
  document.addEventListener('mousedown', function (event) {
    if (
      event.target.dataset.conference && !event.target.dataset.admin
      ||
      event.target.parentNode.dataset.conference && !event.target.parentNode.dataset.admin
    ) {
      const conferenceId = getConferenceId(event);
      const url = `https://localhost/conferences/${conferenceId}/cancel`;
      cancelParticipationModal('modal', url, cancelParticipationCsrfToken);
    } else if (event.target.dataset.admin === 'delete' || event.target.parentNode.dataset.admin === 'delete') {
      const conferenceId = getConferenceId(event)
      const url = `https://localhost/conferences/${conferenceId}/delete`;
      deleteAdminModal('modal', url, deleteCsrfToken)
    }
  });
}
