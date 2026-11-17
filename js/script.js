
const themeToggle = document.getElementById('themeToggle');
const body = document.body;
fetch('api/content.php?section=settings')
  .then(response => response.ok ? response.json() : Promise.reject(new Error('Website settings could not be loaded.')))
  .then(data => {
    if (!data.settings || typeof data.settings.site_title !== 'string' || typeof data.settings.footer_text !== 'string') {
      throw new Error('The website settings response is invalid.');
    }
    const pageTitle = document.title.split(' | ')[0].trim();
    document.title = pageTitle === data.settings.site_title
      ? data.settings.site_title : `${pageTitle} | ${data.settings.site_title}`;
    document.querySelectorAll('.footer > p').forEach(footer => { footer.textContent = data.settings.footer_text; });
  })
  .catch(error => console.error(error));

if (localStorage.getItem('theme') === 'dark') {
  body.classList.add('dark');
  if (themeToggle) themeToggle.textContent = '☀️';
}

if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    body.classList.toggle('dark');
    const isDark = body.classList.contains('dark');
    themeToggle.textContent = isDark ? '☀️' : '🌙';
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
  });
}


const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('navMenu');

if (hamburger && navMenu) {
  hamburger.addEventListener('click', () => {
    navMenu.classList.toggle('open');
  });
  navMenu.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => navMenu.classList.remove('open'));
  });
}


const typedText = document.getElementById('typed-text');
if (typedText) {
  const phrases = [
    typedText.dataset.headline || 'IT Student and Aspiring UI/UX Designer',
    'Aspiring UI/UX Designer',
    'Web Enthusiast',
    'Creative Thinker'
  ];
  let phraseIndex = 0;
  let charIndex = 0;
  let isDeleting = false;

  function type() {
    const current = phrases[phraseIndex];
    if (isDeleting) {
      typedText.textContent = current.substring(0, charIndex--);
    } else {
      typedText.textContent = current.substring(0, charIndex++);
    }

    let speed = isDeleting ? 60 : 120;

    if (!isDeleting && charIndex === current.length + 1) {
      speed = 1500;
      isDeleting = true;
    } else if (isDeleting && charIndex === 0) {
      isDeleting = false;
      phraseIndex = (phraseIndex + 1) % phrases.length;
      speed = 400;
    }
    setTimeout(type, speed);
  }
  type();
}


const scrollBtn = document.getElementById('scrollTop');
if (scrollBtn) {
  window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
      scrollBtn.classList.add('show');
    } else {
      scrollBtn.classList.remove('show');
    }
  });
  scrollBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}


const skillFills = document.querySelectorAll('.fill');
if (skillFills.length > 0) {
  function animateSkills() {
    skillFills.forEach(fill => {
      const rect = fill.getBoundingClientRect();
      if (rect.top < window.innerHeight - 50) {
        fill.style.width = fill.dataset.width;
      }
    });
  }
  window.addEventListener('scroll', animateSkills);
  window.addEventListener('load', animateSkills);
}


const filterBtns = document.querySelectorAll('.filter-btn');

if (filterBtns.length > 0) {
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;

      document.querySelectorAll('.project-card').forEach(card => {
        if (filter === 'all' || card.dataset.category === filter) {
          card.style.display = 'block';
          setTimeout(() => { card.style.opacity = '1'; }, 10);
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
}
const modal = document.getElementById('imageModal');
const modalImg = document.getElementById('modalImg');
const modalClose = document.getElementById('modalClose');
const projectsGrid = document.getElementById('projectsGrid');

if (modal && projectsGrid) {
  projectsGrid.addEventListener('click', event => {
    const image = event.target.closest('.project-img');
    if (image) {
      modal.classList.add('active');
      modalImg.src = image.src;
    }
  });
  if (modalClose) modalClose.addEventListener('click', () => modal.classList.remove('active'));
  modal.addEventListener('click', (e) => {
    if (e.target === modal) modal.classList.remove('active');
  });
}


const accordionBtns = document.querySelectorAll('.accordion-btn');
accordionBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    const content = btn.nextElementSibling;
    btn.classList.toggle('active');
    if (content.style.maxHeight && content.style.maxHeight !== '0px') {
      content.style.maxHeight = '0px';
      content.classList.remove('open');
    } else {
      content.style.maxHeight = content.scrollHeight + 'px';
      content.classList.add('open');
    }
  });
});


const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const message = document.getElementById('message');
    const nameError = document.getElementById('nameError');
    const emailError = document.getElementById('emailError');
    const messageError = document.getElementById('messageError');
    const successMsg = document.getElementById('successMsg');

    let isValid = true;
    nameError.textContent = '';
    emailError.textContent = '';
    messageError.textContent = '';
    successMsg.textContent = '';
    if (name.value.trim().length < 2) {
      nameError.textContent = 'Please enter your name (at least 2 characters).';
      isValid = false;
    }
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email.value.trim())) {
      emailError.textContent = 'Please enter a valid email address.';
      isValid = false;
    }
    if (message.value.trim().length < 10) {
      messageError.textContent = 'Message must be at least 10 characters.';
      isValid = false;
    }

    if (isValid) {
      successMsg.textContent = '✅ Message sent successfully! Thank you, ' + name.value + '.';
      contactForm.reset();
      setTimeout(() => { successMsg.textContent = ''; }, 5000);
    }
  });
}