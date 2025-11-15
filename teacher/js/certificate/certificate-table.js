/**
 * Certificate Table Manager
 * Handles table rendering and interactions
 */

class CertificateTableManager {
  constructor(manager) {
    this.manager = manager;
    this.tableBody = document.querySelector('#certificateTable tbody');
  }

  showLoadingState() {
    if (this.tableBody) {
      this.tableBody.innerHTML = `
        <tr class="loading-row">
          <td colspan="12" class="py-8 text-center">
            <div class="flex justify-center items-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <span class="ml-3 text-gray-600">กำลังโหลดข้อมูล...</span>
            </div>
          </td>
        </tr>
      `;
    }
  }

  hideLoadingState() {
    const loadingRow = document.querySelector('.loading-row');
    loadingRow?.remove();
  }

  showErrorState() {
    if (this.tableBody) {
      this.tableBody.innerHTML = `
        <tr>
          <td colspan="12" class="text-center py-8">
            <div class="flex flex-col items-center">
              <i class="fas fa-exclamation-triangle text-6xl text-red-300 mb-4"></i>
              <p class="text-red-500 text-lg">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
              <p class="text-gray-500 mb-4">กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต</p>
              <button onclick="location.reload()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-sync-alt mr-2"></i>ลองใหม่
              </button>
            </div>
          </td>
        </tr>
      `;
    }
  }

  renderTable(certificates) {
    if (!this.tableBody) return;
    
    this.tableBody.innerHTML = '';
    
    if (certificates.length === 0) {
      this.showEmptyState();
      return;
    }

    certificates.forEach((cert, index) => {
      const row = this.createTableRow(cert, index);
      this.tableBody.appendChild(row);
    });

    this.bindTableEvents();
  }

  showEmptyState() {
    if (this.tableBody) {
      this.tableBody.innerHTML = `
        <tr>
          <td colspan="12" class="text-center py-8">
            <div class="flex flex-col items-center">
              <i class="fas fa-certificate text-6xl text-gray-300 mb-4"></i>
              <p class="text-gray-500 text-lg">ยังไม่มีข้อมูลเกียรติบัตร</p>
              <p class="text-gray-400">เริ่มต้นบันทึกเกียรติบัตรแรกของคุณ</p>
            </div>
          </td>
        </tr>
      `;
    }
  }

  createTableRow(cert, index) {
    const row = document.createElement('tr');
    row.className = 'table-row-hover border-b hover:shadow-md transition-all duration-300';
    row.style.animationDelay = `${index * 50}ms`;
    
    row.innerHTML = `
      <td class="py-4 px-4 border-b font-medium">${cert.student_name}</td>
      <td class="py-4 px-4 border-b text-center">
        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">
          ${cert.student_class}/${cert.student_room}
        </span>
      </td>
      <td class="py-4 px-4 border-b">${cert.award_name || '-'}</td>
      <td class="py-4 px-4 border-b text-center">
        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm">
          ${cert.award_level || '-'}
        </span>
      </td>
      <td class="py-4 px-4 border-b">${cert.award_organization || '-'}</td>
      <td class="py-4 px-4 border-b text-center">${this.getAwardBadge(cert.award_type)}</td>
      <td class="py-4 px-4 border-b max-w-xs truncate" title="${cert.award_detail}">
        ${cert.award_detail}
      </td>
      <td class="py-4 px-4 border-b text-center">
        <span class="text-gray-600">
          <i class="fas fa-calendar-alt mr-1"></i>
          ${this.formatDate(cert.award_date)}
        </span>
      </td>
      <td class="py-4 px-4 border-b text-center">${this.getImageColumn(cert)}</td>
      <td class="py-4 px-4 border-b text-center">${this.getTermYearInfo(cert)}</td>
      <td class="py-4 px-4 border-b text-center">${cert.teacher_name || '-'}</td>
      <td class="py-4 px-4 border-b text-center">
        <div class="flex gap-2 justify-center">
          <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded-lg btn-edit transition-all duration-300 hover:scale-105" data-id="${cert.id}" title="แก้ไข">
            <i class="fas fa-edit"></i>
          </button>
          <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg btn-delete transition-all duration-300 hover:scale-105" data-id="${cert.id}" title="ลบ">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </td>
    `;
    
    return row;
  }

  getAwardBadge(awardType) {
    if (awardType.includes('ชนะเลิศ')) {
      return `<span class="award-badge award-gold"><i class="fas fa-trophy mr-1"></i>${awardType}</span>`;
    } else if (awardType.includes('รองชนะเลิศ')) {
      return `<span class="award-badge award-silver"><i class="fas fa-medal mr-1"></i>${awardType}</span>`;
    } else if (awardType.includes('ชมเชย')) {
      return `<span class="award-badge award-bronze"><i class="fas fa-award mr-1"></i>${awardType}</span>`;
    } else {
      return `<span class="award-badge award-other"><i class="fas fa-certificate mr-1"></i>${awardType}</span>`;
    }
  }

  getImageColumn(cert) {
    if (cert.certificate_image) {
      return `<button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-sm view-image transition-all duration-300 hover:scale-105" data-image="${cert.certificate_image}">
                <i class="fas fa-eye mr-1"></i>ดูรูป
              </button>`;
    } else {
      return '<span class="text-gray-400 text-sm"><i class="fas fa-image mr-1"></i>ไม่มีรูป</span>';
    }
  }

  getTermYearInfo(cert) {
    if (cert.term && cert.year) {
      return `<span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">${cert.term}/${cert.year}</span>`;
    } else {
      return '<span class="text-gray-400 text-xs">-</span>';
    }
  }

  formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('th-TH', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }

  bindTableEvents() {
    // View image events
    document.querySelectorAll('.view-image').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const imageName = btn.getAttribute('data-image');
        this.showImageModal(imageName);
      });
    });

    // Delete events
    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const certId = btn.getAttribute('data-id');
        this.confirmDelete(certId);
      });
    });

    // Edit events
    document.querySelectorAll('.btn-edit').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const certId = btn.getAttribute('data-id');
        this.manager.formHandler.editCertificate(certId);
      });
    });
  }

  showImageModal(imageName) {
    Swal.fire({
      title: 'รูปเกียรติบัตร',
      imageUrl: `../uploads/certificates/${imageName}`,
      imageWidth: 600,
      imageHeight: 400,
      imageAlt: 'เกียรติบัตร',
      showCloseButton: true,
      showConfirmButton: false,
      customClass: {
        image: 'rounded-lg shadow-lg'
      }
    });
  }

  confirmDelete(certId) {
    Swal.fire({
      title: 'ยืนยันการลบ',
      text: 'คุณต้องการลบข้อมูลเกียรติบัตรนี้หรือไม่?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'ลบ',
      cancelButtonText: 'ยกเลิก',
      confirmButtonColor: '#ef4444',
      customClass: {
        confirmButton: 'hover:scale-105 transition-transform',
        cancelButton: 'hover:scale-105 transition-transform'
      }
    }).then(result => {
      if (result.isConfirmed) {
        this.deleteCertificate(certId);
      }
    });
  }

  async deleteCertificate(certId) {
    try {
      const response = await fetch('../controllers/CertificateController.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: certId })
      });
      
      const result = await response.json();
      
      if (result.success) {
        this.manager.showSuccess(result.message, 2000);
        await Promise.all([
          this.manager.loadCertificates(),
          this.manager.loadStatistics()
        ]);
      } else {
        this.manager.showError(result.message);
      }
    } catch (error) {
      console.error('Delete error:', error);
      this.manager.showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    }
  }
}
