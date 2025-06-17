/**
 * Certificate Management System - Main Controller
 * @author CKTech Team
 * @version 2.0
 */

class CertificateManager {
  constructor() {
    this.teacherId = window.teacherId || null;
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
    //   console.error('Initialization error:', error);
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
    } catch (error) {
    //   console.error('Error loading term info:', error);
    }
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
        // console.error('List error:', result.message);
      }
      
      this.tableManager.renderTable(this.certificatesData);
    } catch (error) {
    //   console.error('Error loading certificates:', error);
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
      }    } catch (error) {
    //   console.error('Error loading statistics:', error);
    }
  }
  setupModalFocusTrap() {
    const modal = document.getElementById('modalAddCertificate');
    
    // ป้องกันการกด Escape และการคลิกนอก modal
    document.addEventListener('keydown', (e) => {
      if (!modal.classList.contains('hidden')) {
        // ป้องกันการกด Escape
        if (e.key === 'Escape') {
          e.preventDefault();
          e.stopPropagation();
          return false;
        }
        
        // จัดการ Tab navigation ให้อยู่ใน modal เท่านั้น
        if (e.key === 'Tab') {
          const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
          );
          const focusableArray = Array.from(focusableElements);
          const firstElement = focusableArray[0];
          const lastElement = focusableArray[focusableArray.length - 1];
          
          if (e.shiftKey) {
            // Shift + Tab
            if (document.activeElement === firstElement) {
              e.preventDefault();
              lastElement.focus();
            }
          } else {
            // Tab
            if (document.activeElement === lastElement) {
              e.preventDefault();
              firstElement.focus();
            }
          }
        }
      }
    });

    // ป้องกันการคลิกนอก modal อย่างสมบูรณ์
    modal.addEventListener('click', (e) => {
      // หยุดการ propagation ทุก event
      e.stopPropagation();
      e.preventDefault();
      
      // ถ้าคลิกที่ background ของ modal (ไม่ใช่เนื้อหา)
      if (e.target === modal) {
        return false;
      }
    });

    // ป้องกัน event bubbling จาก document
    document.addEventListener('click', (e) => {
      if (!modal.classList.contains('hidden')) {
        // ถ้า modal เปิดอยู่และคลิกที่ไหนก็ตาม ให้ตรวจสอบว่าอยู่ใน modal หรือไม่
        if (!modal.contains(e.target)) {
          e.preventDefault();
          e.stopPropagation();
          return false;
        }
      }
    }, true); // ใช้ capture phase
  }

  initEventHandlers() {
    // Modal focus management
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
