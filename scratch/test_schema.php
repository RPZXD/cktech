<?php
require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
$db = new App\DatabaseTeachingReport();
try {
    // Add columns
    $db->getPDO()->exec("ALTER TABLE teaching_reports ADD COLUMN IF NOT EXISTS term VARCHAR(10) DEFAULT NULL");
    $db->getPDO()->exec("ALTER TABLE teaching_reports ADD COLUMN IF NOT EXISTS pee VARCHAR(10) DEFAULT NULL");
    echo "Columns added (or verified exist).\n";
    
    // Backfill
    $rowsAffected = $db->getPDO()->exec("UPDATE teaching_reports 
        SET term = CASE 
            WHEN MONTH(report_date) BETWEEN 5 AND 10 THEN '1'
            ELSE '2'
        END,
        pee = CASE 
            WHEN MONTH(report_date) BETWEEN 5 AND 12 THEN YEAR(report_date) + 543
            ELSE YEAR(report_date) + 543 - 1
        END
        WHERE term IS NULL OR pee IS NULL");
    echo "Backfilled " . $rowsAffected . " rows.\n";

    // Show sample rows
    $samples = $db->getPDO()->query("SELECT report_date, term, pee FROM teaching_reports LIMIT 10")->fetchAll();
    print_r($samples);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
