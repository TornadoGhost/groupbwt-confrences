export function show() {
  const notification = document.getElementById('notifications')
  const notificationButton = notification.querySelector('button[type=button]');
  const notificationList = document.getElementById('notification-list');
  notificationButton.addEventListener('mousedown', function (event) {
    if (event.currentTarget === this) {
      const notificationWrapper = notificationList.parentElement;
      if (notificationWrapper.classList.contains('d-none')) {
        notificationWrapper.classList.remove('d-none');
        notificationWrapper.classList.add('d-block');
      } else {
        notificationWrapper.classList.remove('d-block');
        notificationWrapper.classList.add('d-none');
      }
    }
  });

  document.addEventListener('mousedown', function (event) {
    if (!notification.contains(event.target) && !notificationList.classList.contains('d-none')) {
      if (notificationList.parentElement) {
        notificationList.parentElement.classList.remove('d-block');
        notificationList.parentElement.classList.add('d-none');
      }
    }
  })
}
