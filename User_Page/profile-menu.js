(() => {
  const container = document.getElementById('avatar-container');
  const trigger = document.getElementById('avatar-trigger');
  const dropdown = document.getElementById('avatar-dropdown');

  if(!container || !trigger || !dropdown){
    return;
  }

  trigger.addEventListener('click', (event) => {
    event.stopPropagation();
    const isOpen = dropdown.classList.toggle('show');
    trigger.setAttribute('aria-expanded', String(isOpen));
  });

  dropdown.addEventListener('click', (event) => event.stopPropagation());

  document.addEventListener('click', (event) => {
    if(!container.contains(event.target)){
      dropdown.classList.remove('show');
      trigger.setAttribute('aria-expanded', 'false');
    }
  });

  document.addEventListener('keydown', (event) => {
    if(event.key === 'Escape'){
      dropdown.classList.remove('show');
      trigger.setAttribute('aria-expanded', 'false');
      trigger.focus();
    }
  });
})();
