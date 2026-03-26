<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class ContractsController extends Controller
{
    public function show(array $params = []): void
    {
        clear_old();
        $teacherId = (int) ($params['teacherId'] ?? 0);
        $db = Database::connection();

        $teacherStmt = $db->prepare('SELECT * FROM teachers WHERE id = :id LIMIT 1');
        $teacherStmt->execute(['id' => $teacherId]);
        $teacher = $teacherStmt->fetch();

        if (!$teacher) {
            flash('error', 'استاد پیدا نشد.');
            $this->redirect('/teachers');
        }

        $contract = $this->contract($teacherId);

        $this->render('contracts/show', [
            'title' => 'قرارداد استاد',
            'teacher' => $teacher,
            'contract' => $contract,
        ]);
    }

    public function save(array $params = []): void
    {
        $this->csrfCheck();
        $teacherId = (int) ($params['teacherId'] ?? 0);
        $db = Database::connection();

        $contract = $this->contract($teacherId);
        if (!$contract) {
            flash('error', 'قرارداد پیدا نشد.');
            $this->redirect('/teachers');
        }

        $signed = upload_file('signed_file', 'teacher_contracts/signed', ['pdf', 'jpg', 'jpeg', 'png']) ?: $contract['signed_file'];

        $stmt = $db->prepare('UPDATE teacher_contracts
            SET contract_date = :contract_date,
                monthly_salary = :monthly_salary,
                position = :position,
                notes = :notes,
                signed_file = :signed_file,
                updated_at = NOW()
            WHERE teacher_id = :teacher_id');

        $stmt->execute([
            'contract_date' => (($_POST['contract_date'] ?? '') !== '') ? $_POST['contract_date'] : null,
            'monthly_salary' => trim((string) ($_POST['monthly_salary'] ?? '')),
            'position' => trim((string) ($_POST['position'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'signed_file' => $signed,
            'teacher_id' => $teacherId,
        ]);

        flash('success', 'قرارداد ذخیره شد.');
        $this->redirect('/contracts/' . $teacherId);
    }

    private function contract(int $teacherId): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM teacher_contracts WHERE teacher_id = :teacher_id LIMIT 1');
        $stmt->execute(['teacher_id' => $teacherId]);
        $contract = $stmt->fetch();

        if ($contract) {
            return $contract;
        }

        $number = str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT);
        $create = $db->prepare('INSERT INTO teacher_contracts (teacher_id, contract_number, created_at, updated_at)
            VALUES (:teacher_id, :contract_number, NOW(), NOW())');
        $create->execute([
            'teacher_id' => $teacherId,
            'contract_number' => $number,
        ]);

        $reload = $db->prepare('SELECT * FROM teacher_contracts WHERE teacher_id = :teacher_id LIMIT 1');
        $reload->execute(['teacher_id' => $teacherId]);
        $record = $reload->fetch();

        return $record ?: null;
    }
}
