/**
 * Certificate Form Handler
 * Manages add/edit certificate forms and student management
 */

class CertificateFormHandler {
  constructor(manager) {
    this.manager = manager;
    this.studentCount = 1;
    this.modal = document.getElementById('modalAddCertificate');
    this.form = document.getElementById('formAddCertificate');

    this.init();
  }

  init() {
    this.initStudentManagement();
    this.initModalEvents();
    this.initFormSubmission();
  }

  initStudentManagement() {
    // NOTE: addStudentBtn is now handled in the view (views/teacher/certificate.php)
    // with the Simple Student Search Script - do not add listener here
    const studentsContainer = document.getElementById('studentsContainer');

    // Event delegation for remove buttons
    studentsContainer?.addEventListener('click', (e) => {
      if (e.target.classList.contains('remove-student')) {
        e.target.closest('.student-item').remove();
        this.updateRemoveButtons();
        this.updateStudentNumbers();
      }
    });

    this.updateRemoveButtons();
  }

  addStudentItem() {
    const studentsContainer = document.getElementById('studentsContainer');
    const studentItem = document.createElement('div');
    studentItem.className = 'student-item bg-gray-50 p-3 rounded border mb-2';

    studentItem.innerHTML = `
      <div class="flex justify-between items-center mb-2">
        <span class="font-medium text-sm">👤 นักเรียนคนที่ ${this.studentCount + 1}</span>
        <button type="button" class="remove-student bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">ลบ</button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
        <div>
          <label class="block mb-1 text-sm">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
          <input type="text" name="students[${this.studentCount}][name]" required class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300" placeholder="ชื่อ-นามสกุล" />
        </div>
        <div>
          <label class="block mb-1 text-sm">ระดับชั้น <span class="text-red-500">*</span></label>
          <select name="students[${this.studentCount}][class]" required class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300">
            <option value="">-- เลือกชั้น --</option>
            <option value="ม.1">ม.1</option>
            <option value="ม.2">ม.2</option>
            <option value="ม.3">ม.3</option>
            <option value="ม.4">ม.4</option>
            <option value="ม.5">ม.5</option>
            <option value="ม.6">ม.6</option>
          </select>
        </div>
        <div>
          <label class="block mb-1 text-sm">ห้อง <span class="text-red-500">*</span></label>
          <input type="text" name="students[${this.studentCount}][room]" required class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300" placeholder="เช่น 1, 2, 3" />
        </div>
      </div>
      <div class="mt-2">
        <label class="block mb-1 text-sm">รูปเกียรติบัตร</label>
        <input type="file" name="students[${this.studentCount}][image]" accept="image/*" class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300" />
        <p class="text-xs text-gray-500 mt-1">รองรับไฟล์รูปภาพ (JPG, PNG, GIF) ขนาดไม่เกิน 5MB</p>
      </div>
    `;

    studentsContainer.appendChild(studentItem);
    this.studentCount++;
    this.updateRemoveButtons();
  }

  updateRemoveButtons() {
    const studentsContainer = document.getElementById('studentsContainer');
    const studentItems = studentsContainer?.querySelectorAll('.student-item') || [];

    studentItems.forEach((item) => {
      const removeBtn = item.querySelector('.remove-student');
      if (studentItems.length === 1) {
        removeBtn?.classList.add('hidden');
      } else {
        removeBtn?.classList.remove('hidden');
      }
    });
  }

  updateStudentNumbers() {
    const studentsContainer = document.getElementById('studentsContainer');
    const studentItems = studentsContainer?.querySelectorAll('.student-item') || [];

    studentItems.forEach((item, index) => {
      const label = item.querySelector('span');
      if (label) {
        label.textContent = `👤 นักเรียนคนที่ ${index + 1}`;
      }
    });
  }
  initModalEvents() {
    const btnClose = document.getElementById('closeModalAddCertificate');
    const btnCancel = document.getElementById('cancelAddCertificate');

    // เฉพาะปุ่ม X และ ยกเลิก เท่านั้นที่ปิดได้
    btnClose?.addEventListener('click', (e) => {
      e.preventDefault();
      this.confirmCloseModal();
    });

    btnCancel?.addEventListener('click', (e) => {
      e.preventDefault();
      this.confirmCloseModal();
    });

    // ลบการปิด modal เมื่อคลิกข้างนอก
    // this.modal?.addEventListener('click', (e) => {
    //   if (e.target === this.modal) {
    //     this.hideModal();
    //   }
    // });
  }

  // เพิ่มการยืนยันก่อนปิด modal
  confirmCloseModal() {
    const hasData = this.checkFormData();

    if (hasData) {
      Swal.fire({
        title: 'ยืนยันการออก',
        text: 'ข้อมูลที่กรอกจะสูญหาย ต้องการออกจริงหรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ออก',
        cancelButtonText: 'ยกเลิก'
      }).then((result) => {
        if (result.isConfirmed) {
          this.hideModal();
        }
      });
    } else {
      this.hideModal();
    }
  }

  // ตรวจสอบว่ามีข้อมูลในฟอร์มหรือไม่
  checkFormData() {
    const inputs = this.form?.querySelectorAll('input[type="text"], input[type="date"], select, textarea');
    if (!inputs) return false;

    for (let input of inputs) {
      if (input.value.trim() !== '') {
        return true;
      }
    }
    return false;
  }

  initFormSubmission() {
    this.form?.addEventListener('submit', (e) => {
      e.preventDefault();
      this.handleFormSubmit();
    });
  }

  async handleFormSubmit() {
    const formData = new FormData(this.form);
    const mode = this.form.getAttribute('data-mode');

    // Collect student data
    const students = this.collectStudentData();

    // Validate students
    if (!this.validateStudents(students, mode)) {
      return;
    }

    // Prepare certificate data
    const certificateData = this.prepareCertificateData(formData, students);

    try {
      // Upload images for each student
      const studentsWithImages = await this.uploadStudentImages(students);
      certificateData.students = studentsWithImages;

      // Save certificate
      await this.saveCertificate(certificateData);
    } catch (error) {
      //   console.error('Form submission error:', error);
      this.manager.showError('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
    }
  }

  collectStudentData() {
    const students = [];
    const studentItems = document.querySelectorAll('.student-item');

    studentItems.forEach((item, index) => {
      const idInput = item.querySelector('.student-id-hidden');
      const nameInput = item.querySelector('.student-name-input');
      const classInput = item.querySelector('.student-class-input');
      const roomInput = item.querySelector('.student-room-input');
      const imageInput = item.querySelector('.student-file-input');

      const name = nameInput?.value?.trim();
      const studentClass = classInput?.value?.trim();
      const room = roomInput?.value?.trim();

      if (name && studentClass && room) {
        students.push({
          student_id: idInput?.value || null,
          name: name,
          class: studentClass,
          room: room,
          image: imageInput?.files && imageInput.files[0] ? imageInput.files[0] : null
        });
      }
    });

    return students;
  }

  validateStudents(students, mode) {
    if (mode === 'edit') {
      if (students.length !== 1) {
        this.manager.showError('กรุณากรอกข้อมูลนักเรียน 1 คน');
        return false;
      }
    } else {
      if (students.length === 0) {
        this.manager.showError('กรุณากรอกข้อมูลนักเรียนอย่างน้อย 1 คน');
        return false;
      }
    }
    return true;
  }

  prepareCertificateData(formData, students) {
    return {
      students: students,
      award_name: formData.get('award_name'),
      award_level: formData.get('award_level'),
      award_organization: formData.get('award_organization'),
      award_type: formData.get('award_type'),
      award_detail: formData.get('award_detail'),
      award_date: formData.get('award_date'),
      note: formData.get('note'),
      term: formData.get('term'),
      year: formData.get('year')
    };
  }

  async uploadStudentImages(students) {
    const studentsWithImages = [];

    for (let student of students) {
      let imageFilename = null;

      if (student.image && student.image.size > 0) {
        try {
          const uploadData = new FormData();
          uploadData.append('certificate_image', student.image);

          const response = await fetch('../controllers/CertificateController.php?action=upload', {
            method: 'POST',
            body: uploadData
          });

          const uploadResult = await response.json();

          if (uploadResult.success) {
            imageFilename = uploadResult.filename;
          } else {
            throw new Error(`ไม่สามารถอัพโหลดรูปของ ${student.name}: ${uploadResult.message}`);
          }
        } catch (error) {
          throw new Error(`เกิดข้อผิดพลาดในการอัพโหลดรูปของ ${student.name}: ${error.message}`);
        }
      }

      studentsWithImages.push({
        student_id: student.student_id,
        name: student.name,
        class: student.class,
        room: student.room,
        certificate_image: imageFilename
      });
    }

    return studentsWithImages;
  }

  async saveCertificate(certificateData) {
    const mode = this.form.getAttribute('data-mode');
    const certId = this.form.getAttribute('data-id');

    let url = '../controllers/CertificateController.php?action=create';

    if (mode === 'edit' && certId) {
      url = '../controllers/CertificateController.php?action=update';
      certificateData.id = certId;
    }

    this.manager.showLoading('กำลังบันทึก...');

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(certificateData)
      });

      const result = await response.json();

      if (result.success) {
        let successMessage = mode === 'edit' ? 'แก้ไขเกียรติบัตรเรียบร้อยแล้ว' : result.message;

        if (mode !== 'edit' && result.term_info) {
          successMessage += `<br><small class="text-gray-600">บันทึกในภาคเรียนที่ ${result.term_info.term} ปีการศึกษา ${result.term_info.year}</small>`;
        }

        this.manager.showSuccess(successMessage);
        this.hideModal();
        this.resetForm();

        // Reload data
        await Promise.all([
          this.manager.loadCertificates(),
          this.manager.loadStatistics()
        ]);
      } else {
        this.manager.showError(result.message);
      }
    } catch (error) {
      //   console.error('Save error:', error);
      //   this.manager.showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    } finally {
      this.manager.closeLoading();
    }
  }

  async editCertificate(certId) {
    try {
      const response = await fetch(`../controllers/CertificateController.php?action=detail&id=${encodeURIComponent(certId)}`);
      const cert = await response.json();

      if (cert.success === false) {
        this.manager.showError(cert.message);
        return;
      }

      // Setup edit mode
      this.setupEditMode(cert);
      this.populateForm(cert);
      this.showModal();
    } catch (error) {
      //   console.error('Edit error:', error);
      //   this.manager.showError('ไม่สามารถโหลดข้อมูลได้');
    }
  }

  setupEditMode(cert) {
    // Change modal title
    const modalTitle = document.getElementById('modalTitleText');
    if (modalTitle) {
      modalTitle.textContent = 'แก้ไขเกียรติบัตร';
    }

    // Hide add student button
    const addStudentBtn = document.getElementById('addStudentBtn');
    if (addStudentBtn) {
      addStudentBtn.style.display = 'none';
    }

    // Hide remove button for first student
    const firstStudentRemoveBtn = document.querySelector('.student-item .remove-student');
    if (firstStudentRemoveBtn) {
      firstStudentRemoveBtn.classList.add('hidden');
    }

    // Remove other students
    const otherStudents = document.querySelectorAll('.student-item:not(:first-child)');
    otherStudents.forEach(item => item.remove());

    // Set form mode
    this.form.setAttribute('data-mode', 'edit');
    this.form.setAttribute('data-id', cert.id);
  }

  populateForm(cert) {
    // Populate student data
    const firstStudent = document.querySelector('.student-item');
    if (firstStudent) {
      const idInput = firstStudent.querySelector('.student-id-hidden');
      const nameInput = firstStudent.querySelector('.student-name-input');
      const classInput = firstStudent.querySelector('.student-class-input');
      const roomInput = firstStudent.querySelector('.student-room-input');
      const searchInput = firstStudent.querySelector('.student-search');

      if (idInput) idInput.value = cert.student_id || '';
      if (nameInput) nameInput.value = cert.student_name || '';
      if (classInput) classInput.value = cert.student_class || '';
      if (roomInput) roomInput.value = cert.student_room || '';
      if (searchInput) searchInput.value = cert.student_name || '';
    }

    // Populate form fields
    const f = this.form;
    if (f.award_name) f.award_name.value = cert.award_name || '';
    if (f.award_level) f.award_level.value = cert.award_level || '';
    if (f.award_organization) f.award_organization.value = cert.award_organization || '';
    if (f.award_type) f.award_type.value = cert.award_type || '';
    if (f.award_detail) f.award_detail.value = cert.award_detail || '';
    if (f.award_date) f.award_date.value = cert.award_date || '';
    if (f.note) f.note.value = cert.note || '';

    // Set term and year
    const termInput = document.getElementById('termInput');
    const yearInput = document.getElementById('yearInput');
    if (termInput && cert.term) termInput.value = cert.term;
    if (yearInput && cert.year) yearInput.value = cert.year;
  } showModal() {
    this.modal?.classList.remove('hidden');

    // เพิ่ม class เพื่อป้องกันการ scroll ของ body
    document.body.classList.add('modal-open');

    // Focus management และ trap focus ใน modal
    setTimeout(() => {
      const firstInput = this.modal?.querySelector('input[name="students[0][name]"]');
      if (firstInput) {
        firstInput.focus();
      }
    }, 100);
  }

  hideModal() {
    this.modal?.classList.add('hidden');

    // ลบ class เพื่อคืนค่าการ scroll ของ body
    document.body.classList.remove('modal-open');

    this.resetForm();
  }

  resetForm() {
    this.form?.reset();
    this.form?.removeAttribute('data-mode');
    this.form?.removeAttribute('data-id');

    // Reset modal title
    const modalTitle = document.getElementById('modalTitleText');
    if (modalTitle) {
      modalTitle.textContent = 'บันทึกเกียรติบัตรใหม่';
    }

    // Show add student button
    const addStudentBtn = document.getElementById('addStudentBtn');
    if (addStudentBtn) {
      addStudentBtn.style.display = 'flex';
    }

    // Reset students
    this.resetStudents();

    // Set current term info
    if (this.manager.currentTermInfo) {
      const termInput = document.getElementById('termInput');
      const yearInput = document.getElementById('yearInput');
      if (termInput) termInput.value = this.manager.currentTermInfo.term;
      if (yearInput) yearInput.value = this.manager.currentTermInfo.year;
    }

    this.studentCount = 1;
  }

  resetStudents() {
    const studentsContainer = document.getElementById('studentsContainer');
    const firstStudent = studentsContainer?.querySelector('.student-item');

    if (firstStudent) {
      // Reset first student
      const nameInput = firstStudent.querySelector('input[name="students[0][name]"]');
      const classSelect = firstStudent.querySelector('select[name="students[0][class]"]');
      const roomInput = firstStudent.querySelector('input[name="students[0][room]"]');

      if (nameInput) nameInput.value = '';
      if (classSelect) classSelect.value = '';
      if (roomInput) roomInput.value = '';

      // Remove other students
      const otherStudents = studentsContainer.querySelectorAll('.student-item:not(:first-child)');
      otherStudents.forEach(item => item.remove());

      // Hide remove button for first student
      const firstStudentRemoveBtn = firstStudent.querySelector('.remove-student');
      if (firstStudentRemoveBtn) {
        firstStudentRemoveBtn.style.display = 'none';
      }
    }
  }
}
