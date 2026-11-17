const toolsGrid = document.querySelector('[data-dynamic-tools]');
if (toolsGrid) {
  fetch('api/content.php?section=skills')
    .then(response => response.ok ? response.json() : Promise.reject(new Error('Tools could not be loaded.')))
    .then(data => {
      if (!Array.isArray(data.items)) throw new Error('The tools response is invalid.');
      const tools = data.items.filter(skill => skill.category === 'Tools').map(skill => {
        if (typeof skill.name !== 'string' || skill.name.trim() === '') {
          throw new Error('A tool has an invalid name.');
        }
        const item = document.createElement('div');
        item.className = 'tool-card';
        item.textContent = skill.name;
        return item;
      });
      toolsGrid.replaceChildren(...tools);
    })
    .catch(error => console.error(error));
}
