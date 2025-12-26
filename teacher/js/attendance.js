/**
 * Attendance Page JavaScript
 * MVC Pattern - Handles attendance grid interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    const config = window.ATTENDANCE_CONFIG || {};
    const teacherId = config.teacherId;

    // DOM Elements
    const subjectSelect = document.getElementById('subjectSelect');
    const classSelect = document.getElementById('classSelect');
    const customClassGroup = document.getElementById('customClassGroup');
    const customClassInput = document.getElementById('customClassInput');
    const monthInput = document.getElementById('monthInput');
    const subjectHint = document.getElementById('subjectHint');
    const loadBtn = document.getElementById('loadAttendanceBtn');
    const resetBtn = document.getElementById('resetFilterBtn');
    const exportBtn = document.getElementById('exportCsvBtn');
    const printBtn = document.getElementById('printGridBtn');
    const gridHost = document.getElementById('gridHost');
    const mobileCardsHost = document.getElementById('mobileCardsHost');
    const legendHost = document.getElementById('legendHost');
    const gridMeta = document.getElementById('gridMeta');
    const filterTimestamp = document.getElementById('filterTimestamp');

    // State
    const state = {
        subjects: [],
        activeSubject: null,
        statusMeta: {},
        summaryColumns: []
    };

    // Initialize
    updateTimestamp();
    wireEvents();
    loadSubjects();

    function updateTimestamp() {
        if (filterTimestamp) {
            const formatter = new Intl.DateTimeFormat('th-TH', { dateStyle: 'medium', timeStyle: 'short' });
            filterTimestamp.textContent = formatter.format(new Date());
        }
    }

    function wireEvents() {
        subjectSelect.addEventListener('change', handleSubjectChange);
        classSelect.addEventListener('change', handleClassChange);
        loadBtn.addEventListener('click', handleLoad);
        resetBtn.addEventListener('click', resetFilters);
        if (exportBtn) exportBtn.addEventListener('click', exportGridToCSV);
        if (printBtn) printBtn.addEventListener('click', printGrid);
    }

    async function loadSubjects() {
        subjectHint.textContent = 'กำลังโหลดรายวิชา...';
        try {
            const params = new URLSearchParams({ action: 'list', onlyOpen: 1 });
            if (teacherId) params.append('teacherId', teacherId);
            const response = await fetch('../controllers/SubjectController.php?' + params.toString(), {
                credentials: 'same-origin'
            });
            const data = await response.json();
            state.subjects = Array.isArray(data) ? data : [];
            renderSubjectOptions();
            subjectHint.textContent = state.subjects.length ? 'เลือกวิชาเพื่อเริ่มใช้งาน' : 'ไม่พบวิชาที่เปิดสอน';
        } catch (error) {
            console.error(error);
            subjectHint.textContent = 'ไม่สามารถโหลดรายวิชาได้';
        }
    }

    function renderSubjectOptions() {
        let html = '<option value="">เลือกรายวิชา</option>';
        state.subjects.forEach(sub => {
            html += `<option value="${sub.id}">${sub.code || ''} ${sub.name}</option>`;
        });
        subjectSelect.innerHTML = html;
    }

    function handleSubjectChange() {
        const subjectId = subjectSelect.value;
        const subject = state.subjects.find(item => String(item.id) === String(subjectId));
        state.activeSubject = subject || null;

        if (!subject) {
            subjectHint.textContent = 'เลือกวิชาเพื่อดึงข้อมูล';
            classSelect.innerHTML = '<option value="">เลือกวิชาก่อน</option>';
            classSelect.disabled = true;
            customClassGroup.classList.add('hidden');
            return;
        }

        subjectHint.textContent = `${subject.code || ''} | ระดับ ${subject.level || '-'}`;
        populateRooms(subject);
    }

    function populateRooms(subject) {
        const rooms = [];
        const seen = new Set();

        if (subject && Array.isArray(subject.class_periods)) {
            subject.class_periods.forEach(period => {
                if (!period || !period.class_room) return;
                const room = String(period.class_room).trim();
                if (!room || seen.has(room)) return;
                seen.add(room);
                rooms.push(room);
            });
        }

        classSelect.disabled = false;
        customClassGroup.classList.add('hidden');

        if (!rooms.length) {
            classSelect.innerHTML = '<option value="__custom__">พิมพ์ห้องเรียนเอง</option>';
            classSelect.value = '__custom__';
            customClassGroup.classList.remove('hidden');
            customClassInput.focus();
            return;
        }

        let html = '<option value="">เลือกห้องเรียน</option>';
        rooms.forEach(room => {
            html += `<option value="${room}">${room}</option>`;
        });
        html += '<option value="__custom__">พิมพ์ห้องเรียนเอง…</option>';
        classSelect.innerHTML = html;
        classSelect.value = rooms[0];
    }

    function handleClassChange() {
        if (classSelect.value === '__custom__') {
            customClassGroup.classList.remove('hidden');
            customClassInput.focus();
        } else {
            customClassGroup.classList.add('hidden');
        }
    }

    function getSelectedClassRoom() {
        if (classSelect.value === '__custom__') {
            return customClassInput.value.trim();
        }
        return classSelect.value.trim();
    }

    function handleLoad() {
        const subjectId = subjectSelect.value;
        const classRoom = getSelectedClassRoom();
        const month = monthInput.value;

        if (!subjectId) {
            toast('กรุณาเลือกรายวิชา');
            return;
        }
        if (!classRoom) {
            toast('กรุณาเลือกหรือระบุห้องเรียน');
            return;
        }
        if (!month) {
            toast('กรุณาเลือกเดือน');
            return;
        }

        fetchAttendanceGrid({ subjectId, classRoom, month });
    }

    async function fetchAttendanceGrid({ subjectId, classRoom, month }) {
        setGridLoading(true, 'กำลังโหลดข้อมูลการเข้าเรียน...');

        const params = new URLSearchParams({
            action: 'calendar_grid',
            subject_id: subjectId,
            class_room: classRoom,
            month: month
        });
        if (teacherId) params.append('teacher_id', teacherId);

        try {
            const response = await fetch('../controllers/AttendanceController.php?' + params.toString(), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const json = await response.json();

            if (!json.success) throw new Error(json.error || 'ไม่สามารถโหลดข้อมูล');

            const payload = json.data || {};
            state.statusMeta = payload.summary?.status_meta || {};
            state.summaryColumns = payload.summary?.columns || [];
            renderGrid(payload, classRoom);
        } catch (error) {
            console.error(error);
            toast(error.message || 'เกิดข้อผิดพลาด');
            showEmpty(error.message || 'ไม่พบข้อมูล');
        } finally {
            setGridLoading(false);
        }
    }

    function renderGrid(data, fallbackClass) {
        if (!data || !Array.isArray(data.days) || data.days.length === 0) {
            showEmpty('ไม่พบรายงานในเดือนที่เลือก');
            return;
        }

        const students = Array.isArray(data.students) ? data.students : [];
        if (!students.length) {
            showEmpty('ยังไม่มีข้อมูลเช็คชื่อ');
            return;
        }

        const days = Array.isArray(data.days) ? data.days : [];

        // Filter to only days that have actual attendance data
        const daysWithData = days.filter(day => {
            if (day == null) return false;
            // Check if day has report flag
            if (day.has_report || day.report_count) return true;
            // Check if any student has status for this date
            if (students.some(s => s && s.statuses && s.statuses[day.date])) return true;
            return false;
        });

        // If no days with data, show empty message
        if (!daysWithData.length) {
            showEmpty('ไม่พบวันที่มีการบันทึกในเดือนนี้');
            return;
        }

        // Build table header
        const headerDays = daysWithData.map(day => `
            <th class="day-head" title="${day.weekday_th || day.weekday}">
                ${day.day}
                <div class="text-[10px] opacity-70">${day.weekday_th || ''}</div>
            </th>
        `).join('');

        const summaryCols = (state.summaryColumns || []).map(col =>
            `<th class="summary-cell" title="${col.label}">${col.emoji}</th>`
        ).join('');

        const summaryColspan = state.summaryColumns.length || 0;
        const headSecond = `<tr>${headerDays}${summaryCols}</tr>`;
        const headFirst = `<tr>
            <th rowspan="2" class="name-col">ชื่อ - สกุล</th>
            <th colspan="${daysWithData.length}" class="text-xs">วันที่มีการสอน (${daysWithData.length} วัน)</th>
            ${summaryColspan ? `<th colspan="${summaryColspan}" class="text-xs">สรุป</th>` : ''}
        </tr>`;

        // Build table body
        const bodyRows = students.map((student, idx) => {
            const statusCells = daysWithData.map(day => {
                const status = student.statuses?.[day.date] || '';
                const visual = getStatusVisual(status);
                return `<td class="status-cell ${visual.className}" title="${visual.label}">${visual.icon}</td>`;
            }).join('');

            const summaryCells = (state.summaryColumns || []).map(col => {
                const value = student.totals?.[col.key] ?? 0;
                return `<td class="summary-cell" title="${col.label}">${value}</td>`;
            }).join('');

            const no = student.student_no ? `(${student.student_no})` : `#${idx + 1}`;
            const name = student.student_name || 'ไม่พบชื่อ';

            return `<tr>
                <td class="name-col">
                    <div class="font-semibold text-indigo-600 dark:text-indigo-400 text-xs md:text-sm">${no} ${name}</div>
                    <div class="text-[10px] text-gray-500">ID: ${student.student_id}</div>
                </td>
                ${statusCells}
                ${summaryCells}
            </tr>`;
        }).join('');

        const tableHtml = `
            <table class="attendance-table">
                <thead>${headFirst}${headSecond}</thead>
                <tbody>${bodyRows}</tbody>
            </table>
        `;
        gridHost.innerHTML = tableHtml;

        // Render mobile cards
        renderMobileCards(students, daysWithData);

        // Update meta info
        const monthLabel = data.meta?.month_label || monthInput.value;
        const classLabel = data.meta?.class_room || fallbackClass;
        const reportCount = data.meta?.report_dates?.length || daysWithData.length || 0;
        const studentCount = data.meta?.student_count || students.length;

        gridMeta.innerHTML = `
            <span class="legend-pill bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                <i class="far fa-calendar-alt"></i> ${monthLabel}
            </span>
            <span class="legend-pill bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                <i class="fas fa-door-open"></i> ห้อง ${classLabel}
            </span>
            <span class="legend-pill bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                <i class="fas fa-user-graduate"></i> ${studentCount} คน
            </span>
            <span class="legend-pill bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                <i class="fas fa-clipboard-check"></i> ${reportCount} วันบันทึก
            </span>
        `;

        renderLegend();
    }

    function renderMobileCards(students, daysWithData) {
        if (!mobileCardsHost) return;

        const cardsHtml = students.map((student, idx) => {
            const no = student.student_no ? `(${student.student_no})` : `#${idx + 1}`;
            const name = student.student_name || 'ไม่พบชื่อ';

            // Build status badges for each day
            const statusBadges = daysWithData.map(day => {
                const status = student.statuses?.[day.date] || '';
                const visual = getStatusVisual(status);
                return `
                    <div class="text-center">
                        <div class="text-[10px] text-gray-500 mb-1">${day.day}</div>
                        <div class="status-badge ${visual.className}" title="${day.weekday_th || ''}: ${visual.label}">${visual.icon}</div>
                    </div>
                `;
            }).join('');

            // Build summary chips
            const summaryChips = (state.summaryColumns || []).map(col => {
                const value = student.totals?.[col.key] ?? 0;
                if (value === 0) return '';
                return `<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700">${col.emoji} ${value}</span>`;
            }).filter(Boolean).join('');

            return `
                <div class="student-card">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <div class="font-bold text-indigo-600 dark:text-indigo-400">${no} ${name}</div>
                            <div class="text-xs text-gray-500">ID: ${student.student_id}</div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-3">
                        ${statusBadges}
                    </div>
                    ${summaryChips ? `<div class="flex flex-wrap gap-1 pt-2 border-t border-gray-100 dark:border-gray-700">${summaryChips}</div>` : ''}
                </div>
            `;
        }).join('');

        mobileCardsHost.innerHTML = cardsHtml;
    }

    function renderLegend() {
        const entries = Object.entries(state.statusMeta || {});
        if (!entries.length) {
            legendHost.innerHTML = '<span class="text-sm text-gray-500">ยังไม่มีสถานะให้แสดง</span>';
            return;
        }

        legendHost.innerHTML = entries.map(([key, meta]) => `
            <div class="legend-pill ${meta.cell_class || ''}">
                <span>${meta.emoji || '•'}</span>
                <span>${meta.label || key}</span>
            </div>
        `).join('');
    }

    function getStatusVisual(status) {
        if (!status) {
            return { icon: '·', label: 'ไม่มีข้อมูล', className: 'status-empty' };
        }
        const meta = state.statusMeta[status];
        if (!meta) {
            return { icon: '•', label: status, className: '' };
        }
        return {
            icon: meta.emoji || '•',
            label: meta.label || status,
            className: meta.cell_class || ''
        };
    }

    function setGridLoading(isLoading, message) {
        if (isLoading) {
            gridHost.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="loader-spin mb-4"></div>
                    <p class="text-gray-500 dark:text-gray-400">${message || 'กำลังโหลด...'}</p>
                </div>
            `;
        }
    }

    function showEmpty(text) {
        const emptyHtml = `
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <div class="text-4xl mb-3">📊</div>
                <p class="font-semibold">${text}</p>
                <p class="text-sm mt-1">ลองเปลี่ยนตัวกรองหรือเลือกเดือนอื่น</p>
            </div>
        `;
        gridHost.innerHTML = emptyHtml;
        if (mobileCardsHost) mobileCardsHost.innerHTML = emptyHtml;
        gridMeta.innerHTML = '<span class="legend-pill bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"><i class="fas fa-info-circle"></i> ยังไม่พบข้อมูล</span>';
        legendHost.innerHTML = '';
    }

    function resetFilters() {
        subjectSelect.value = '';
        classSelect.innerHTML = '<option value="">เลือกวิชาก่อน</option>';
        classSelect.disabled = true;
        customClassGroup.classList.add('hidden');
        customClassInput.value = '';
        monthInput.value = config.currentMonth || new Date().toISOString().slice(0, 7);
        subjectHint.textContent = 'เลือกวิชาเพื่อเริ่มใช้งาน';
        showEmpty('ยังไม่ได้เลือกข้อมูล');
    }

    function toast(message) {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                icon: 'info',
                title: message
            });
        } else {
            alert(message);
        }
    }

    function exportGridToCSV() {
        const table = gridHost.querySelector('table');
        if (!table) {
            toast('ยังไม่มีตารางให้ส่งออก');
            return;
        }

        const rows = Array.from(table.querySelectorAll('thead tr, tbody tr'));
        const csvLines = rows.map(row => {
            const cells = Array.from(row.querySelectorAll('th, td'));
            return cells.map(cell => {
                let text = cell.innerText.replace(/\r?\n/g, ' ').trim();
                text = text.replace(/"/g, '""');
                if (text.indexOf(',') >= 0 || text.indexOf('"') >= 0) {
                    return `"${text}"`;
                }
                return text;
            }).join(',');
        });

        const csvContent = csvLines.join('\r\n');
        const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        const url = URL.createObjectURL(blob);
        a.href = url;
        const monthLabel = monthInput.value || new Date().toISOString().slice(0, 7);
        a.download = `attendance_${monthLabel}.csv`;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => { URL.revokeObjectURL(url); a.remove(); }, 500);
    }

    function printGrid() {
        const table = gridHost.querySelector('table');
        if (!table) {
            toast('ยังไม่มีตารางให้พิมพ์');
            return;
        }

        const printWindow = window.open('', '_blank');
        const styles = `
            <style>
                body { font-family: 'Sarabun', Arial, sans-serif; padding: 20px; }
                table { border-collapse: collapse; width: 100%; font-size: 10px; }
                th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: center; }
                th { background: #f3f4f6; }
                .name-col { text-align: left; }
                h3 { margin-bottom: 10px; }
            </style>
        `;
        const title = `<h3>📚 สมุดเช็คชื่อรายเดือน</h3><p>พิมพ์เมื่อ: ${new Date().toLocaleString('th-TH')}</p>`;
        printWindow.document.open();
        printWindow.document.write(`<!doctype html><html><head><meta charset="utf-8">${styles}</head><body>${title}${table.outerHTML}</body></html>`);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => { printWindow.print(); }, 400);
    }
});
