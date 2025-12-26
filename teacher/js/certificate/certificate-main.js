/**
 * Certificate Management System - Main Controller
 * @author CKTech Team
 * @version 2.0
 */

class CertificateManager {
  constructor() {
    this.teacherId = (window.CERTIFICATE_CONFIG && window.CERTIFICATE_CONFIG.teacherId) || window.teacherId || null;
    this.certificatesData = [];
    this.currentTermInfo = null;

    // Initialize modules
    this.formHandler = new CertificateFormHandler(this);
    this.tableManager = new CertificateTableManager(this);
    this.filterManager = new CertificateFilterManager(this);
    this.statsManager = new CertificateStatsManager(this);
    this.exportManager = new CertificateExportManager(this);

    this.init();
  }

  async init() {
    try {
      await this.loadCurrentTermInfo();
      this.initEventHandlers();
      await Promise.all([
        this.loadCertificates(),
        this.loadStatistics()
      ]);
      this.filterManager.init();
    } catch (error) {
      this.showError('เกิดข้อผิดพลาดในการเริ่มต้นระบบ');
    }
  }

  async loadCurrentTermInfo() {
    try {
      const response = await fetch('../controllers/CertificateController.php?action=termInfo');
      const result = await response.json();
      if (result.success) {
        this.currentTermInfo = result.data;
        this.displayCurrentTermInfo();
      }
    } catch (error) { }
  }

  displayCurrentTermInfo() {
    if (this.currentTermInfo) {
      const termInfoElement = document.createElement('div');
      termInfoElement.className = 'text-sm text-blue-600 bg-blue-50 px-3 py-1 rounded-full';
      termInfoElement.innerHTML = `📚 ภาคเรียนที่ ${this.currentTermInfo.term} ปีการศึกษา ${this.currentTermInfo.year}`;

      const headerElement = document.querySelector('.content-header h1');
      if (headerElement) {
        headerElement.appendChild(termInfoElement);
      }
    }
  }

  async loadCertificates() {
    this.tableManager.showLoadingState();

    try {
      const response = await fetch(`../controllers/CertificateController.php?action=list&teacherId=${encodeURIComponent(this.teacherId)}`);
      const result = await response.json();

      if (result.success) {
        this.certificatesData = Array.isArray(result.data) ? result.data : [];
      } else {
        this.certificatesData = [];
      }

      this.tableManager.renderTable(this.certificatesData);
    } catch (error) {
      this.tableManager.showErrorState();
    } finally {
      this.tableManager.hideLoadingState();
    }
  }

  async loadStatistics() {
    try {
      const response = await fetch(`../controllers/CertificateController.php?action=statistics&teacherId=${encodeURIComponent(this.teacherId)}`);
      const result = await response.json();
      if (result.success) {
        this.statsManager.updateDisplay(result.data);
      }
    } catch (error) { }
  }

  setupModalFocusTrap() {
    const modal = document.getElementById('modalAddCertificate');
    if (!modal) return;

    document.addEventListener('keydown', (e) => {
      if (!modal.classList.contains('hidden')) {
        if (e.key === 'Tab') {
          const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
          );
          const focusableArray = Array.from(focusableElements);
          if (focusableArray.length > 0) {
            const firstElement = focusableArray[0];
            const lastElement = focusableArray[focusableArray.length - 1];

            if (e.shiftKey) {
              if (document.activeElement === firstElement) {
                e.preventDefault();
                lastElement.focus();
              }
            } else {
              if (document.activeElement === lastElement) {
                e.preventDefault();
                firstElement.focus();
              }
            }
          }
        }
      }
    });
  }

  initEventHandlers() {
    this.setupModalFocusTrap();

    // Statistics toggle
    const btnStats = document.getElementById('btnStats');
    const statsCards = document.getElementById('statsCards');

    btnStats?.addEventListener('click', () => {
      if (statsCards.classList.contains('hidden')) {
        statsCards.classList.remove('hidden');
        statsCards.style.animation = 'fadeIn 0.5s ease-out';
        btnStats.innerHTML = '<i class="fas fa-chart-line mr-2"></i>ซ่อนสถิติ';
      } else {
        statsCards.classList.add('hidden');
        btnStats.innerHTML = '<i class="fas fa-chart-bar mr-2"></i>สถิติรางวัล';
      }
    });

    // Export button
    document.getElementById('btnExport')?.addEventListener('click', () => {
      this.exportManager.showExportDialog();
    });

    // Refresh button
    const btnRefresh = document.getElementById('btnRefresh');
    btnRefresh?.addEventListener('click', () => {
      const icon = btnRefresh.querySelector('i');
      icon?.classList.add('fa-spin');

      Promise.all([this.loadCertificates(), this.loadStatistics()])
        .finally(() => {
          setTimeout(() => {
            icon?.classList.remove('fa-spin');
          }, 500);
        });
    });

    // Add certificate button
    document.getElementById('btnAddCertificate')?.addEventListener('click', () => {
      this.formHandler.showModal();
    });
  }

  // Utility methods
  showSuccess(message, timer = 3000) {
    Swal.fire({
      title: 'สำเร็จ',
      html: message,
      icon: 'success',
      timer: timer,
      showConfirmButton: false
    });
  }

  showError(message) {
    Swal.fire({
      title: 'ข้อผิดพลาด',
      text: message,
      icon: 'error'
    });
  }

  showLoading(title = 'กำลังประมวลผล...') {
    Swal.fire({
      title: title,
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });
  }

  closeLoading() {
    Swal.close();
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  window.certificateManager = new CertificateManager();
});
