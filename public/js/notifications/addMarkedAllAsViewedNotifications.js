import {markAsViewedNotification} from "./markAsViewedNotification.js";

export function addMarkedAllAsViewedNotifications() {
  const btn = document.createElement('button');
  btn.classList.add('btn', 'btn-sm', 'btn-light');
  btn.addEventListener('click', function() {
    markAsViewedNotification();
  });

  return btn;
}
