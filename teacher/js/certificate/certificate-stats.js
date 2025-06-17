/**
 * Certificate Statistics Manager
 * Handles statistics display and management
 */

class CertificateStatsManager {
  constructor(manager) {
    this.manager = manager;
    this.initElements();
  }

  initElements() {
    this.totalCertsElement = document.getElementById('totalCerts');
    this.totalStudentsElement = document.getElementById('totalStudents');
    this.topAwardElement = document.getElementById('topAward');
    this.thisMonthElement = document.getElementById('thisMonth');
  }

  updateDisplay(data) {
    // Prevent null/undefined values
    if (this.totalCertsElement) {
      this.totalCertsElement.textContent = data.total_certificates ?? 0;
    }
    
    if (this.totalStudentsElement) {
      this.totalStudentsElement.textContent = data.total_students ?? 0;
    }
    
    if (this.topAwardElement) {
      this.topAwardElement.textContent = data.top_award ?? '-';
    }
    
    if (this.thisMonthElement) {
      this.thisMonthElement.textContent = data.this_month ?? 0;
    }

    // Load additional statistics
    this.loadTopStudents();
    this.loadRecentCertificates();
  }

  async loadTopStudents() {
    try {
      const response = await fetch(`../controllers/CertificateController.php?action=topStudents&teacherId=${encodeURIComponent(this.manager.teacherId)}&limit=5`);
      const result = await response.json();
      
      if (result.success && result.data.length > 0) {
        this.displayTopStudents(result.data);
      }
    } catch (error) {
      console.error('Error loading top students:', error);
    }
  }

  async loadRecentCertificates() {
    try {
      const response = await fetch(`../controllers/CertificateController.php?action=recent&teacherId=${encodeURIComponent(this.manager.teacherId)}&limit=3`);
      const result = await response.json();
      
      if (result.success && result.data.length > 0) {
        this.displayRecentCertificates(result.data);
      }
    } catch (error) {
      console.error('Error loading recent certificates:', error);
    }
  }

  displayTopStudents(students) {
    // This could be implemented to show top students in a modal or sidebar
    console.log('Top students:', students);
    
    // Future implementation: Create a modal or section to display top students
    // Example: Show in a modal when clicking a "Top Students" button
  }

  displayRecentCertificates(certificates) {
    // This could be implemented to show recent certificates in a sidebar or notification area
    console.log('Recent certificates:', certificates);
    
    // Future implementation: Create a recent activity panel
  }

  async getStatisticsByDateRange(startDate, endDate, term = null, year = null) {
    try {
      let url = `../controllers/CertificateController.php?action=statistics&teacherId=${encodeURIComponent(this.manager.teacherId)}`;
      
      if (startDate && endDate) {
        url += `&startDate=${encodeURIComponent(startDate)}&endDate=${encodeURIComponent(endDate)}`;
      }
      
      if (term) {
        url += `&term=${encodeURIComponent(term)}`;
      }
      
      if (year) {
        url += `&year=${encodeURIComponent(year)}`;
      }
      
      const response = await fetch(url);
      const result = await response.json();
      
      if (result.success) {
        return result.data;
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      console.error('Error getting statistics by date range:', error);
      throw error;
    }
  }

  // Method to show statistics modal with detailed breakdown
  async showDetailedStats() {
    try {
      this.manager.showLoading('กำลังโหลดสถิติ...');
      
      const stats = await this.getStatisticsByDateRange();
      
      let html = `
        <div class="text-left">
          <h4 class="font-bold text-lg mb-4">📊 สถิติโดยละเอียด</h4>
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-blue-50 p-3 rounded">
              <div class="text-2xl font-bold text-blue-600">${stats.total_certificates}</div>
              <div class="text-sm text-gray-600">เกียรติบัตรทั้งหมด</div>
            </div>
            <div class="bg-green-50 p-3 rounded">
              <div class="text-2xl font-bold text-green-600">${stats.total_students}</div>
              <div class="text-sm text-gray-600">นักเรียนที่ได้รับรางวัล</div>
            </div>
          </div>
      `;
      
      if (stats.award_breakdown && stats.award_breakdown.length > 0) {
        html += `
          <h5 class="font-semibold mb-2">🏆 ประเภทรางวัล</h5>
          <div class="space-y-2 mb-4">
        `;
        
        stats.award_breakdown.forEach(award => {
          const percentage = ((award.count_by_type / stats.total_certificates) * 100).toFixed(1);
          html += `
            <div class="flex justify-between items-center">
              <span class="text-sm">${award.award_type}</span>
              <span class="text-sm font-semibold">${award.count_by_type} (${percentage}%)</span>
            </div>
          `;
        });
        
        html += '</div>';
      }
      
      html += '</div>';
      
      this.manager.closeLoading();
      
      Swal.fire({
        title: 'สถิติรายละเอียด',
        html: html,
        width: '600px',
        showCloseButton: true,
        showConfirmButton: false
      });
      
    } catch (error) {
      this.manager.closeLoading();
      this.manager.showError('ไม่สามารถโหลดสถิติได้');
    }
  }
}
