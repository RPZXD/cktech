/**
 * Certificate Export Manager
 * Handles data export functionality
 */

class CertificateExportManager {
  constructor(manager) {
    this.manager = manager;
  }

  showExportDialog() {
    Swal.fire({
      title: 'ส่งออกข้อมูล',
      text: 'เลือกรูปแบบที่ต้องการส่งออก',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Excel (.xlsx)',
      cancelButtonText: 'PDF',
      showDenyButton: true,
      denyButtonText: 'CSV',
      customClass: {
        confirmButton: 'btn btn-success',
        cancelButton: 'btn btn-danger', 
        denyButton: 'btn btn-info'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        this.exportToFormat('excel');
      } else if (result.isDenied) {
        this.exportToFormat('csv');
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        this.exportToFormat('pdf');
      }
    });
  }

  async exportToFormat(format) {
    try {
      // Show loading
      this.manager.showLoading(`กำลังสร้างไฟล์ ${format.toUpperCase()}...`);

      // Create export URL
      const exportUrl = `../controllers/CertificateController.php?action=export&format=${format}&teacherId=${encodeURIComponent(this.manager.teacherId)}`;
      
      // Check for errors before downloading
      const response = await fetch(exportUrl, {
        method: 'GET',
        headers: {
          'Accept': this.getAcceptHeader(format)
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      // Check if response is JSON (error response)
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        const data = await response.json();
        throw new Error(data.message || 'เกิดข้อผิดพลาดในการส่งออกข้อมูล');
      }
      
      // If successful, trigger download
      this.manager.closeLoading();
      this.downloadFile(exportUrl);
      
      this.manager.showSuccess(`ไฟล์ ${format.toUpperCase()} ถูกส่งออกเรียบร้อยแล้ว`, 2000);
      
    } catch (error) {
      this.manager.closeLoading();
      this.manager.showError(error.message || 'ไม่สามารถส่งออกข้อมูลได้');
    }
  }

  getAcceptHeader(format) {
    switch (format) {
      case 'json':
        return 'application/json';
      case 'csv':
        return 'text/csv';
      case 'excel':
      case 'xlsx':
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
      case 'pdf':
        return 'application/pdf';
      default:
        return '*/*';
    }
  }

  downloadFile(url) {
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  // Individual export methods for direct calling
  exportToCSV() {
    this.exportToFormat('csv');
  }

  exportToExcel() {
    this.exportToFormat('excel');
  }

  exportToPDF() {
    this.exportToFormat('pdf');
  }

  exportToJSON() {
    this.exportToFormat('json');
  }

  // Advanced export with custom filters
  async exportWithFilters(filters = {}) {
    try {
      this.manager.showLoading('กำลังเตรียมข้อมูลสำหรับส่งออก...');
      
      // Build URL with filters
      let exportUrl = `../controllers/CertificateController.php?action=export&teacherId=${encodeURIComponent(this.manager.teacherId)}`;
      
      Object.keys(filters).forEach(key => {
        if (filters[key]) {
          exportUrl += `&${key}=${encodeURIComponent(filters[key])}`;
        }
      });
      
      // Show format selection
      this.manager.closeLoading();
      
      const result = await Swal.fire({
        title: 'เลือกรูปแบบการส่งออก',
        text: 'ข้อมูลจะถูกกรองตามเงื่อนไขที่เลือก',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Excel',
        cancelButtonText: 'ยกเลิก',
        showDenyButton: true,
        denyButtonText: 'PDF'
      });
      
      if (result.isConfirmed) {
        exportUrl += '&format=excel';
        this.downloadFile(exportUrl);
      } else if (result.isDenied) {
        exportUrl += '&format=pdf';
        this.downloadFile(exportUrl);
      }
      
    } catch (error) {
      this.manager.closeLoading();
      this.manager.showError('เกิดข้อผิดพลาดในการส่งออกข้อมูล');
    }
  }

  // Export current filtered results
  exportCurrentView() {
    const currentFilters = this.manager.filterManager.getCurrentFilters();
    this.exportWithFilters(currentFilters);
  }
}
