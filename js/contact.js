const messageForm = document.getElementById('contactMessageForm');
const contactToken = document.getElementById('contactToken');
const messageStatus = document.getElementById('successMsg');

if (messageForm && contactToken && messageStatus) {
  let csrfToken = '';
  fetch('api/contact-token.php')
    .then(response => response.ok ? response.json() : Promise.reject(new Error('The contact form is unavailable.')))
    .then(data => {
      if (typeof data.csrf_token !== 'string') throw new Error('The contact form token is missing.');
      csrfToken = data.csrf_token;
      contactToken.value = csrfToken;
    })
    .catch(error => { messageStatus.textContent = error.message; });

  messageForm.addEventListener('submit', async event => {
    event.preventDefault();
    messageStatus.textContent = '';
    try {
      if (!csrfToken) throw new Error('Wait for the contact form to finish loading.');
      const response = await fetch('api/contact.php', { method: 'POST', body: new FormData(messageForm) });
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'The message could not be sent.');
      messageStatus.textContent = result.message;
      messageForm.reset();
      contactToken.value = csrfToken;
    } catch (error) {
      messageStatus.textContent = error.message;
    }
  });
}
