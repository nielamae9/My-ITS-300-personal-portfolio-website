const profileName = document.querySelector('[data-profile-name]');
const profileBiography = document.querySelector('[data-profile-biography]');
const profileImage = document.querySelector('[data-profile-image]');

if (profileName && profileBiography && profileImage) {
  fetch('api/content.php?section=profile')
    .then(response => response.ok ? response.json() : Promise.reject(new Error('Profile could not be loaded.')))
    .then(data => {
      const profile = data.profile;
      if (!profile || typeof profile.full_name !== 'string' || typeof profile.biography !== 'string') {
        throw new Error('The profile response is invalid.');
      }
      profileName.textContent = `Hello! I'm ${profile.full_name}`;
      profileBiography.textContent = profile.biography;
      profileImage.alt = profile.full_name;
      if (typeof profile.profile_image === 'string' && /^(images|uploads)\/[a-zA-Z0-9._-]+\.(jpe?g|png|gif|webp)$/i.test(profile.profile_image)) {
        profileImage.src = profile.profile_image;
      }
    })
    .catch(error => console.error(error));
}
