/* main.js — scripts JavaScript Dashboard ensiasd  */

document.addEventListener('DOMContentLoaded', function () {

  // auto-fermeture des alertes après 4 secondes
  document.querySelectorAll('.alert:not(.alert-permanent)').forEach(function (alert) {
    setTimeout(function () {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      if (bsAlert) bsAlert.close();
    }, 4000);
  });

  // activer les tooltips Bootstrap
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
    new bootstrap.Tooltip(el);
  });

  // confirmation de suppression améliorée
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!confirm(form.dataset.confirm || 'Confirmer cette action ?')) {
        e.preventDefault();
      }
    });
  });

  // highlight ligne au survol des tableaux
  document.querySelectorAll('.table tbody tr').forEach(function (row) {
    row.style.cursor = 'default';
  });

  // sidebar toggle pour mobile
  const toggleBtn = document.getElementById('sidebarToggle');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      const sidebar = document.querySelector('.sidebar');
      if (sidebar) sidebar.classList.toggle('show');
    });
  }

  // charts Chart.js 
  if (window.Chart) {
    Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#6c757d';
    Chart.defaults.plugins.legend.labels.boxWidth = 12;
  }

});
