const messageForm = document.getElementById('contactMessageForm');
const contactToken = document.getElementById('contactToken');
const messageStatus = document.getElementById('successMsg');
const unavailableMessage = 'The contact form is temporarily unavailable. Please use the email link above.';

if (messageForm && contactToken && messageStatus) {
  let csrfToken = '';
  fetch('api/contact-token.php')
    .then(response => {
      if (!response.ok) throw new Error(unavailableMessage);
      return response.json();
    })
    .then(data => {
      if (!data || typeof data.csrf_token !== 'string') throw new Error(unavailableMessage);
      csrfToken = data.csrf_token;
      contactToken.value = csrfToken;
    })
    .catch(error => {
      console.error('Contact form token request failed:', error);
      messageStatus.textContent = unavailableMessage;
    });

  messageForm.addEventListener('submit', async event => {
    event.preventDefault();
    messageStatus.textContent = '';
    try {
      if (!csrfToken) throw new Error('Wait for the contact form to finish loading.');
      const response = await fetch('api/contact.php', { method: 'POST', body: new FormData(messageForm) });
      let result;
      try {
        result = await response.json();
      } catch (error) {
        console.error('Contact form returned a non-JSON response:', error);
        messageStatus.textContent = unavailableMessage;
        return;
      }
      if (!response.ok) {
        messageStatus.textContent = typeof result.error === 'string' ? result.error : unavailableMessage;
        return;
      }
      if (typeof result.message !== 'string') throw new Error(unavailableMessage);
      messageStatus.textContent = result.message;
      messageForm.reset();
      contactToken.value = csrfToken;
    } catch (error) {
      if (error.message !== 'Wait for the contact form to finish loading.') {
        console.error('Contact form submission failed:', error);
      }
      messageStatus.textContent = error.message === 'Wait for the contact form to finish loading.'
        ? error.message : unavailableMessage;
    }
  });
}
