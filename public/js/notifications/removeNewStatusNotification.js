export function removeNewStatusNotification() {
  const notificationWrapper = document.querySelector('div.notification-wrapper');
  notificationWrapper.addEventListener('mouseover', function (event) {
    if (event.currentTarget === this && event.target.closest('.list-group-item')) {
      const listElem = event.target.closest('.list-group-item');
      const newNotificationText = listElem.getElementsByClassName('text-danger')[0];
      if (newNotificationText) {
        // TODO: add fetch to change 'watched' status of the notification

        newNotificationText.remove();

        const notificationsButton = document.getElementById('notifications')
          .querySelector('button[type=button]');
        const newNotificationsCount = document.getElementById('notification-list').querySelectorAll('.list-group-item p.text-danger');

        if (notificationsButton.classList.contains('new-notification') && newNotificationsCount.length <= 0) {
          notificationsButton.classList.remove('new-notification');
        }
      }
    }
  })
}
