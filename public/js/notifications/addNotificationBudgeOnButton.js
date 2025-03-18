export function addNotificationBudgeOnButton() {
  const notificationsButton = document.getElementById('notifications')
    .querySelector('button[type=button]');
  if (!notificationsButton.classList.contains('new-notification')) {
    notificationsButton.classList.add('new-notification');
  }
}
