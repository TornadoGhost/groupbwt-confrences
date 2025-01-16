export function getConferenceId(event) {
  let conferenceId;
  const idByButton = event.target.dataset.conference;
  const idByIcon = event.target.parentNode.dataset.conference;;

  if (idByButton) {
    conferenceId = idByButton;
  } else if (idByIcon) {
    conferenceId = idByIcon;
  }

  return conferenceId;
}
