/**
 * Department Certificate Management System
 * @author CKTech Team
 * @version 1.0
 */

class DepartmentCertificateManager {
  constructor() {
    this.departmentName = window.departmentName || null;
    this.certificatesData = [];
    this.originalCertificatesData = []; // เก็บข้อมูลดั้งเดิม
    this.teachersData = [];
    this.currentView = 'table';
    
    this.init();
  }
  async init() {
    try {
      this.initEventHandlers();
      
      // โหลดครูก่อน แล้วค่อยโหลด certificates และ statistics
      await this.loadDepartmentTeachers();
      await this.loadDepartmentCertificates();
      await this.loadDepartmentStatistics();
      await this.loadFilterOptions();
    } catch (error) {
      console.error('Initialization error:', error);
      this.showError('เกิดข้อผิดพลาดในการเริ่มต้นระบบ');
    }
  }
  async loadDepartmentTeachers() {
    try {
      const response = await fetch(`../controllers/DepartmentController.php?action=listTeachers&department=${encodeURIComponent(this.departmentName)}`);
      const teachers = await response.json();
      
      this.teachersData = teachers || [];
      this.populateTeacherFilter();
    } catch (error) {
      console.error('Error loading teachers:', error);
    }
  }
  async loadDepartmentCertificates() {
    this.showLoadingState();
    
    try {
      // รอให้โหลดครูเสร็จก่อน
      if (this.teachersData.length === 0) {
        await this.loadDepartmentTeachers();
      }
      
      let allCertificates = [];
        // โหลด certificates จากครูทุกคนในกลุ่มสาระ
      for (const teacher of this.teachersData) {
        try {
          console.log(`Loading certificates for teacher: ${teacher.Teach_name} (ID: ${teacher.Teach_id})`);
          const response = await fetch(`../controllers/CertificateController.php?action=list&teacherId=${teacher.Teach_id}`);
          if (response.ok) {
            const result = await response.json();
            console.log(`Response for teacher ${teacher.Teach_name}:`, result);
            
            // ตรวจสอบรูปแบบ response
            let certificates = [];
            if (result && result.success && Array.isArray(result.data)) {
              certificates = result.data;
            } else if (Array.isArray(result)) {
              certificates = result;
            }
            
            if (certificates.length > 0) {
              allCertificates = allCertificates.concat(certificates);
            }
          }
        } catch (error) {
          console.error(`Error loading certificates for teacher ${teacher.Teach_name}:`, error);
        }      }
        console.log('Total certificates loaded:', allCertificates.length);
      this.originalCertificatesData = allCertificates; // เก็บข้อมูลดั้งเดิม
      this.certificatesData = [...allCertificates]; // copy ข้อมูลสำหรับการแสดงผล
      this.renderCurrentView();
    } catch (error) {
      console.error('Error loading certificates:', error);
      this.showErrorState();
    } finally {
      this.hideLoadingState();
    }
  }  async loadDepartmentStatistics() {
    try {
      // คำนวณ statistics จากข้อมูลดั้งเดิม
      const stats = {
        total_certificates: this.originalCertificatesData.length,
        total_teachers: this.teachersData.length,
        top_teacher: this.getTopTeacher(),
        this_month: this.getThisMonthCount()
      };
      
      this.updateStatisticsDisplay(stats);
    } catch (error) {
      console.error('Error loading statistics:', error);
    }
  }
  getTopTeacher() {
    if (this.originalCertificatesData.length === 0) return '-';
    
    // นับจำนวน certificate แต่ละครู
    const teacherCounts = {};
    this.originalCertificatesData.forEach(cert => {
      const teacherName = cert.teacher_name;
      teacherCounts[teacherName] = (teacherCounts[teacherName] || 0) + 1;
    });
    
    // หาครูที่มี certificate มากที่สุด
    let maxCount = 0;
    let topTeacher = '-';
    Object.entries(teacherCounts).forEach(([name, count]) => {
      if (count > maxCount) {
        maxCount = count;
        topTeacher = name;
      }
    });
    
    return maxCount > 0 ? topTeacher : '-';
  }
  getThisMonthCount() {
    const now = new Date();
    const thisMonth = now.getMonth();
    const thisYear = now.getFullYear();
    
    return this.originalCertificatesData.filter(cert => {
      const certDate = new Date(cert.created_at || cert.award_date);
      return certDate.getMonth() === thisMonth && certDate.getFullYear() === thisYear;
    }).length;
  }  async loadFilterOptions() {
    try {
      // สร้าง filter options จากข้อมูลดั้งเดิม
      const awardTypes = [...new Set(this.originalCertificatesData.map(cert => cert.award_type).filter(Boolean))];
      const terms = [...new Set(this.originalCertificatesData.map(cert => cert.term).filter(Boolean))];
      const years = [...new Set(this.originalCertificatesData.map(cert => cert.year).filter(Boolean))];
      
      this.populateFilterOptions({ awardTypes, terms, years });
    } catch (error) {
      console.error('Error loading filter options:', error);
    }
  }

  populateTeacherFilter() {
    const teacherSelect = document.getElementById('filterTeacher');
    if (!teacherSelect) return;

    // Clear existing options except the first one
    teacherSelect.innerHTML = '<option value="">ครูทั้งหมด</option>';

    this.teachersData.forEach(teacher => {
      const option = document.createElement('option');
      option.value = teacher.Teach_id;
      option.textContent = teacher.Teach_name;
      teacherSelect.appendChild(option);
    });
  }
  populateFilterOptions(data) {
    // Populate award type filter
    const awardTypeSelect = document.getElementById('filterAwardType');
    if (awardTypeSelect && data.awardTypes) {
      awardTypeSelect.innerHTML = '<option value="">ประเภทรางวัลทั้งหมด</option>';
      data.awardTypes.forEach(type => {
        const option = document.createElement('option');
        option.value = type;
        option.textContent = type;
        awardTypeSelect.appendChild(option);
      });
    }

    // Populate term filter
    const termSelect = document.getElementById('filterDeptTerm');
    if (termSelect && data.terms) {
      termSelect.innerHTML = '<option value="">ทุกภาคเรียน</option>';
      data.terms.forEach(term => {
        const option = document.createElement('option');
        option.value = term;
        option.textContent = `ภาคเรียนที่ ${term}`;
        termSelect.appendChild(option);
      });
    }

    // Populate year filter
    const yearSelect = document.getElementById('filterDeptYear');
    if (yearSelect && data.years) {
      yearSelect.innerHTML = '<option value="">ทุกปี</option>';
      data.years.forEach(year => {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = `ปีการศึกษา ${year}`;
        yearSelect.appendChild(option);
      });
    }
  }

  updateStatisticsDisplay(stats) {
    // Update statistics cards
    const elements = {
      deptTotalCerts: stats.total_certificates || 0,
      deptTotalTeachers: stats.total_teachers || 0,
      deptTopTeacher: stats.top_teacher || '-',
      deptThisMonth: stats.this_month || 0
    };

    Object.entries(elements).forEach(([id, value]) => {
      const element = document.getElementById(id);
      if (element) {
        element.textContent = value;
      }
    });
  }

  initEventHandlers() {
    // View toggle buttons
    document.getElementById('btnTableView')?.addEventListener('click', () => {
      this.switchView('table');
    });

    document.getElementById('btnSummaryView')?.addEventListener('click', () => {
      this.switchView('summary');
    });

    // Filter handlers
    ['filterTeacher', 'filterAwardType', 'filterDeptTerm', 'filterDeptYear'].forEach(filterId => {
      document.getElementById(filterId)?.addEventListener('change', () => {
        this.applyFilters();
      });
    });

    // Clear filter button
    document.getElementById('btnClearDeptFilter')?.addEventListener('click', () => {
      this.clearFilters();
    });

    // Export button
    document.getElementById('btnExportDepartment')?.addEventListener('click', () => {
      this.exportDepartmentReport();
    });    // Close modal button
    document.getElementById('closeCertificateDetail')?.addEventListener('click', () => {
      this.closeDetailModal();
    });    // Close modal when clicking outside
    document.getElementById('modalCertificateDetail')?.addEventListener('click', (e) => {
      if (e.target.id === 'modalCertificateDetail') {
        this.closeDetailModal();
      }
    });

    // Close modal with ESC key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        const modal = document.getElementById('modalCertificateDetail');
        if (modal && !modal.classList.contains('hidden')) {
          this.closeDetailModal();
        }
      }
    });
  }

  switchView(viewType) {
    this.currentView = viewType;
    
    // Update button states
    document.getElementById('btnTableView')?.classList.toggle('bg-blue-600', viewType === 'table');
    document.getElementById('btnTableView')?.classList.toggle('bg-gray-400', viewType !== 'table');
    document.getElementById('btnSummaryView')?.classList.toggle('bg-blue-600', viewType === 'summary');
    document.getElementById('btnSummaryView')?.classList.toggle('bg-gray-400', viewType !== 'summary');

    // Show/hide views
    document.getElementById('tableView')?.classList.toggle('hidden', viewType !== 'table');
    document.getElementById('summaryView')?.classList.toggle('hidden', viewType !== 'summary');

    this.renderCurrentView();
  }

  renderCurrentView() {
    if (this.currentView === 'table') {
      this.renderTableView();
    } else {
      this.renderSummaryView();
    }
  }
  renderTableView() {
    const tableBody = document.querySelector('#departmentCertificateTable tbody');
    if (!tableBody) return;

    console.log('Rendering table view with certificates:', this.certificatesData.length);
    tableBody.innerHTML = '';

    if (this.certificatesData.length === 0) {
      this.showEmptyState(tableBody, 9);
      return;
    }

    this.certificatesData.forEach((cert, index) => {
      const row = this.createTableRow(cert, index);
      tableBody.appendChild(row);
    });

    this.bindTableEvents();
  }

  renderSummaryView() {
    const container = document.getElementById('teacherSummaryContainer');
    if (!container) return;

    container.innerHTML = '';

    // Group certificates by teacher
    const teacherGroups = this.groupCertificatesByTeacher();

    if (Object.keys(teacherGroups).length === 0) {
      container.innerHTML = `
        <div class="col-span-full text-center py-8">
          <i class="fas fa-certificate text-6xl text-gray-300 mb-4"></i>
          <p class="text-gray-500 text-lg">ยังไม่มีข้อมูลเกียรติบัตร</p>
        </div>
      `;
      return;
    }    Object.entries(teacherGroups).forEach(([teacherId, data]) => {
      const card = this.createTeacherSummaryCard(data);
      container.appendChild(card);
    });

    // Bind events หลังจากสร้าง cards แล้ว
    this.bindSummaryEvents();
  }

  groupCertificatesByTeacher() {
    const groups = {};
    
    this.certificatesData.forEach(cert => {
      const teacherId = cert.teacher_id;
      if (!groups[teacherId]) {
        groups[teacherId] = {
          teacher_id: teacherId,
          teacher_name: cert.teacher_name,
          certificates: [],
          total_count: 0,
          award_types: {}
        };
      }
      
      groups[teacherId].certificates.push(cert);
      groups[teacherId].total_count++;
      
      const awardType = cert.award_type;
      groups[teacherId].award_types[awardType] = (groups[teacherId].award_types[awardType] || 0) + 1;
    });

    return groups;
  }

  createTableRow(cert, index) {
    const row = document.createElement('tr');
    row.className = 'table-row-hover border-b hover:shadow-md transition-all duration-300';
    row.style.animationDelay = `${index * 50}ms`;
    
    row.innerHTML = `
      <td class="py-4 px-4 border-b font-medium">
        <span class="text-blue-700">${cert.teacher_name}</span>
      </td>
      <td class="py-4 px-4 border-b">${cert.student_name}</td>
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
      <td class="py-4 px-4 border-b text-center">${this.getAwardBadge(cert.award_type)}</td>
      <td class="py-4 px-4 border-b text-center">
        <span class="text-gray-600">
          <i class="fas fa-calendar-alt mr-1"></i>
          ${this.formatDate(cert.award_date)}
        </span>
      </td>
      <td class="py-4 px-4 border-b text-center">${this.getTermYearInfo(cert)}</td>
      <td class="py-4 px-4 border-b text-center">
        <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg btn-detail transition-all duration-300 hover:scale-105" data-id="${cert.id}" title="ดูรายละเอียด">
          <i class="fas fa-eye"></i>
        </button>
      </td>
    `;
    
    return row;
  }
  createTeacherSummaryCard(teacherData) {
    console.log('Creating teacher summary card for:', teacherData);
    const card = document.createElement('div');
    card.className = 'certificate-card p-6';
    
    // Create award type summary
    const awardTypesHtml = Object.entries(teacherData.award_types)
      .map(([type, count]) => `
        <div class="flex justify-between items-center py-1">
          <span class="text-sm text-gray-600">${type}:</span>
          <span class="font-semibold text-blue-600">${count} รางวัล</span>
        </div>
      `).join('');

    card.innerHTML = `
      <div class="flex items-center mb-4">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
          <i class="fas fa-user-tie text-blue-600 text-xl"></i>
        </div>
        <div>
          <h3 class="text-lg font-bold text-gray-800">${teacherData.teacher_name}</h3>
          <p class="text-sm text-gray-500">รวม ${teacherData.total_count} เกียรติบัตร</p>
        </div>
      </div>
      
      <div class="border-t pt-4">
        <h4 class="font-semibold text-gray-700 mb-2">รายละเอียดรางวัล:</h4>
        ${awardTypesHtml}
      </div>
        <div class="mt-4 pt-4 border-t">
        <button class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-colors view-teacher-details" data-teacher-id="${teacherData.teacher_id || ''}" title="ดูข้อมูลครู ${teacherData.teacher_name}">
          <i class="fas fa-eye mr-2"></i>ดูรายละเอียดทั้งหมด
        </button>
      </div>
    `;

    console.log('Teacher card created with teacher_id:', teacherData.teacher_id);
    return card;
  }
  bindTableEvents() {
    // Detail buttons
    document.querySelectorAll('.btn-detail').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const certId = btn.getAttribute('data-id');
        this.showCertificateDetail(certId);
      });
    });
  }

  bindSummaryEvents() {
    // Teacher detail buttons in summary view
    document.querySelectorAll('.view-teacher-details').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const teacherId = btn.getAttribute('data-teacher-id');
        console.log('Teacher detail clicked for teacher ID:', teacherId);
        this.filterByTeacher(teacherId);
      });
    });
  }
  async showCertificateDetail(certId) {
    try {
      console.log('Loading certificate detail for ID:', certId);
      const response = await fetch(`../controllers/CertificateController.php?action=detail&id=${certId}`);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const result = await response.json();
      console.log('Certificate detail response:', result);
      
      // ตรวจสอบ response format
      let certificateData = null;
      if (result && result.success && result.data) {
        // Format แบบ {success: true, data: {...}}
        certificateData = result.data;
      } else if (result && result.id) {
        // Format แบบ object โดยตรง
        certificateData = result;
      }
        if (certificateData) {
        this.renderCertificateDetail(certificateData);
        const modal = document.getElementById('modalCertificateDetail');
        if (modal) {
          modal.classList.remove('hidden');
          // Ensure modal is on top
          modal.style.zIndex = '9999';
          // Prevent body scroll when modal is open
          document.body.style.overflow = 'hidden';
        }
      } else {
        this.showError('ไม่สามารถโหลดรายละเอียดได้');
      }
    } catch (error) {
      console.error('Error loading certificate detail:', error);
      this.showError('เกิดข้อผิดพลาดในการโหลดข้อมูล');
    }
  }
  renderCertificateDetail(cert) {
    const container = document.getElementById('certificateDetailContent');
    if (!container) return;

    console.log('Rendering certificate detail:', cert);

    container.innerHTML = `
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div>
            <label class="font-semibold text-gray-700">ชื่อนักเรียน:</label>
            <p class="text-gray-900">${cert.student_name || '-'}</p>
          </div>
          <div>
            <label class="font-semibold text-gray-700">ชั้น/ห้อง:</label>
            <p class="text-gray-900">${cert.student_class || '-'}/${cert.student_room || '-'}</p>
          </div>
          <div>
            <label class="font-semibold text-gray-700">ชื่อรางวัล:</label>
            <p class="text-gray-900">${cert.award_name || '-'}</p>
          </div>
          <div>
            <label class="font-semibold text-gray-700">ระดับรางวัล:</label>
            <p class="text-gray-900">${cert.award_level || '-'}</p>
          </div>
          <div>
            <label class="font-semibold text-gray-700">หน่วยงาน:</label>
            <p class="text-gray-900">${cert.award_organization || '-'}</p>
          </div>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="font-semibold text-gray-700">ประเภทรางวัล:</label>
            <p class="text-gray-900">${cert.award_type || '-'}</p>
          </div>
          <div>
            <label class="font-semibold text-gray-700">วันที่ได้รับ:</label>
            <p class="text-gray-900">${cert.award_date ? this.formatDate(cert.award_date) : '-'}</p>
          </div>
          <div>
            <label class="font-semibold text-gray-700">ภาคเรียน/ปีการศึกษา:</label>
            <p class="text-gray-900">${cert.term || '-'}/${cert.year || '-'}</p>
          </div>
          <div>
            <label class="font-semibold text-gray-700">ครูผู้บันทึก:</label>
            <p class="text-gray-900">${cert.teacher_name || '-'}</p>
          </div>
        </div>
      </div>
      
      <div class="mt-6">
        <label class="font-semibold text-gray-700">รายละเอียด:</label>
        <p class="text-gray-900 mt-2 p-3 bg-gray-50 rounded-lg">${cert.award_detail || '-'}</p>
      </div>
      
      ${cert.note ? `
        <div class="mt-4">
          <label class="font-semibold text-gray-700">หมายเหตุ:</label>
          <p class="text-gray-900 mt-2 p-3 bg-gray-50 rounded-lg">${cert.note}</p>
        </div>
      ` : ''}
        ${cert.certificate_image ? `
        <div class="mt-4">
          <label class="font-semibold text-gray-700">รูปเกียรติบัตร:</label>
          <div class="mt-2">
            <img src="../uploads/certificates/${cert.certificate_image}" alt="เกียรติบัตร" class="max-w-full h-auto rounded-lg shadow-lg cursor-pointer" onclick="this.requestFullscreen()">
          </div>
        </div>
      ` : ''}
      
      <div class="mt-6 pt-4 border-t flex justify-end gap-3">
        <button onclick="window.departmentCertManager.closeDetailModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
          <i class="fas fa-times mr-2"></i>ปิด
        </button>
      </div>
    `;
  }
  filterByTeacher(teacherId) {
    console.log('filterByTeacher called with:', teacherId);
    const filterElement = document.getElementById('filterTeacher');
    if (filterElement) {
      filterElement.value = teacherId;
      console.log('Filter element value set to:', filterElement.value);
    } else {
      console.error('Filter element not found');
    }
    this.switchView('table');
    this.applyFilters();
  }
  applyFilters() {
    const filters = {
      teacher: document.getElementById('filterTeacher')?.value || '',
      awardType: document.getElementById('filterAwardType')?.value || '',
      term: document.getElementById('filterDeptTerm')?.value || '',
      year: document.getElementById('filterDeptYear')?.value || ''
    };

    console.log('Applying filters:', filters);
    console.log('Original data count:', this.originalCertificatesData.length);

    // Filter จากข้อมูลดั้งเดิม
    this.certificatesData = this.originalCertificatesData.filter(cert => {
      return (!filters.teacher || cert.teacher_id == filters.teacher) &&
             (!filters.awardType || cert.award_type === filters.awardType) &&
             (!filters.term || cert.term === filters.term) &&
             (!filters.year || cert.year === filters.year);
    });

    console.log('Filtered data count:', this.certificatesData.length);
    this.renderCurrentView();
  }
  clearFilters() {
    ['filterTeacher', 'filterAwardType', 'filterDeptTerm', 'filterDeptYear'].forEach(filterId => {
      const element = document.getElementById(filterId);
      if (element) element.value = '';
    });
    
    // รีเซ็ตข้อมูลกลับไปเป็นข้อมูลดั้งเดิม
    this.certificatesData = [...this.originalCertificatesData];
    this.renderCurrentView();
  }
  closeDetailModal() {
    const modal = document.getElementById('modalCertificateDetail');
    if (modal) {
      modal.classList.add('hidden');
      // Restore body scroll when modal is closed
      document.body.style.overflow = '';
    }
  }
  async exportDepartmentReport() {
    try {
      // ส่งออกข้อมูลจาก certificatesData ที่มีอยู่แล้ว
      if (this.originalCertificatesData.length === 0) {
        this.showError('ไม่มีข้อมูลให้ส่งออก');
        return;
      }

      // สร้าง URL สำหรับ export โดยใช้ teacher IDs
      const teacherIds = this.teachersData.map(t => t.Teach_id).join(',');
      const url = `../controllers/CertificateController.php?action=export&teacherIds=${encodeURIComponent(teacherIds)}&format=csv`;
      
      // หรือใช้วิธี POST ข้อมูลไปตรงๆ
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '../controllers/CertificateController.php?action=departmentExport';
      form.target = '_blank';

      const formatInput = document.createElement('input');
      formatInput.type = 'hidden';
      formatInput.name = 'format';
      formatInput.value = 'csv';
      form.appendChild(formatInput);

      const dataInput = document.createElement('input');
      dataInput.type = 'hidden';
      dataInput.name = 'certificates';
      dataInput.value = JSON.stringify(this.originalCertificatesData);
      form.appendChild(dataInput);

      document.body.appendChild(form);
      form.submit();
      document.body.removeChild(form);

    } catch (error) {
      console.error('Export error:', error);
      this.showError('เกิดข้อผิดพลาดในการส่งออกข้อมูล');
    }
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

  showLoadingState() {
    const loadingRow = document.querySelector('.loading-row');
    if (loadingRow) {
      loadingRow.style.display = 'table-row';
    }
  }

  hideLoadingState() {
    const loadingRow = document.querySelector('.loading-row');
    if (loadingRow) {
      loadingRow.style.display = 'none';
    }
  }

  showEmptyState(container, colspan = 1) {
    container.innerHTML = `
      <tr>
        <td colspan="${colspan}" class="text-center py-8">
          <div class="flex flex-col items-center">
            <i class="fas fa-certificate text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">ยังไม่มีข้อมูลเกียรติบัตร</p>
            <p class="text-gray-400">รอครูในกลุ่มสาระบันทึกข้อมูล</p>
          </div>
        </td>
      </tr>
    `;
  }

  showErrorState() {
    const tableBody = document.querySelector('#departmentCertificateTable tbody');
    if (tableBody) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center py-8">
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

  showError(message) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: message,
        confirmButtonColor: '#ef4444'
      });
    } else {
      alert(message);
    }
  }

  showSuccess(message, timer = 3000) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'สำเร็จ',
        text: message,
        timer: timer,
        showConfirmButton: false
      });
    } else {
      alert(message);
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  window.departmentCertManager = new DepartmentCertificateManager();
});
