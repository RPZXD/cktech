/**
 * Department Certificate Management JavaScript
 * Handles certificate reports and summaries for department heads
 */

class DepartmentCertificateManager {
  constructor(config) {
    this.department = config.department;
    this.baseUrl = '../';
    this.certificates = [];
    this.teachers = [];
    this.currentView = 'table';

    this.init();
  }

  init() {
    // Check if DOM is already loaded
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => {
        this.bindEvents();
        this.loadInitialData();
      });
    } else {
      // DOM already loaded, run immediately
      this.bindEvents();
      this.loadInitialData();
    }
  }

  bindEvents() {
    // Filters
    ['filterTeacher', 'filterAwardType', 'filterDeptTerm', 'filterDeptYear'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.addEventListener('change', () => this.applyFilters());
    });

    // View Toggles
    document.getElementById('btnTableView')?.addEventListener('click', () => this.switchView('table'));
    document.getElementById('btnSummaryView')?.addEventListener('click', () => this.switchView('summary'));

    // Export
    document.getElementById('btnExportDepartment')?.addEventListener('click', () => this.handleExport());

    // Clear Filter
    document.getElementById('btnClearDeptFilter')?.addEventListener('click', () => this.clearFilters());
  }

  async loadInitialData() {
    this.showLoading('กำลังโหลดข้อมูลเกียรติบัตร...');
    try {
      // Load Teachers
      const tRes = await fetch(`${this.baseUrl}controllers/DepartmentController.php?action=listTeachers&department=${encodeURIComponent(this.department)}`);
      this.teachers = await tRes.json();
      this.populateTeacherFilter();

      // Load All Certificates for these teachers
      const allCerts = [];
      for (const t of this.teachers) {
        const cRes = await fetch(`${this.baseUrl}controllers/CertificateController.php?action=list&teacherId=${t.Teach_id}`);
        const result = await cRes.json();
        const certs = result.data || result || [];
        allCerts.push(...certs);
      }

      this.certificates = allCerts;
      this.render();
      this.updateStats();
      this.populateOtherFilters();
    } catch (error) {
      console.error('Error loading initial data:', error);
      Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
    } finally {
      this.hideLoading();
    }
  }

  render() {
    if (this.currentView === 'table') {
      this.renderTable();
      this.renderMobileCards();
    } else {
      this.renderSummary();
    }
  }

  renderMobileCards() {
    const container = document.getElementById('mobileCertList');
    if (!container) return;

    const filtered = this.getFilteredData();
    container.innerHTML = '';

    if (filtered.length === 0) {
      container.innerHTML = `
        <div class="text-center py-12">
          <div class="text-4xl mb-3 opacity-30">📭</div>
          <p class="text-slate-400 font-bold">ไม่พบเกียรติบัตร</p>
        </div>
      `;
      return;
    }

    filtered.slice(0, 30).forEach((c, i) => {
      const card = document.createElement('div');
      card.className = 'cert-mobile-card';
      card.innerHTML = `
        <div class="flex items-start justify-between gap-3 mb-3">
          <div class="flex-1 min-w-0">
            <p class="font-black text-slate-800 text-sm truncate">${c.student_name}</p>
            <p class="text-[10px] font-bold text-slate-400">ม.${c.student_class}/${c.student_room} • ${c.teacher_name}</p>
          </div>
          ${this.getAwardBadge(c.award_type)}
        </div>
        <p class="text-xs text-slate-600 mb-3 line-clamp-1">${c.award_name || '-'}</p>
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-bold text-slate-400">${this.formatThaiDate(c.award_date)}</span>
          <button onclick="window.departmentCertManager.showDetail(${c.id})" class="px-3 py-1.5 bg-amber-500 text-white rounded-lg text-[10px] font-bold active:scale-95 transition-transform">
            <i class="fas fa-eye mr-1"></i> ดู
          </button>
        </div>
      `;
      container.appendChild(card);
    });

    if (filtered.length > 30) {
      container.innerHTML += `
        <div class="text-center py-3">
          <p class="text-xs font-bold text-slate-400">แสดง 30 รายการจาก ${filtered.length} รายการ</p>
        </div>
      `;
    }
  }

  renderTable() {
    const tbody = document.querySelector('#departmentCertificateTable tbody');
    if (!tbody) return;

    const filtered = this.getFilteredData();
    tbody.innerHTML = '';

    if (filtered.length === 0) {
      tbody.innerHTML = '<tr><td colspan="9" class="p-12 text-center text-slate-400 font-bold italic font-medium">ไม่พบข้อมูลเกียรติบัตรที่ค้นหา</td></tr>';
      return;
    }

    filtered.forEach(c => {
      const tr = document.createElement('tr');
      tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors border-b border-slate-100 dark:border-slate-800';
      tr.innerHTML = `
                <td class="p-6 font-bold text-slate-700 dark:text-slate-300 text-xs">${c.teacher_name}</td>
                <td class="p-6 font-black text-slate-800 dark:text-white">${c.student_name}</td>
                <td class="p-6 text-center">
                    <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-full text-[10px] font-black text-slate-500">ม.${c.student_class}/${c.student_room}</span>
                </td>
                <td class="p-6 text-slate-600 dark:text-slate-400 text-xs font-medium">${c.award_name || '-'}</td>
                <td class="p-6 text-center">
                    <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black">${c.award_level || '-'}</span>
                </td>
                <td class="p-6 text-center">
                    ${this.getAwardBadge(c.award_type)}
                </td>
                <td class="p-6 text-center text-slate-500 text-[10px] font-bold">${this.formatThaiDate(c.award_date)}</td>
                <td class="p-6 text-center text-slate-400 text-[10px] font-black">${c.term || '-'}/${c.year || '-'}</td>
                <td class="p-6 text-center">
                    <button onclick="window.departmentCertManager.showDetail(${c.id})" class="p-2 bg-amber-100 text-amber-600 rounded-xl hover:bg-amber-200 transition-all">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            `;
      tbody.appendChild(tr);
    });
  }

  renderSummary() {
    const container = document.getElementById('teacherSummaryContainer');
    if (!container) return;

    const filtered = this.getFilteredData();
    container.innerHTML = '';

    const groups = {};
    filtered.forEach(c => {
      if (!groups[c.teacher_id]) {
        groups[c.teacher_id] = { name: c.teacher_name, count: 0, gold: 0, silver: 0, bronze: 0 };
      }
      groups[c.teacher_id].count++;
      if (c.award_type.includes('ชนะเลิศ') && !c.award_type.includes('รอง')) groups[c.teacher_id].gold++;
      else if (c.award_type.includes('รองชนะเลิศ')) groups[c.teacher_id].silver++;
    });

    Object.values(groups).forEach(g => {
      const card = document.createElement('div');
      card.className = 'glass p-8 rounded-[2rem] border border-white/20 shadow-lg hover:-translate-y-2 transition-transform';
      card.innerHTML = `
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center text-blue-600 text-xl font-bold">
                        ${g.name.charAt(0)}
                    </div>
                    <div>
                        <h4 class="font-black text-slate-800 dark:text-white">${g.name}</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Registered Teacher</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Total</p>
                        <p class="text-2xl font-black text-blue-600">${g.count}</p>
                    </div>
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-2xl">
                        <p class="text-[10px] font-black text-amber-500 uppercase mb-1">Winner</p>
                        <p class="text-2xl font-black text-amber-600">${g.gold}</p>
                    </div>
                </div>
            `;
      container.appendChild(card);
    });
  }

  getFilteredData() {
    const filters = {
      teacher: document.getElementById('filterTeacher')?.value,
      awardType: document.getElementById('filterAwardType')?.value,
      term: document.getElementById('filterDeptTerm')?.value,
      year: document.getElementById('filterDeptYear')?.value
    };

    return this.certificates.filter(c => {
      if (filters.teacher && c.teacher_id != filters.teacher) return false;
      if (filters.awardType && c.award_type !== filters.awardType) return false;
      if (filters.term && c.term !== filters.term) return false;
      if (filters.year && c.year !== filters.year) return false;
      return true;
    });
  }

  applyFilters() {
    this.render();
  }

  clearFilters() {
    ['filterTeacher', 'filterAwardType', 'filterDeptTerm', 'filterDeptYear'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });
    this.render();
  }

  switchView(view) {
    this.currentView = view;
    const btnTable = document.getElementById('btnTableView');
    const btnSummary = document.getElementById('btnSummaryView');
    const tableView = document.getElementById('tableView');
    const summaryView = document.getElementById('summaryView');

    if (view === 'table') {
      btnTable.className = 'px-6 py-2 rounded-xl text-sm font-black transition-all bg-white dark:bg-slate-700 text-blue-600 shadow-sm';
      btnSummary.className = 'px-6 py-2 rounded-xl text-sm font-black transition-all text-slate-500 hover:text-slate-700 dark:hover:text-slate-300';
      tableView.classList.remove('hidden');
      summaryView.classList.add('hidden');
    } else {
      btnSummary.className = 'px-6 py-2 rounded-xl text-sm font-black transition-all bg-white dark:bg-slate-700 text-blue-600 shadow-sm';
      btnTable.className = 'px-6 py-2 rounded-xl text-sm font-black transition-all text-slate-500 hover:text-slate-700 dark:hover:text-slate-300';
      summaryView.classList.remove('hidden');
      tableView.classList.add('hidden');
    }
    this.render();
  }

  updateStats() {
    const total = this.certificates.length;
    const uniqueTeachers = new Set(this.certificates.map(c => c.teacher_id)).size;

    // Find Top Teacher
    const counts = {};
    this.certificates.forEach(c => {
      counts[c.teacher_name] = (counts[c.teacher_name] || 0) + 1;
    });
    let top = '-', max = 0;
    Object.entries(counts).forEach(([name, count]) => {
      if (count > max) { max = count; top = name; }
    });

    const now = new Date();
    const thisMonth = this.certificates.filter(c => {
      const d = new Date(c.award_date);
      return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
    }).length;

    const setVal = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = val;
    };

    setVal('deptTotalCerts', total);
    setVal('deptTotalTeachers', uniqueTeachers);
    setVal('deptTopTeacher', top === '-' ? '-' : `${top} (${max})`);
    setVal('deptThisMonth', thisMonth);
  }

  populateTeacherFilter() {
    const sel = document.getElementById('filterTeacher');
    if (!sel) return;
    this.teachers.forEach(t => {
      const opt = document.createElement('option');
      opt.value = t.Teach_id;
      opt.textContent = t.Teach_name;
      sel.appendChild(opt);
    });
  }

  populateOtherFilters() {
    const types = [...new Set(this.certificates.map(c => c.award_type))];
    const terms = [...new Set(this.certificates.map(c => c.term))];
    const years = [...new Set(this.certificates.map(c => c.year))];

    const fill = (id, items) => {
      const sel = document.getElementById(id);
      if (!sel) return;
      items.filter(Boolean).sort().forEach(item => {
        const opt = document.createElement('option');
        opt.value = item;
        opt.textContent = id.includes('Term') ? `เทอม ${item}` : id.includes('Year') ? `ปีการศึกษา ${item}` : item;
        sel.appendChild(opt);
      });
    };

    fill('filterAwardType', types);
    fill('filterDeptTerm', terms);
    fill('filterDeptYear', years);
  }

  async showDetail(id) {
    this.showLoading('กำลังโหลดรายละเอียด...');
    try {
      const res = await fetch(`${this.baseUrl}controllers/CertificateController.php?action=detail&id=${id}`);
      const result = await res.json();
      const c = result.data || result;

      let html = `
                <div class="space-y-6 text-left">
                    <div class="flex items-center gap-6 p-6 bg-slate-50 dark:bg-slate-800/50 rounded-[2rem]">
                        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-3xl">👤</div>
                        <div>
                            <h4 class="text-xl font-black text-slate-800 dark:text-white">${c.student_name}</h4>
                            <p class="text-sm font-bold text-slate-500">ม.${c.student_class}/${c.student_room} | เลขประจำตัว ${c.student_id || '-'}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 border border-slate-100 dark:border-slate-800 rounded-2xl">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-1">รางวัล</p>
                            <p class="font-bold text-slate-700 dark:text-slate-300">${c.award_name}</p>
                        </div>
                        <div class="p-4 border border-slate-100 dark:border-slate-800 rounded-2xl">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-1">ประเภท</p>
                            <p class="font-bold text-slate-700 dark:text-slate-300">${c.award_type}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-2">ข้อมูลเพิ่มเติม</p>
                        <div class="p-4 bg-amber-50/50 dark:bg-amber-900/10 rounded-2xl border border-amber-100 dark:border-amber-900/30">
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">${c.award_detail || 'ไม่มีรายละเอียดเพิ่มเติม'}</p>
                            <div class="flex items-center gap-4 text-[10px] font-black text-amber-600 uppercase">
                                <span>📅 ${this.formatThaiDate(c.award_date)}</span>
                                <span>🏫 ${c.award_organization || '-'}</span>
                            </div>
                        </div>
                    </div>

                    ${c.certificate_image ? `
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2">รูปเกียรติบัตร</p>
                            <img src="${this.baseUrl}uploads/certificates/${c.certificate_image}" class="w-full h-auto rounded-[2rem] shadow-lg cursor-pointer hover:opacity-90 transition-opacity" onclick="window.departmentCertManager.previewImage(this.src)">
                        </div>
                    ` : ''}
                </div>
            `;

      Swal.fire({
        html: html,
        width: '600px',
        showConfirmButton: false,
        showCloseButton: true,
        customClass: { popup: 'rounded-[2.5rem]' }
      });
    } catch (error) {
      console.error('Error showing detail:', error);
      Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
    } finally {
      this.hideLoading();
    }
  }

  previewImage(src) {
    Swal.fire({ imageUrl: src, imageWidth: '100%', showConfirmButton: false, showCloseButton: true, customClass: { popup: 'rounded-[2.5rem] overflow-hidden' } });
  }

  getAwardBadge(type) {
    if (type.includes('ชนะเลิศ') && !type.includes('รอง')) return '<span class="px-2 py-1 bg-amber-100 text-amber-600 rounded-lg text-[10px] font-black ring-1 ring-amber-200">🥇 ชนะเลิศ</span>';
    if (type.includes('รองชนะเลิศ')) return '<span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black ring-1 ring-slate-200">🥈 รองชนะเลิศ</span>';
    if (type.includes('ชมเชย')) return '<span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black ring-1 ring-blue-100">🥉 ชมเชย</span>';
    return `<span class="px-2 py-1 bg-slate-50 text-slate-400 rounded-lg text-[10px] font-black">${type}</span>`;
  }

  formatThaiDate(dStr) {
    if (!dStr) return '-';
    const months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    const d = new Date(dStr);
    return `${d.getDate()} ${months[d.getMonth() + 1]} ${d.getFullYear() + 543}`;
  }

  handleExport() {
    const filtered = this.getFilteredData();
    if (filtered.length === 0) {
      Swal.fire('ไม่มีข้อมูล', 'ไม่พบข้อมูลให้ส่งออก', 'warning');
      return;
    }

    const csvContent = "data:text/csv;charset=utf-8,"
      + ["ชื่อครู,ชื่อนักเรียน,ชั้น,ห้อง,รางวัล,ประเภท,วันที่,เทอม,ปี"].join(",") + "\n"
      + filtered.map(c => [
        c.teacher_name,
        c.student_name,
        c.student_class,
        c.student_room,
        `"${c.award_name}"`,
        c.award_type,
        c.award_date,
        c.term,
        c.year
      ].join(",")).join("\n");

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `certificate_report_${this.department}_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  showLoading(title = 'กำลังโหลด...') {
    Swal.fire({ title: title, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
  }

  hideLoading() { Swal.close(); }

  closeDetailModal() { Swal.close(); }
}
