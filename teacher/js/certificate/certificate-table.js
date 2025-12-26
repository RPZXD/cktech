/**
 * Certificate Table Manager
 * Handles table rendering and interactions
 */

class CertificateTableManager {
  constructor(manager) {
    this.manager = manager;
    this.tableBody = document.querySelector('#certificateTableBody');
    this.mobileContainer = document.querySelector('#mobileCertificateCards');
  }

  showLoadingState() {
    const loadingHtml = `
      <div class="flex flex-col items-center justify-center py-12 text-gray-500">
        <div class="text-5xl animate-bounce mb-4">🏆</div>
        <div class="flex items-center gap-3">
          <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-orange-500"></div>
          <span class="font-medium">กำลังโหลดข้อมูล...</span>
        </div>
      </div>
    `;

    if (this.tableBody) {
      this.tableBody.innerHTML = `
        <tr class="loading-row">
          <td colspan="9" class="py-12 text-center">${loadingHtml}</td>
        </tr>
      `;
    }

    if (this.mobileContainer) {
      this.mobileContainer.innerHTML = loadingHtml;
    }
  }

  hideLoadingState() {
    // Handled by render
  }

  showErrorState() {
    const errorHtml = `
      <div class="flex flex-col items-center justify-center py-12 text-center">
        <div class="text-5xl mb-4">⚠️</div>
        <p class="text-red-500 font-bold text-lg">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
        <p class="text-gray-500 mb-6">กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต</p>
        <button onclick="location.reload()" class="px-6 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl shadow-lg transition-all active:scale-95">
          <i class="fas fa-sync-alt mr-2"></i>ลองใหม่
        </button>
      </div>
    `;

    if (this.tableBody) {
      this.tableBody.innerHTML = `<tr><td colspan="9">${errorHtml}</td></tr>`;
    }

    if (this.mobileContainer) {
      this.mobileContainer.innerHTML = errorHtml;
    }
  }

  renderTable(certificates) {
    if (this.tableBody) this.tableBody.innerHTML = '';
    if (this.mobileContainer) this.mobileContainer.innerHTML = '';

    if (certificates.length === 0) {
      this.showEmptyState();
      return;
    }

    // Desktop
    if (this.tableBody) {
      certificates.forEach((cert, index) => {
        const row = this.createTableRow(cert, index);
        this.tableBody.appendChild(row);
      });
    }

    // Mobile
    if (this.mobileContainer) {
      certificates.forEach((cert, index) => {
        const card = this.createMobileCard(cert, index);
        this.mobileContainer.appendChild(card);
      });
    }

    this.bindTableEvents();
  }

  showEmptyState() {
    const emptyHtml = `
      <div class="flex flex-col items-center justify-center py-12 text-center">
        <div class="text-5xl mb-4 opacity-40">🏆</div>
        <p class="text-gray-500 font-bold text-lg">ยังไม่มีข้อมูลเกียรติบัตร</p>
        <p class="text-gray-400">เริ่มต้นบันทึกเกียรติบัตรแรกของคุณวันนี้</p>
      </div>
    `;

    if (this.tableBody) {
      this.tableBody.innerHTML = `<tr><td colspan="9">${emptyHtml}</td></tr>`;
    }

    if (this.mobileContainer) {
      this.mobileContainer.innerHTML = emptyHtml;
    }
  }

  createTableRow(cert, index) {
    const row = document.createElement('tr');
    row.className = 'table-row-hover border-b border-gray-100 dark:border-gray-700 hover:bg-orange-50/30 dark:hover:bg-orange-900/10 transition-all';

    row.innerHTML = `
      <td class="py-4 px-4 font-bold text-slate-700 dark:text-slate-200">${cert.student_name}</td>
      <td class="py-4 px-4">
        <span class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-3 py-1 rounded-full text-xs font-bold ring-1 ring-blue-100 dark:ring-blue-800">
          ${cert.student_class}/${cert.student_room}
        </span>
      </td>
      <td class="py-4 px-4 text-slate-600 dark:text-slate-400 font-medium">${cert.award_name || '-'}</td>
      <td class="py-4 px-4 text-center">
        <span class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-3 py-1 rounded-full text-xs font-bold ring-1 ring-indigo-100 dark:ring-indigo-800">
          ${cert.award_level || '-'}
        </span>
      </td>
      <td class="py-4 px-4 text-center">${this.getAwardBadge(cert.award_type)}</td>
      <td class="py-4 px-4 text-center">
        <span class="text-slate-500 dark:text-slate-400 text-xs">
          <i class="fas fa-calendar-alt mr-1 opacity-50"></i>
          ${this.formatDate(cert.award_date)}
        </span>
      </td>
      <td class="py-4 px-4 text-center">${this.getImageColumn(cert)}</td>
      <td class="py-4 px-4 text-center">${this.getTermYearInfo(cert)}</td>
      <td class="py-4 px-4">
        <div class="flex gap-2 justify-center">
          <button class="w-9 h-9 flex items-center justify-center bg-amber-500 hover:bg-amber-600 text-white rounded-xl btn-edit shadow-md shadow-amber-500/20 active:scale-90 transition-all" data-id="${cert.id}" title="แก้ไข">
            <i class="fas fa-edit text-xs"></i>
          </button>
          <button class="w-9 h-9 flex items-center justify-center bg-rose-500 hover:bg-rose-600 text-white rounded-xl btn-delete shadow-md shadow-rose-500/20 active:scale-90 transition-all" data-id="${cert.id}" title="ลบ">
            <i class="fas fa-trash text-xs"></i>
          </button>
        </div>
      </td>
    `;

    return row;
  }

  createMobileCard(cert, index) {
    const card = document.createElement('div');
    card.className = 'glass rounded-2xl p-4 shadow-md border border-white/20 animate-fade-in mb-3';

    card.innerHTML = `
      <div class="flex justify-between items-start mb-3">
        <div>
          <h4 class="font-bold text-slate-900 dark:text-white">${cert.student_name}</h4>
          <div class="flex gap-2 mt-1">
            <span class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded-lg text-[10px] font-bold ring-1 ring-blue-100">
              ${cert.student_class}/${cert.student_room}
            </span>
            <span class="text-slate-400 text-[10px]">
              <i class="fas fa-calendar-alt mr-1"></i>${this.formatDate(cert.award_date)}
            </span>
          </div>
        </div>
        <div class="flex flex-col items-end gap-2">
           ${this.getTermYearInfo(cert)}
           ${this.getAwardBadge(cert.award_type)}
        </div>
      </div>
      
      <div class="space-y-2 mb-4">
        <div class="flex justify-between items-center text-xs">
          <span class="text-slate-500">รางวัล:</span>
          <span class="font-bold text-slate-700 dark:text-slate-300 text-right">${cert.award_name || '-'}</span>
        </div>
        <div class="flex justify-between items-center text-xs">
          <span class="text-slate-500">ระดับ:</span>
          <span class="font-bold text-indigo-600 dark:text-indigo-400">${cert.award_level || '-'}</span>
        </div>
      </div>
      
      <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-2">
            ${this.getImageColumn(cert)}
        </div>
        <div class="flex gap-2">
          <button class="w-10 h-10 flex items-center justify-center bg-amber-500 text-white rounded-xl btn-edit active:scale-90 transition-all" data-id="${cert.id}">
            <i class="fas fa-edit text-sm"></i>
          </button>
          <button class="w-10 h-10 flex items-center justify-center bg-rose-500 text-white rounded-xl btn-delete active:scale-90 transition-all" data-id="${cert.id}">
            <i class="fas fa-trash text-sm"></i>
          </button>
        </div>
      </div>
    `;

    return card;
  }

  getAwardBadge(awardType) {
    if (!awardType) return '';
    let style = 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 ring-slate-200';
    let icon = 'fa-certificate';

    if (awardType.includes('ชนะเลิศ') && !awardType.includes('รอง')) {
      style = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 ring-amber-200';
      icon = 'fa-trophy';
    } else if (awardType.includes('รองชนะเลิศ')) {
      style = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 ring-slate-200';
      icon = 'fa-medal';
    } else if (awardType.includes('ชมเชย')) {
      style = 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 ring-orange-200';
      icon = 'fa-award';
    }

    return `
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] md:text-xs font-bold ring-1 ${style}">
        <i class="fas ${icon} opacity-80"></i>
        ${awardType}
      </span>
    `;
  }

  getImageColumn(cert) {
    if (cert.certificate_image) {
      return `
        <button class="view-image inline-flex items-center gap-2 px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 active:scale-90 transition-all" data-image="${cert.certificate_image}">
          <i class="fas fa-eye"></i>ดูรูป
        </button>`;
    } else {
      return '<span class="text-slate-400 text-[10px] md:text-xs font-medium italic"><i class="fas fa-image mr-1 opacity-50"></i>ไม่มีรูป</span>';
    }
  }

  getTermYearInfo(cert) {
    if (cert && cert.term && cert.year) {
      return `<span class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded-lg text-[10px] md:text-xs font-extrabold ring-1 ring-emerald-100 dark:ring-emerald-800">${cert.term}/${cert.year}</span>`;
    }
    return '<span class="text-slate-400 text-[10px]">-</span>';
  }

  formatDate(dateString) {
    if (!dateString) return '-';
    try {
      const date = new Date(dateString);
      return date.toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    } catch (e) { return dateString; }
  }

  bindTableEvents() {
    // Re-bind to use both desktop and mobile elements
    const bindBtn = (selector, action) => {
      document.querySelectorAll(selector).forEach(btn => {
        // Remove old listeners by cloning (simple way)
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        newBtn.addEventListener('click', (e) => action(newBtn, e));
      });
    };

    bindBtn('.view-image', (btn) => {
      const imageName = btn.getAttribute('data-image');
      this.showImageModal(imageName);
    });

    bindBtn('.btn-delete', (btn) => {
      const certId = btn.getAttribute('data-id');
      this.confirmDelete(certId);
    });

    bindBtn('.btn-edit', (btn) => {
      const certId = btn.getAttribute('data-id');
      this.manager.formHandler.editCertificate(certId);
    });
  }

  showImageModal(imageName) {
    Swal.fire({
      title: 'รูปเกียรติบัตร',
      imageUrl: `../uploads/certificates/${imageName}`,
      imageWidth: 800,
      imageAlt: 'เกียรติบัตร',
      showCloseButton: true,
      showConfirmButton: false,
      background: 'rgba(255, 255, 255, 0.95)',
      backdrop: 'rgba(0,0,0,0.8)',
      customClass: {
        image: 'rounded-2xl shadow-2xl border-4 border-white'
      }
    });
  }

  confirmDelete(certId) {
    Swal.fire({
      title: 'ยืนยันการลบ?',
      text: 'ข้อมูลเกียรติบัตรนี้จะถูกลบออกจากระบบอย่างถาวร',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'ใช่, ลบเลย',
      cancelButtonText: 'ยกเลิก',
      confirmButtonColor: '#f43f5e',
      cancelButtonColor: '#94a3b8',
      borderRadius: '1.25rem',
      customClass: {
        confirmButton: 'font-bold px-6 py-2',
        cancelButton: 'font-bold px-6 py-2'
      }
    }).then(result => {
      if (result.isConfirmed) {
        this.deleteCertificate(certId);
      }
    });
  }

  async deleteCertificate(certId) {
    try {
      this.manager.showLoading('กำลังลบข้อมูล...');
      const response = await fetch('../controllers/CertificateController.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: certId })
      });

      const result = await response.json();
      this.manager.closeLoading();

      if (result.success) {
        this.manager.showSuccess(result.message);
        await Promise.all([
          this.manager.loadCertificates(),
          this.manager.loadStatistics()
        ]);
      } else {
        this.manager.showError(result.message);
      }
    } catch (error) {
      console.error('Delete error:', error);
      this.manager.closeLoading();
      this.manager.showError('เกิดข้อผิดพลาดในการลบข้อมูล');
    }
  }
}
