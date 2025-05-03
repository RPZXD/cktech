<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'นักเรียน') {
    header('Location: ../login.php');
    exit;
}
$user = $_SESSION['user'];
$student_id = $user['Stu_id'] ?? $_SESSION['username'] ?? null;

// Read configuration from JSON file
$config = json_decode(file_get_contents('../config.json'), true);
$global = $config['global'];

require_once('header.php');
?>
<body class="hold-transition sidebar-mini layout-fixed light-mode">
<div class="wrapper">
    <?php require_once('wrapper.php');?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header bg-blue-600 text-white font-semibold text-lg">
                        รายการกิจกรรมที่คุณได้ลงทะเบียนแล้ว
                    </div>
                    <div class="card-body">
                        <!-- เพิ่มปุ่มพิมพ์ transcript -->
                        <div class="mb-4">
                            <button id="print-transcript-btn" class="bg-green-500 text-white px-4 py-2 rounded">
                                พิมพ์ Transcript กิจกรรม
                            </button>
                        </div>
                        <!-- เพิ่ม dropdown สำหรับเลือกเทอมและปี -->
                        <div class="mb-4 flex flex-wrap gap-4 items-center">
                            <label>
                                <span>เทอม:</span>
                                <select id="term-select" class="border rounded px-2 py-1">
                                    <option value="">ทั้งหมด</option>
                                </select>
                            </label>
                            <label>
                                <span>ปี:</span>
                                <select id="pee-select" class="border rounded px-2 py-1">
                                    <option value="">ทั้งหมด</option>
                                </select>
                            </label>
                        </div>
                        <div class="overflow-x-auto">
                            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
                            <table id="event-list-table" class="min-w-full bg-white border border-gray-200 rounded shadow display">
                                <thead>
                                    <tr class="bg-blue-100 text-blue-900">
                                        <th class="px-4 py-2 text-left">ชื่อกิจกรรม</th>
                                        <th class="px-4 py-2 text-left">วันที่จัด</th>
                                        <th class="px-4 py-2 text-left">ชั่วโมง</th>
                                        <th class="px-4 py-2 text-left">ประเภท</th>
                                        <th class="px-4 py-2 text-left">ผู้สร้างกิจกรรม</th>
                                    </tr>
                                </thead>
                                <tbody id="event-list-table-body">
                                    <!-- ข้อมูลกิจกรรมจะถูกเติมโดย JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php require_once('../footer.php');?>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
function fetchTermsAndPees() {
    return fetch('../controllers/StudentEventController.php?action=terms_pees')
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            const contentType = res.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new Error("Invalid JSON response");
            }
            return res.json();
        })
        .catch(error => {
            console.error("Error fetching terms and pees:", error);
            Swal.fire('Error', 'ไม่สามารถโหลดข้อมูลเทอมและปีได้', 'error');
            return { success: false, terms: [], pees: [] };
        });
}

function fetchEvents(term = '', pee = '') {
    let url = `../controllers/StudentEventController.php?term=${encodeURIComponent(term)}&pee=${encodeURIComponent(pee)}`;
    return fetch(url)
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            const contentType = res.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new Error("Invalid JSON response");
            }
            return res.json();
        })
        .catch(error => {
            console.error("Error fetching events:", error);
            Swal.fire('Error', 'ไม่สามารถโหลดข้อมูลกิจกรรมได้', 'error');
            return { success: false, events: [] };
        });
}

function renderEvents(data) {
    const tbody = document.getElementById('event-list-table-body');
    // Destroy DataTable ก่อนล้าง tbody
    if ($.fn.DataTable.isDataTable('#event-list-table')) {
        $('#event-list-table').DataTable().clear().destroy();
    }
    tbody.innerHTML = '';
    if (data.success && Array.isArray(data.events) && data.events.length > 0) {
        data.events.forEach(ev => {
            const thaiDate = new Date(ev.event_date).toLocaleDateString('th-TH', {
                year: 'numeric', month: 'long', day: 'numeric'
            });
            tbody.innerHTML += `
                <tr>
                    <td class="px-4 py-2">${ev.title}</td>
                    <td class="px-4 py-2">${thaiDate}</td>
                    <td class="px-4 py-2">${ev.hours}</td>
                    <td class="px-4 py-2">${ev.category}</td>
                    <td class="px-4 py-2">${ev.teacher_name || '-'}</td>
                </tr>
            `;
        });

        // Add summary row
        const totalHours = data.events.reduce((sum, ev) => sum + parseFloat(ev.hours || 0), 0);
        const categorySummary = Array.from(new Set(data.events.map(ev => ev.category)))
            .map(category => `${category}: ${data.events.filter(ev => ev.category === category)
                .reduce((sum, ev) => sum + parseFloat(ev.hours || 0), 0)} ชั่วโมง`).join(', ');
    } else {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-gray-500 py-4">ยังไม่มีการลงทะเบียนกิจกรรม</td></tr>`;
    }

    // Initialize DataTable after rendering all rows
    $('#event-list-table').DataTable({
        destroy: true,
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            paginate: {
                first: "หน้าแรก",
                last: "หน้าสุดท้าย",
                next: "ถัดไป",
                previous: "ก่อนหน้า"
            },
            zeroRecords: "ไม่พบข้อมูล",
            infoEmpty: "ไม่มีข้อมูลให้แสดง",
            infoFiltered: "(กรองจาก _MAX_ รายการทั้งหมด)"
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // โหลด dropdown เทอม/ปี
    fetchTermsAndPees().then(resp => {
        if (resp.success) {
            const termSelect = document.getElementById('term-select');
            const peeSelect = document.getElementById('pee-select');
            // ลบ option เดิม ยกเว้น "ทั้งหมด"
            termSelect.length = 1;
            peeSelect.length = 1;
            resp.terms.forEach(term => {
                const opt = document.createElement('option');
                opt.value = term;
                opt.textContent = term;
                termSelect.appendChild(opt);
            });
            resp.pees.forEach(pee => {
                const opt = document.createElement('option');
                opt.value = pee;
                opt.textContent = pee;
                peeSelect.appendChild(opt);
            });
        }
    });


    function reloadEvents() {
        const term = document.getElementById('term-select').value;
        const pee = document.getElementById('pee-select').value;
        // console.log(`Reloading events with term: ${term}, pee: ${pee}`);
        // logic: ไม่เลือกอะไรเลย = ข้อมูลทั้งหมด, เลือกปี = ข้อมูลปีนั้น, เลือกปี+เทอม = ข้อมูลปี+เทอมนั้น
        fetchEvents(term, pee).then(renderEvents);
    }

    // โหลดข้อมูลกิจกรรมครั้งแรก (แสดงข้อมูลทั้งหมด)
    reloadEvents();
    

    // เมื่อเลือกเทอมหรือปีใหม่
    document.getElementById('term-select').addEventListener('change', reloadEvents);
    document.getElementById('pee-select').addEventListener('change', reloadEvents);

    function convertThaiDate(thaiDateStr) {
                const thaiMonths = {
                    "มกราคม": "01",
                    "กุมภาพันธ์": "02",
                    "มีนาคม": "03",
                    "เมษายน": "04",
                    "พฤษภาคม": "05",
                    "มิถุนายน": "06",
                    "กรกฎาคม": "07",
                    "สิงหาคม": "08",
                    "กันยายน": "09",
                    "ตุลาคม": "10",
                    "พฤศจิกายน": "11",
                    "ธันวาคม": "12"
                };

                const parts = thaiDateStr.trim().split(" ");
                if (parts.length !== 3 || !thaiMonths[parts[1]]) return null;

                const day = parts[0].padStart(2, "0");
                const month = thaiMonths[parts[1]];
                const year = parts[2];

                return `${day}/${month}/${year}`;
            }

    // ฟังก์ชันสำหรับพิมพ์ transcript
    function printTranscript() {
        const table = document.getElementById('event-list-table');
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const term = document.getElementById('term-select').value;
        const pee = document.getElementById('pee-select').value;
        

        if (rows.length === 0 || rows[0].querySelector('td').colSpan === 5) {
            Swal.fire('ไม่มีข้อมูล', 'ไม่พบข้อมูลกิจกรรมสำหรับพิมพ์', 'info');
            return;
        }

        let tableRowsHtml = '';
        const summary = {};
        let totalHours = 0;
        let user = {
            'Stu_name': '<?php echo $user['Stu_pre'] . $user['Stu_name'] . ' ' . $user['Stu_sur']; ?>',
            'Stu_id': '<?php echo $user['Stu_id']; ?>',
            'Stu_room': '<?php echo $user['Stu_major'].'/' . $user['Stu_room']; ?>',
            'term': term,
            'pee': pee
        };

        rows.forEach(row => {
            const cells = Array.from(row.querySelectorAll('td')).map(td => td.textContent.trim());
            const activityName = cells[0];
            const date = convertThaiDate(cells[1]);
            const hours = parseFloat(cells[2]);
            const type = cells[3];

            

            if (!isNaN(hours)) {
                totalHours += hours;
                summary[type] = (summary[type] || 0) + hours;
            }

            tableRowsHtml += `
                <tr>
                    <td>${activityName}</td>
                    <td>${date}</td>
                    <td>${hours}</td>
                    <td>${type}</td>
                </tr>
            `;
        });

        // เพิ่มแถวสรุปตามประเภท
        let summaryRowsHtml = '';
        for (const type in summary) {
            summaryRowsHtml += `
                <tr>
                    <td colspan="2" style="text-align: right;"><strong>${type}</strong></td>
                    <td><strong>${summary[type]}</strong></td>
                    <td>ชั่วโมง</td>
                </tr>
            `;
        }

        // เพิ่มแถวรวมทั้งหมด
        summaryRowsHtml += `
            <tr>
                <td colspan="2" style="text-align: right;"><strong>รวมทั้งหมด</strong></td>
                <td><strong>${totalHours}</strong></td>
                <td>ชั่วโมง</td>
            </tr>
        `;

        // ท้ายกระดาษ
        const footerHtml = `
                <!-- ท้ายกระดาษ -->
                    <div style="margin-top: 30px; padding: 0 50px;">

                        <!-- กลุ่มซ้าย-ขวา -->
                        <div style="display: flex; justify-content: space-between;">
                            <!-- ฝั่งซ้าย -->
                            <div style="text-align: center; width: 45%;">
                                ..................................................<br>
                                (นายบุญลือ หนุนนาค)<br>
                                หัวหน้างานกิจกรรมนักเรียน
                            </div>

                            <!-- ฝั่งขวา -->
                            <div style="text-align: center; width: 45%;">
                                ..................................................<br>
                                (นางอานุชรา ใจปัญญา)<br>
                                รองผู้อำนวยการกลุ่มบริหารกิจการนักเรียน
                            </div>
                        </div>

                        <!-- เว้นระยะห่างเล็กน้อย -->
                        <div style="margin-top: 40px; text-align: center;">
                            ..................................................<br>
                            (นางสาวรสสุคนธ์ อินชัยเขา)<br>
                            ผู้อำนวยการโรงเรียน
                        </div>

                    </div>
        `;

        const transcriptContent = `
            <html>
            <head>
                <title>Transcript กิจกรรม</title>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Sarabun&display=swap');
                    body {
                        font-family: 'Sarabun', sans-serif;
                        margin: 20px;
                        font-size: 10pt;
                        background-image: url('../dist/img/logo-phicha02.png');
                        background-size: contain;
                        background-attachment: fixed;
                        background-repeat: no-repeat;
                        background-position: center;
                    }
                    h1 {
                        text-align: center;
                        margin-bottom: 30px;
                        font-size: 14pt;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                        font-size: 10pt;
                    }
                    th, td {
                        border: 1px solid #000;
                        padding: 10px;
                        text-align: center;
                        vertical-align: middle;

                    }
                    th {
                        font-weight: bold;
                        background-color:rgb(102, 21, 252);
                        color: #f2f2f2;
                    }
                    @media print {
                        body {
                            margin: 0;
                        }
                        table {
                            page-break-inside: auto;
                        }
                        tr {
                            page-break-inside: avoid;
                            page-break-after: auto;
                        }
                    }
                </style>
            </head>
            <body>
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="../dist/img/logo-phicha.png" alt="Logo" style="width: 50px; height: auto;">
                </div>
                <h1>Transcript กิจกรรม</h1>
                <p style="text-align: left;">ชื่อ-นามสกุล: ${user['Stu_name']}&nbsp;&nbsp;&nbsp;&nbsp;รหัสนักเรียน: ${user['Stu_id']}&nbsp;&nbsp;&nbsp;&nbsp;ระดับมัธยมศึกษาปีที่: ${user['Stu_room']}&nbsp;&nbsp;&nbsp;&nbsp;ปีการศึกษา: ${user['pee']}</p>
                </p>
                <table>
                    <thead>
                        <tr>
                            <th>ชื่อกิจกรรม</th>
                            <th>วันที่จัด</th>
                            <th>ชั่วโมง</th>
                            <th>ประเภท</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableRowsHtml}
                        ${summaryRowsHtml}
                    </tbody>
                </table>
                ${footerHtml}
            </body>
            </html>
        `;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(transcriptContent);
        printWindow.document.close();

        printWindow.onload = function () {
            printWindow.focus();
            printWindow.print();
        };
    }




    // เพิ่ม event listener ให้ปุ่มพิมพ์ transcript
    document.getElementById('print-transcript-btn').addEventListener('click', printTranscript);
});
</script>
<?php require_once('script.php');?>
</body>
</html>
