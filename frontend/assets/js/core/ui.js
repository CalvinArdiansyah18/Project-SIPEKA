/**
 * SIPEKA UI Utilities & Component Helpers
 * Menyediakan sistem Toast notification, Modal controller, dan render generator.
 */

// Toast Notifications
function showToast(message, type = 'success') {
  let toastContainer = document.getElementById('toast-container');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'toast-container';
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);
  }

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;

  const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
  toast.innerHTML = `
    <span class="toast-icon">${icon}</span>
    <span class="toast-message">${message}</span>
  `;

  toastContainer.appendChild(toast);

  // Trigger animation
  setTimeout(() => toast.classList.add('show'), 10);

  // Remove toast after 3.5 seconds
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

// Modal Controllers
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

// Status Badge Generator
function getStatusBadge(status) {
  switch (status) {
    case 'Menunggu Validasi':
      return `<span class="badge badge-warning">Menunggu Validasi</span>`;
    case 'Dalam Penanganan':
      return `<span class="badge badge-info">Dalam Penanganan</span>`;
    case 'Selesai':
      return `<span class="badge badge-success">Selesai</span>`;
    case 'Tidak Valid':
      return `<span class="badge badge-danger">Tidak Valid</span>`;
    // Kode status dari database (enum status_laporan)
    case 'menunggu':
      return `<span class="badge badge-warning">Menunggu Validasi</span>`;
    case 'diproses':
      return `<span class="badge badge-info">Diproses</span>`;
    case 'dalam_penanganan':
      return `<span class="badge badge-info">Dalam Penanganan</span>`;
    case 'selesai':
      return `<span class="badge badge-success">Selesai</span>`;
    case 'ditolak':
      return `<span class="badge badge-danger">Ditolak</span>`;
    default:
      return `<span class="badge">${status}</span>`;
  }
}

// Format Date
function formatDate(dateStr) {
  if (!dateStr) return '-';
  try {
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    });
  } catch (e) {
    return dateStr;
  }
}

// Setup Global Click Listener for Modal Closers & Mobile Drawer
document.addEventListener('DOMContentLoaded', () => {
  // Mobile Drawer Toggle
  const hamburgerBtn = document.querySelector('.hamburger-btn');
  const sidebar = document.querySelector('.app-sidebar');
  const backdrop = document.querySelector('.sidebar-backdrop');

  if (hamburgerBtn && sidebar) {
    hamburgerBtn.addEventListener('click', () => {
      sidebar.classList.toggle('active');
      if (backdrop) backdrop.classList.toggle('active');
    });
  }

  if (backdrop && sidebar) {
    backdrop.addEventListener('click', () => {
      sidebar.classList.remove('active');
      backdrop.classList.remove('active');
    });
  }
});

document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-backdrop') || e.target.classList.contains('modal-close-btn')) {
    const activeModal = document.querySelector('.modal.active');
    if (activeModal) closeModal(activeModal.id);
  }
});