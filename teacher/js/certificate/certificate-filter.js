/**
 * Certificate Filter Manager
 * Handles search and filtering functionality
 */

class CertificateFilterManager {
  constructor(manager) {
    this.manager = manager;
    this.searchTimeout = null;
    this.initElements();
  }

  initElements() {
    this.searchInput = document.getElementById('searchStudent');
    this.filterClass = document.getElementById('filterClass');
    this.filterAward = document.getElementById('filterAward');
    this.filterTerm = document.getElementById('filterTerm');
    this.filterYear = document.getElementById('filterYear');
    this.btnClearFilter = document.getElementById('btnClearFilter');
  }

  init() {
    this.loadAvailableTermsAndYears();
    this.bindEvents();
  }

  bindEvents() {
    // Debounced search
    this.searchInput?.addEventListener('input', () => {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        const searchTerm = this.searchInput.value.trim();
        if (searchTerm.length >= 2) {
          this.performSearch(searchTerm);
        } else if (searchTerm.length === 0) {
          this.manager.loadCertificates();
        }
      }, 500);
    });

    // Filter events
    this.filterClass?.addEventListener('change', () => this.applyFilters());
    this.filterAward?.addEventListener('change', () => this.applyFilters());
    this.filterTerm?.addEventListener('change', () => this.applyFilters());
    this.filterYear?.addEventListener('change', () => this.applyFilters());

    // Clear filter
    this.btnClearFilter?.addEventListener('click', () => {
      this.clearAllFilters();
    });
  }

  async loadAvailableTermsAndYears() {
    try {
      const response = await fetch(`../controllers/CertificateController.php?action=availableTerms&teacherId=${encodeURIComponent(this.manager.teacherId)}`);
      const result = await response.json();
      
      if (result.success && Array.isArray(result.data)) {
        this.populateTermYearDropdowns(result.data);
      }
    } catch (error) {
    }
  }

  populateTermYearDropdowns(data) {
    const terms = new Set();
    const years = new Set();
    
    data.forEach(item => {
      if (item.term) terms.add(item.term);
      if (item.year) years.add(item.year);
    });

    // Populate term dropdown
    if (this.filterTerm) {
      this.filterTerm.innerHTML = '<option value="">ทุกภาคเรียน</option>';
      Array.from(terms).sort().forEach(term => {
        this.filterTerm.innerHTML += `<option value="${term}">${term}</option>`;
      });
    }

    // Populate year dropdown
    if (this.filterYear) {
      this.filterYear.innerHTML = '<option value="">ทุกปี</option>';
      Array.from(years).sort((a, b) => b - a).forEach(year => {
        this.filterYear.innerHTML += `<option value="${year}">${year}</option>`;
      });
    }
  }

  async performSearch(searchTerm) {
    try {
      const response = await fetch(`../controllers/CertificateController.php?action=search&term=${encodeURIComponent(searchTerm)}&teacherId=${encodeURIComponent(this.manager.teacherId)}`);
      const result = await response.json();
      
      if (result.success) {
        this.manager.certificatesData = result.data;
        this.manager.tableManager.renderTable(this.manager.certificatesData);
        
        if (result.count === 0) {
          this.showNoResultsMessage(searchTerm);
        }
      } else {
        this.manager.showError(result.message);
      }
    } catch (error) {
      this.manager.showError('เกิดข้อผิดพลาดในการค้นหา');
    }
  }

  async applyFilters() {
    const filterClass = this.filterClass?.value || '';
    const filterAward = this.filterAward?.value || '';
    const filterTerm = this.filterTerm?.value || '';
    const filterYear = this.filterYear?.value || '';
    
    let url = `../controllers/CertificateController.php?action=search&teacherId=${encodeURIComponent(this.manager.teacherId)}`;
    
    if (filterClass) url += `&classFilter=${encodeURIComponent(filterClass)}`;
    if (filterAward) url += `&awardFilter=${encodeURIComponent(filterAward)}`;
    if (filterTerm) url += `&termFilter=${encodeURIComponent(filterTerm)}`;
    if (filterYear) url += `&yearFilter=${encodeURIComponent(filterYear)}`;
    
    try {
      const response = await fetch(url);
      const result = await response.json();
      
      if (result.success) {
        this.manager.certificatesData = result.data;
        this.manager.tableManager.renderTable(this.manager.certificatesData);
      } else {
        this.manager.showError(result.message);
      }
    } catch (error) {
      this.manager.showError('เกิดข้อผิดพลาดในการกรองข้อมูล');
    }
  }

  clearAllFilters() {
    if (this.searchInput) this.searchInput.value = '';
    if (this.filterClass) this.filterClass.value = '';
    if (this.filterAward) this.filterAward.value = '';
    if (this.filterTerm) this.filterTerm.value = '';
    if (this.filterYear) this.filterYear.value = '';
    
    this.manager.loadCertificates();
  }

  showNoResultsMessage(searchTerm) {
    Swal.fire({
      title: 'ไม่พบผลลัพธ์',
      text: `ไม่พบข้อมูลที่ตรงกับคำค้นหา "${searchTerm}"`,
      icon: 'info',
      timer: 2000,
      showConfirmButton: false
    });
  }
}
