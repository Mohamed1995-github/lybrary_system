/* Simple modal manager */
function openModal(id) {
  const m = document.getElementById(id);
  if (!m) return;
  m.style.display = 'block';
  const o = document.getElementById('menuOverlay');
  if (o) o.style.display = 'block';
  document.body.style.overflow = 'hidden';
}

function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.style.display = 'none';
  const o = document.getElementById('menuOverlay');
  if (o) o.style.display = 'none';
  document.body.style.overflow = 'auto';
}

function closeAllModals() {
  document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
  const o = document.getElementById('menuOverlay');
  if (o) o.style.display = 'none';
  document.body.style.overflow = 'auto';
}

// Delegated handler for elements using data-modal
document.addEventListener('click', function(e) {
  const el = e.target.closest('[data-modal]');
  if (!el) return;
  const id = el.dataset.modal;
  if (id) openModal(id);
});
