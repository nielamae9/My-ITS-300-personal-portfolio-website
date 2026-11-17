const skillBars = document.querySelector('[data-dynamic-skills]');
if (skillBars) {
  fetch('api/content.php?section=skills')
    .then(response => response.ok ? response.json() : Promise.reject(new Error('Skills could not be loaded.')))
    .then(data => {
      if (!Array.isArray(data.items)) throw new Error('The skills response is invalid.');
      const skills = data.items.filter(skill => skill.category === 'Technical').map(skill => {
        const score = Number(skill.proficiency);
        if (skill.proficiency === null || !Number.isInteger(score) || score < 0 || score > 100) {
          throw new Error('A technical skill has invalid proficiency.');
        }
        const item = document.createElement('div');
        item.className = 'skill';
        item.innerHTML = '<div class="skill-head"><span></span><span></span></div><div class="bar"><div class="fill"></div></div>';
        const labels = item.querySelectorAll('.skill-head span');
        labels[0].textContent = skill.name;
        labels[1].textContent = `${score}%`;
        item.querySelector('.fill').dataset.width = `${score}%`;
        return item;
      });
      skillBars.replaceChildren(...skills);
    })
    .catch(error => console.error(error));
}
