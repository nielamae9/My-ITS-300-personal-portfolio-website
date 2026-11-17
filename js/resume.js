function createTimelineItem(title, date, detail, description) {
  const item = document.createElement('div');
  item.className = 'timeline-item';
  const heading = document.createElement('h3');
  heading.textContent = title;
  const dateLabel = document.createElement('span');
  dateLabel.className = 'timeline-date';
  dateLabel.textContent = date;
  const detailText = document.createElement('p');
  detailText.textContent = detail;
  item.append(heading, dateLabel, detailText);
  if (description) {
    const descriptionText = document.createElement('p');
    descriptionText.textContent = description;
    item.appendChild(descriptionText);
  }
  return item;
}

const educationList = document.querySelector('[data-education-list]');
const experienceList = document.querySelector('[data-experience-list]');
const achievementList = document.querySelector('[data-achievement-list]');

if (educationList && experienceList && achievementList) {
  Promise.all(['education', 'experience', 'achievements'].map(section =>
    fetch(`api/content.php?section=${section}`)
      .then(response => response.ok ? response.json() : Promise.reject(new Error(`${section} could not be loaded.`)))
      .then(data => Array.isArray(data.items) ? data.items : Promise.reject(new Error(`${section} response is invalid.`)))
  )).then(([education, experience, achievements]) => {
    educationList.replaceChildren(...education.map(item => createTimelineItem(
      item.program, [item.start_year, item.end_year].filter(Boolean).join(' – '), item.school, item.description
    )));
    experienceList.replaceChildren(...experience.map(item => createTimelineItem(
      item.title, item.date_label, item.organization, item.description
    )));
    achievementList.replaceChildren(...achievements.flatMap(item => {
      const button = document.createElement('button');
      button.className = 'accordion-btn';
      button.textContent = item.title;
      const content = document.createElement('div');
      content.className = 'accordion-content';
      const description = document.createElement('p');
      description.textContent = item.description;
      content.appendChild(description);
      button.addEventListener('click', () => {
        button.classList.toggle('active');
        content.classList.toggle('open');
        content.style.maxHeight = content.classList.contains('open') ? `${content.scrollHeight}px` : '0px';
      });
      return [button, content];
    }));
  }).catch(error => console.error(error));
}
