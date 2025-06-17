<?php
namespace App\Models;

use App\DatabaseTeachingReport;
use App\DatabaseUsers;
use PDO;
use PDOException;
use Exception;

// Include TermPee class
require_once __DIR__ . '/TermPee.php';

class Certificate
{
    private $db;
    private $userDb;
    private $table = 'certificates';    public function __construct()
    {
        try {
            $this->db = new DatabaseTeachingReport();
            error_log('[CERT_INIT] Main database connected successfully');
        } catch (Exception $e) {
            error_log('[CERT_INIT] Main database connection failed: ' . $e->getMessage());
            throw $e;
        }
        
        try {
            $this->userDb = new DatabaseUsers();
            error_log('[CERT_INIT] Users database connected successfully');
        } catch (Exception $e) {
            error_log('[CERT_INIT] Users database connection failed: ' . $e->getMessage());
            // Set userDb to null so we can handle it gracefully
            $this->userDb = null;
        }
        
        // Ensure PDO throws exceptions (defensive, in case not set in DatabaseTeachingReport)
        try {
            $pdo = $this->db->getPDO();
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            error_log('PDO Exception mode set failed: ' . $e->getMessage());
        }
    }

    public function testConnection()
    {
        try {
            // Test database connection
            $stmt = $this->db->query("SELECT 1 as test");
            $result = $stmt->fetch();
            
            if (!$result || $result['test'] !== 1) {
                throw new Exception('Database connection test failed');
            }
            
            // Test if certificates table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'certificates'");
            if ($stmt->rowCount() === 0) {
                throw new Exception('Certificates table does not exist');
            }
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Connection test failed: ' . $e->getMessage());
        }
    }

    public function create($data)
    {
        try {
            // Check if new columns exist
            $columnsExist = $this->checkNewColumnsExist();
            
            if ($columnsExist) {
                $sql = "INSERT INTO {$this->table} 
                        (student_name, student_class, student_room, award_type, award_detail, 
                         award_date, note, certificate_image, teacher_id, term, year, 
                         award_name, award_level, award_organization, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $params = [
                    $data['student_name'],
                    $data['student_class'],
                    $data['student_room'],
                    $data['award_type'],
                    $data['award_detail'],
                    $data['award_date'],
                    $data['note'] ?? null,
                    $data['certificate_image'] ?? null,
                    $data['teacher_id'],
                    $data['term'] ?? null,
                    $data['year'] ?? null,
                    $data['award_name'] ?? null,
                    $data['award_level'] ?? null,
                    $data['award_organization'] ?? null
                ];
            } else {
                // Fallback insert without new columns
                $sql = "INSERT INTO {$this->table} 
                        (student_name, student_class, student_room, award_type, award_detail, 
                         award_date, note, certificate_image, teacher_id, term, year, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $params = [
                    $data['student_name'],
                    $data['student_class'],
                    $data['student_room'],
                    $data['award_type'],
                    $data['award_detail'],
                    $data['award_date'],
                    $data['note'] ?? null,
                    $data['certificate_image'] ?? null,
                    $data['teacher_id'],
                    $data['term'] ?? null,
                    $data['year'] ?? null
                ];
            }
            
            $stmt = $this->db->query($sql, $params);
            return $this->db->getPDO()->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Failed to create certificate: ' . $e->getMessage());
        }
    }

    public function createMultiple($students, $commonData)
    {
        try {
            $this->db->getPDO()->beginTransaction();
            $insertedIds = [];

            // Check if new columns exist
            $columnsExist = $this->checkNewColumnsExist();

            foreach ($students as $student) {
                $data = array_merge($commonData, [
                    'student_name' => $student['name'],
                    'student_class' => $student['class'],
                    'student_room' => $student['room'],
                    'certificate_image' => $student['certificate_image'] ?? null
                ]);
                
                if ($columnsExist) {
                    $sql = "INSERT INTO {$this->table} 
                            (student_name, student_class, student_room, award_type, award_detail, 
                             award_date, note, certificate_image, teacher_id, term, year,
                             award_name, award_level, award_organization, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    
                    $params = [
                        $data['student_name'],
                        $data['student_class'],
                        $data['student_room'],
                        $data['award_type'],
                        $data['award_detail'],
                        $data['award_date'],
                        $data['note'] ?? null,
                        $data['certificate_image'] ?? null,
                        $data['teacher_id'],
                        $data['term'] ?? null,
                        $data['year'] ?? null,
                        $data['award_name'] ?? null,
                        $data['award_level'] ?? null,
                        $data['award_organization'] ?? null
                    ];
                } else {
                    // Fallback insert without new columns
                    $sql = "INSERT INTO {$this->table} 
                            (student_name, student_class, student_room, award_type, award_detail, 
                             award_date, note, certificate_image, teacher_id, term, year, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    
                    $params = [
                        $data['student_name'],
                        $data['student_class'],
                        $data['student_room'],
                        $data['award_type'],
                        $data['award_detail'],
                        $data['award_date'],
                        $data['note'] ?? null,
                        $data['certificate_image'] ?? null,
                        $data['teacher_id'],
                        $data['term'] ?? null,
                        $data['year'] ?? null
                    ];
                }
                
                $stmt = $this->db->query($sql, $params);
                $insertedIds[] = $this->db->getPDO()->lastInsertId();
            }

            $this->db->getPDO()->commit();
            return $insertedIds;
        } catch (Exception $e) {
            if ($this->db->getPDO()->inTransaction()) {
                $this->db->getPDO()->rollback();
            }
            throw new Exception('Failed to create multiple certificates: ' . $e->getMessage());
        }
    }    public function getAll($teacherId = null)
    {
        try {
            // Log for debugging on shared hosting
            error_log('[CERT_MODEL] getAll() called with teacherId: ' . var_export($teacherId, true));
            
            // Ensure teacherId is properly typed
            if ($teacherId !== null) {
                $teacherId = intval($teacherId);
                if ($teacherId <= 0) {
                    throw new Exception('Invalid teacher ID: ' . $teacherId);
                }
            }

            $columnsExist = $this->checkNewColumnsExist();
            error_log('[CERT_MODEL] New columns exist: ' . ($columnsExist ? 'YES' : 'NO'));

            if ($columnsExist) {
                $sql = "SELECT c.* FROM {$this->table} c";
            } else {
                $sql = "SELECT c.id, c.student_name, c.student_class, c.student_room, 
                               c.award_type, c.award_detail, c.award_date, c.note, 
                               c.certificate_image, c.teacher_id, c.term, c.year, 
                               c.created_at, c.updated_at,
                               NULL as award_name, NULL as award_level, NULL as award_organization
                        FROM {$this->table} c";
            }

            $params = [];
            if ($teacherId) {
                $sql .= " WHERE c.teacher_id = ?";
                $params[] = $teacherId;
            }

            $sql .= " ORDER BY c.created_at DESC";

            error_log('[CERT_MODEL] SQL: ' . $sql);
            error_log('[CERT_MODEL] Params: ' . json_encode($params));

            // Use prepared statement with explicit error handling
            $pdo = $this->db->getPDO();
            $stmt = $pdo->prepare($sql);
            
            if (!$stmt) {
                $errorInfo = $pdo->errorInfo();
                throw new Exception('SQL Prepare failed: ' . $errorInfo[2]);
            }
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception('SQL Execute failed: ' . $errorInfo[2]);
            }
            
            $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log('[CERT_MODEL] Fetched rows: ' . count($certificates));            foreach ($certificates as &$cert) {
                if ($cert['teacher_id']) {
                    try {
                        error_log('[CERT_MODEL] Looking up teacher ID: ' . $cert['teacher_id']);
                        
                        // Check if users database is available
                        if ($this->userDb === null) {
                            $cert['teacher_name'] = 'ครู ID: ' . $cert['teacher_id'];
                            error_log('[CERT_MODEL] Users DB not available, using fallback');
                            continue;
                        }
                        
                        $teacher = $this->userDb->getTeacherById($cert['teacher_id']);
                        if ($teacher && isset($teacher['Teach_name'])) {
                            $cert['teacher_name'] = $teacher['Teach_name'];
                            error_log('[CERT_MODEL] Teacher found: ' . $teacher['Teach_name']);
                        } else {
                            $cert['teacher_name'] = 'ไม่พบข้อมูลครู';
                            error_log('[CERT_MODEL] No teacher found for ID: ' . $cert['teacher_id']);
                        }
                    } catch (\Exception $e) {
                        $cert['teacher_name'] = 'ไม่สามารถโหลดข้อมูลครูได้';
                        error_log('[CERT_MODEL] Teacher lookup error: ' . $e->getMessage());
                        // Don't let teacher lookup failure stop the whole process
                    }
                } else {
                    $cert['teacher_name'] = '-';
                }
            }

            error_log('[CERT_MODEL] Successfully returning ' . count($certificates) . ' certificates');
            return $certificates;
        } catch (\Exception $e) {
            error_log('[CERT_MODEL] ERROR in getAll(): ' . $e->getMessage());
            error_log('[CERT_MODEL] Error file: ' . $e->getFile() . ' line: ' . $e->getLine());
            throw new Exception('Failed to fetch certificates: ' . $e->getMessage());
        }
    }

    public function getById($id)
    {
        try {
            // Check if new columns exist first
            $columnsExist = $this->checkNewColumnsExist();
            
            if ($columnsExist) {
                $sql = "SELECT c.* FROM {$this->table} c WHERE c.id = ?";
            } else {
                // Fallback query without new columns
                $sql = "SELECT c.id, c.student_name, c.student_class, c.student_room, 
                               c.award_type, c.award_detail, c.award_date, c.note, 
                               c.certificate_image, c.teacher_id, c.term, c.year, 
                               c.created_at, c.updated_at,
                               NULL as award_name, NULL as award_level, NULL as award_organization
                        FROM {$this->table} c WHERE c.id = ?";
            }
            
            $stmt = $this->db->query($sql, [$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                throw new Exception('Certificate not found');
            }
            
            // Add teacher name from the users database
            if ($result['teacher_id']) {
                try {
                    $teacher = $this->userDb->getTeacherById($result['teacher_id']);
                    $result['teacher_name'] = $teacher ? $teacher['Teach_name'] : 'ไม่พบข้อมูลครู';
                } catch (Exception $e) {
                    $result['teacher_name'] = 'ไม่สามารถโหลดข้อมูลครูได้';
                }
            } else {
                $result['teacher_name'] = '-';
            }
            
            return $result;
        } catch (Exception $e) {
            throw new Exception('Failed to fetch certificate: ' . $e->getMessage());
        }
    }

    public function update($id, $data)
    {
        try {
            // Check if new columns exist
            $columnsExist = $this->checkNewColumnsExist();
            
            if ($columnsExist) {
                $sql = "UPDATE {$this->table} 
                        SET student_name = ?, student_class = ?, student_room = ?, 
                            award_type = ?, award_detail = ?, award_date = ?, note = ?, 
                            certificate_image = COALESCE(?, certificate_image), 
                            term = ?, year = ?, award_name = ?, award_level = ?, 
                            award_organization = ?, updated_at = NOW()
                        WHERE id = ?";
                
                $params = [
                    $data['student_name'],
                    $data['student_class'],
                    $data['student_room'],
                    $data['award_type'],
                    $data['award_detail'],
                    $data['award_date'],
                    $data['note'] ?? null,
                    $data['certificate_image'] ?? null,
                    $data['term'] ?? null,
                    $data['year'] ?? null,
                    $data['award_name'] ?? null,
                    $data['award_level'] ?? null,
                    $data['award_organization'] ?? null,
                    $id
                ];
            } else {
                // Fallback update without new columns
                $sql = "UPDATE {$this->table} 
                        SET student_name = ?, student_class = ?, student_room = ?, 
                            award_type = ?, award_detail = ?, award_date = ?, note = ?, 
                            certificate_image = COALESCE(?, certificate_image), 
                            term = ?, year = ?, updated_at = NOW()
                        WHERE id = ?";
                
                $params = [
                    $data['student_name'],
                    $data['student_class'],
                    $data['student_room'],
                    $data['award_type'],
                    $data['award_detail'],
                    $data['award_date'],
                    $data['note'] ?? null,
                    $data['certificate_image'] ?? null,
                    $data['term'] ?? null,
                    $data['year'] ?? null,
                    $id
                ];
            }

            $stmt = $this->db->query($sql, $params);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception('Failed to update certificate: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            // Get certificate image before deletion
            $certificate = $this->getById($id);
            
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->query($sql, [$id]);
            
            // Delete image file if exists
            if ($certificate && isset($certificate['certificate_image']) && $certificate['certificate_image']) {
                $imagePath = "../uploads/certificates/" . $certificate['certificate_image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception('Failed to delete certificate: ' . $e->getMessage());
        }
    }

    public function uploadImage($file)
    {
        try {
            if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('ไม่สามารถอัพโหลดไฟล์ได้');
            }

            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('รองรับเฉพาะไฟล์รูปภาพ (JPG, PNG, GIF)');
            }

            if ($file['size'] > $maxSize) {
                throw new Exception('ขนาดไฟล์ต้องไม่เกิน 5MB');
            }

            // Create upload directory if not exists
            $uploadDir = "../uploads/certificates/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'cert_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
            $uploadPath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('ไม่สามารถบันทึกไฟล์ได้');
            }

            return $fileName;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getStatisticsByDateRange($teacherId = null, $startDate = null, $endDate = null, $term = null, $year = null)
    {
        try {
            // Get basic statistics
            $sql = "SELECT 
                        COUNT(*) as total_certificates,
                        COUNT(DISTINCT student_name) as total_students,
                        term,
                        year
                    FROM {$this->table} WHERE 1=1";
            
            $params = [];
            if ($teacherId) {
                $sql .= " AND teacher_id = ?";
                $params[] = $teacherId;
            }
            
            if ($startDate && $endDate) {
                $sql .= " AND award_date BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            if ($term) {
                $sql .= " AND term = ?";
                $params[] = $term;
            }
            
            if ($year) {
                $sql .= " AND year = ?";
                $params[] = $year;
            }
            
            $stmt = $this->db->query($sql, $params);
            $basicStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get award type statistics
            $sql = "SELECT 
                        award_type,
                        COUNT(*) as count_by_type
                    FROM {$this->table} WHERE 1=1";
            
            $params = [];
            if ($teacherId) {
                $sql .= " AND teacher_id = ?";
                $params[] = $teacherId;
            }
            
            if ($startDate && $endDate) {
                $sql .= " AND award_date BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            if ($term) {
                $sql .= " AND term = ?";
                $params[] = $term;
            }
            
            if ($year) {
                $sql .= " AND year = ?";
                $params[] = $year;
            }
            
            $sql .= " GROUP BY award_type ORDER BY count_by_type DESC";
            
            $stmt = $this->db->query($sql, $params);
            $awardStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get this month's statistics
            $sql = "SELECT COUNT(*) as this_month
                    FROM {$this->table}
                    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                    AND YEAR(created_at) = YEAR(CURRENT_DATE())";
            
            $params = [];
            if ($teacherId) {
                $sql .= " AND teacher_id = ?";
                $params[] = $teacherId;
            }
            
            if ($term) {
                $sql .= " AND term = ?";
                $params[] = $term;
            }
            
            if ($year) {
                $sql .= " AND year = ?";
                $params[] = $year;
            }
            
            $stmt = $this->db->query($sql, $params);
            $monthStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_certificates' => $basicStats['total_certificates'] ?? 0,
                'total_students' => $basicStats['total_students'] ?? 0,
                'this_month' => $monthStats['this_month'] ?? 0,
                'top_award' => !empty($awardStats) ? $awardStats[0]['award_type'] : '-',
                'award_breakdown' => $awardStats,
                'current_term' => $basicStats['term'] ?? null,
                'current_year' => $basicStats['year'] ?? null
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to fetch statistics: ' . $e->getMessage());
        }
    }

    public function getStatistics($teacherId = null)
    {
        return $this->getStatisticsByDateRange($teacherId);
    }

    public function getTopStudents($teacherId = null, $limit = 10)
    {
        try {
            $sql = "SELECT 
                        student_name,
                        student_class,
                        student_room,
                        COUNT(*) as total_awards,
                        GROUP_CONCAT(DISTINCT award_type ORDER BY award_type SEPARATOR ', ') as award_types
                    FROM {$this->table} WHERE 1=1";
            
            $params = [];
            if ($teacherId) {
                $sql .= " AND teacher_id = ?";
                $params[] = $teacherId;
            }
            
            $sql .= " GROUP BY student_name, student_class, student_room 
                      ORDER BY total_awards DESC, student_name ASC 
                      LIMIT " . intval($limit);
            
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch top students: ' . $e->getMessage());
        }
    }

    public function getRecentCertificates($teacherId = null, $limit = 5)
    {
        try {
            $sql = "SELECT 
                        student_name,
                        student_class,
                        student_room,
                        award_type,
                        award_detail,
                        award_date,
                        created_at
                    FROM {$this->table} WHERE 1=1";
            
            $params = [];
            if ($teacherId) {
                $sql .= " AND teacher_id = ?";
                $params[] = $teacherId;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT " . intval($limit);
            
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch recent certificates: ' . $e->getMessage());
        }
    }

    public function searchCertificates($searchTerm, $teacherId = null, $term = null, $year = null, $class = null, $awardType = null)
    {
        try {
            $sql = "SELECT c.* FROM {$this->table} c WHERE 1=1";
            $params = [];
            
            // Search term condition - เพิ่มการค้นหาในฟิลด์ใหม่
            if (!empty($searchTerm)) {
                $sql .= " AND (c.student_name LIKE ? OR c.award_type LIKE ? OR c.award_detail LIKE ? 
                          OR c.award_name LIKE ? OR c.award_level LIKE ? OR c.award_organization LIKE ?)";
                $params[] = "%$searchTerm%";
                $params[] = "%$searchTerm%";
                $params[] = "%$searchTerm%";
                $params[] = "%$searchTerm%";
                $params[] = "%$searchTerm%";
                $params[] = "%$searchTerm%";
            }
            
            // Teacher filter
            if ($teacherId) {
                $sql .= " AND c.teacher_id = ?";
                $params[] = $teacherId;
            }
            
            // Term filter
            if ($term) {
                $sql .= " AND c.term = ?";
                $params[] = $term;
            }
            
            // Year filter
            if ($year) {
                $sql .= " AND c.year = ?";
                $params[] = $year;
            }
            
            // Class filter
            if ($class) {
                $sql .= " AND c.student_class = ?";
                $params[] = $class;
            }
            
            // Award type filter
            if ($awardType) {
                $sql .= " AND c.award_type = ?";
                $params[] = $awardType;
            }
            
            $sql .= " ORDER BY c.created_at DESC";
            
            $stmt = $this->db->query($sql, $params);
            $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add teacher names
            foreach ($certificates as &$cert) {
                if ($cert['teacher_id']) {
                    try {
                        $teacher = $this->userDb->getTeacherById($cert['teacher_id']);
                        $cert['teacher_name'] = $teacher ? $teacher['Teach_name'] : 'ไม่พบข้อมูลครู';
                    } catch (Exception $e) {
                        $cert['teacher_name'] = 'ไม่สามารถโหลดข้อมูลครูได้';
                    }
                } else {
                    $cert['teacher_name'] = '-';
                }
            }
            
            return $certificates;
        } catch (Exception $e) {
            throw new Exception('Failed to search certificates: ' . $e->getMessage());
        }
    }

    public function getAvailableTermsAndYears($teacherId = null)
    {
        try {
            $sql = "SELECT DISTINCT term, year 
                    FROM {$this->table} 
                    WHERE term IS NOT NULL AND year IS NOT NULL";
            
            $params = [];
            if ($teacherId) {
                $sql .= " AND teacher_id = ?";
                $params[] = $teacherId;
            }
            
            $sql .= " ORDER BY year DESC, term DESC";
            
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch terms and years: ' . $e->getMessage());
        }
    }

    public function getCurrentTermInfo()
    {
        try {
            $termPee = \TermPee::getCurrent();
            return [
                'term' => $termPee->term,
                'year' => $termPee->pee
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to get current term info: ' . $e->getMessage());
        }
    }

    private function checkNewColumnsExist()
    {
        try {
            $sql = "SHOW COLUMNS FROM {$this->table} LIKE 'award_name'";
            $stmt = $this->db->query($sql);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }    public function getDepartmentCertificates($departmentId)
    {
        try {
            error_log('[CERT_MODEL] getDepartmentCertificates called with departmentId: ' . $departmentId);
            
            $columnsExist = $this->checkNewColumnsExist();
            
            // If departmentId is numeric, get department name first
            if (is_numeric($departmentId)) {
                try {
                    $deptSql = "SELECT Dep_name FROM department WHERE Dep_id = ?";
                    $deptStmt = $this->userDb->query($deptSql, [$departmentId]);
                    $dept = $deptStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($dept) {
                        $departmentName = $dept['Dep_name'];
                    } else {
                        throw new Exception('Department not found');
                    }
                } catch (Exception $e) {
                    error_log('[CERT_MODEL] Department lookup failed: ' . $e->getMessage());
                    throw new Exception('Failed to find department: ' . $e->getMessage());
                }
            } else {
                $departmentName = $departmentId;
            }

            if ($columnsExist) {
                $sql = "SELECT c.*, t.Teach_name as teacher_name 
                        FROM {$this->table} c 
                        LEFT JOIN phichaia_student.teacher t ON c.teacher_id = t.Teach_id 
                        WHERE t.Teach_major = ? AND t.Teach_status = '1'";
            } else {
                $sql = "SELECT c.id, c.student_name, c.student_class, c.student_room, 
                               c.award_type, c.award_detail, c.award_date, c.note, 
                               c.certificate_image, c.teacher_id, c.term, c.year, 
                               c.created_at, c.updated_at,
                               NULL as award_name, NULL as award_level, NULL as award_organization,
                               t.Teach_name as teacher_name
                        FROM {$this->table} c 
                        LEFT JOIN phichaia_student.teacher t ON c.teacher_id = t.Teach_id 
                        WHERE t.Teach_major = ? AND t.Teach_status = '1'";
            }

            $sql .= " ORDER BY c.created_at DESC";

            error_log('[CERT_MODEL] Department SQL: ' . $sql);
            error_log('[CERT_MODEL] Department Name: ' . $departmentName);
            
            $stmt = $this->db->query($sql, [$departmentName]);
            $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log('[CERT_MODEL] Department certificates found: ' . count($certificates));
            
            return $certificates;
        } catch (\Exception $e) {
            error_log('[CERT_MODEL] ERROR in getDepartmentCertificates(): ' . $e->getMessage());
            throw new Exception('Failed to fetch department certificates: ' . $e->getMessage());
        }
    }    public function getDepartmentStatistics($departmentId)
    {
        try {
            // If departmentId is numeric, get department name first
            if (is_numeric($departmentId)) {
                try {
                    $deptSql = "SELECT Dep_name FROM department WHERE Dep_id = ?";
                    $deptStmt = $this->userDb->query($deptSql, [$departmentId]);
                    $dept = $deptStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($dept) {
                        $departmentName = $dept['Dep_name'];
                    } else {
                        throw new Exception('Department not found');
                    }
                } catch (Exception $e) {
                    error_log('[CERT_MODEL] Department lookup failed: ' . $e->getMessage());
                    throw new Exception('Failed to find department: ' . $e->getMessage());
                }
            } else {
                $departmentName = $departmentId;
            }
            
            // Get basic statistics
            $sql = "SELECT 
                        COUNT(c.id) as total_certificates,
                        COUNT(DISTINCT c.teacher_id) as total_teachers,
                        COUNT(DISTINCT c.student_name) as total_students
                    FROM {$this->table} c 
                    LEFT JOIN phichaia_student.teacher t ON c.teacher_id = t.Teach_id 
                    WHERE t.Teach_major = ? AND t.Teach_status = '1'";
            
            $stmt = $this->db->query($sql, [$departmentName]);
            $basicStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get top teacher
            $sql = "SELECT t.Teach_name, COUNT(c.id) as cert_count
                    FROM {$this->table} c 
                    LEFT JOIN phichaia_student.teacher t ON c.teacher_id = t.Teach_id 
                    WHERE t.Teach_major = ? AND t.Teach_status = '1'
                    GROUP BY c.teacher_id, t.Teach_name 
                    ORDER BY cert_count DESC 
                    LIMIT 1";
            
            $stmt = $this->db->query($sql, [$departmentName]);
            $topTeacher = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get this month's statistics
            $sql = "SELECT COUNT(c.id) as this_month
                    FROM {$this->table} c 
                    LEFT JOIN phichaia_student.teacher t ON c.teacher_id = t.Teach_id 
                    WHERE t.Teach_major = ? AND t.Teach_status = '1'
                    AND MONTH(c.created_at) = MONTH(CURRENT_DATE()) 
                    AND YEAR(c.created_at) = YEAR(CURRENT_DATE())";
            
            $stmt = $this->db->query($sql, [$departmentName]);
            $monthStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_certificates' => $basicStats['total_certificates'] ?? 0,
                'total_teachers' => $basicStats['total_teachers'] ?? 0,
                'total_students' => $basicStats['total_students'] ?? 0,
                'top_teacher' => $topTeacher ? $topTeacher['Teach_name'] . ' (' . $topTeacher['cert_count'] . ')' : '-',
                'this_month' => $monthStats['this_month'] ?? 0
            ];
        } catch (Exception $e) {
            error_log('[CERT_MODEL] ERROR in getDepartmentStatistics(): ' . $e->getMessage());
            throw new Exception('Failed to fetch department statistics: ' . $e->getMessage());
        }
    }

    public function getDepartmentTermsAndYears($departmentId)
    {
        try {
            // Get distinct terms
            $sql = "SELECT DISTINCT c.term 
                    FROM {$this->table} c 
                    LEFT JOIN phichaia_student.teacher t ON c.teacher_id = t.Teach_id 
                    WHERE t.Dep_id = ? AND c.term IS NOT NULL 
                    ORDER BY c.term";
            
            $stmt = $this->db->query($sql, [$departmentId]);
            $terms = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Get distinct years
            $sql = "SELECT DISTINCT c.year 
                    FROM {$this->table} c 
                    LEFT JOIN phichaia_student.teacher t ON c.teacher_id = t.Teach_id 
                    WHERE t.Dep_id = ? AND c.year IS NOT NULL 
                    ORDER BY c.year DESC";
            
            $stmt = $this->db->query($sql, [$departmentId]);
            $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return [
                'terms' => $terms,
                'years' => $years
            ];
        } catch (Exception $e) {
            error_log('[CERT_MODEL] ERROR in getDepartmentTermsAndYears(): ' . $e->getMessage());
            throw new Exception('Failed to fetch department terms and years: ' . $e->getMessage());
        }
    }
}
