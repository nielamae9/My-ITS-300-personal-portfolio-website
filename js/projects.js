const dynamicProjectsGrid = document.getElementById('projectsGrid');

function createProjectCard(project) {
  const card = document.createElement('article');
  card.className = 'project-card';
  card.dataset.category = project.category.trim().toLowerCase();

  const image = document.createElement('img');
  image.className = 'project-img';
  image.alt = project.title;
  image.src = typeof project.image_path === 'string'
    && /^(images|uploads)\/[a-zA-Z0-9._-]+\.(jpe?g|png|gif|webp)$/i.test(project.image_path)
    ? project.image_path : 'images/project1.jpg';

  const info = document.createElement('div');
  info.className = 'project-info';
  const title = document.createElement('h3');
  title.textContent = project.title;
  const description = document.createElement('p');
  description.textContent = project.description;
  const tags = document.createElement('div');
  tags.className = 'tech-tags';

  const technologies = typeof project.technologies === 'string' ? project.technologies.split(',') : [];
  technologies.filter(technology => technology.trim() !== '').forEach(technology => {
    const tag = document.createElement('span');
    tag.textContent = technology.trim();
    tags.appendChild(tag);
  });

  info.append(title, description, tags);
  card.append(image, info);
  return card;
}

if (dynamicProjectsGrid) {
  fetch('api/content.php?section=projects')
    .then(response => response.ok ? response.json() : Promise.reject(new Error('Projects could not be loaded.')))
    .then(data => {
      if (!Array.isArray(data.items)) throw new Error('The projects response is invalid.');
      const projects = data.items.filter(project =>
        project && typeof project.title === 'string'
        && typeof project.description === 'string' && typeof project.category === 'string'
        && project.category.trim() !== ''
      );
      if (!projects.length) {
        dynamicProjectsGrid.textContent = 'No projects have been added yet.';
        return;
      }
      dynamicProjectsGrid.replaceChildren(...projects.map(createProjectCard));
    })
    .catch(error => console.error(error));
}
