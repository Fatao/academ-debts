<?php

namespace App\Http\Controllers;

use App\Models\{User, Group, Discipline, Debt};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ImportController extends Controller
{
    public function showForm()
    {
        return view('moderator.import');
    }

    // Import freelancers from CSV
    public function importFreelancers(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'file.required' => 'Выберите файл.',
            'file.mimes'    => 'Файл должен быть в формате CSV.',
        ]);

        $file   = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle, 0, ';');

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < 4) continue;

            // Expected columns: Фамилия;Имя;Отчество;Email;Группа
            [$lastName, $firstName, $middleName, $email, $groupName] = array_pad($row, 5, '');

            $email = trim($email);
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Пропущена строка: некорректный email «{$email}»";
                $skipped++;
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            // Find or create group
            $group = null;
            if ($groupName = trim($groupName)) {
                $group = Group::firstOrCreate(['name' => $groupName]);
            }

            User::create([
                'last_name'   => trim($lastName),
                'first_name'  => trim($firstName),
                'middle_name' => trim($middleName),
                'email'       => $email,
                'password'    => Hash::make('freelancer123'),
                'group_id'    => $group?->id,
            ]);

            $imported++;
        }

        fclose($handle);

        $message = "Импортировано студентов: {$imported}.";
        if ($skipped) $message .= " Пропущено (уже существуют или ошибка): {$skipped}.";

        return back()->with('success', $message)->with('import_errors', $errors);
    }

    // Import debts from CSV
    public function importDebts(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'file.required' => 'Выберите файл.',
            'file.mimes'    => 'Файл должен быть в формате CSV.',
        ]);

        $file   = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle, 0, ';');

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < 3) continue;

            // Expected: Email студента;Код дисциплины;Название дисциплины;Email преподавателя
            [$freelancerEmail, $discCode, $discName, $jobgiverEmail] = array_pad($row, 4, '');

            $freelancer = User::where('email', trim($freelancerEmail))->first();
            if (!$freelancer) {
                $errors[] = "Студент не найден: {$freelancerEmail}";
                $skipped++;
                continue;
            }

            $discipline = Discipline::firstOrCreate(
                ['code' => trim($discCode)],
                ['name' => trim($discName) ?: trim($discCode)]
            );

            $jobgiver = User::where('email', trim($jobgiverEmail))->first();
            if (!$jobgiver) {
                $errors[] = "Преподаватель не найден: {$jobgiverEmail}";
                $skipped++;
                continue;
            }

            // Skip if debt already exists
            $exists = Debt::where('freelancer_id', $freelancer->id)
                ->where('discipline_id', $discipline->id)
                ->where('status', 'DEBT')
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Debt::create([
                'freelancer_id'     => $freelancer->id,
                'discipline_id'  => $discipline->id,
                'assigned_by_id' => $jobgiver->id,
                'status'         => 'DEBT',
            ]);

            $imported++;
        }

        fclose($handle);

        $message = "Импортировано задолженностей: {$imported}.";
        if ($skipped) $message .= " Пропущено: {$skipped}.";

        return back()->with('success', $message)->with('import_errors', $errors);
    }

    public function downloadTemplate(string $type)
    {
        $templates = [
            'freelancers' => [
                'filename' => 'шаблон_студенты.csv',
                'header'   => 'Фамилия;Имя;Отчество;Email;Группа',
                'example'  => 'Иванов;Иван;Иванович;[ivanov@uni.ru](mailto:ivanov@uni.ru);ИВТ-41',
            ],
            'debts' => [
                'filename' => 'шаблон_задолженности.csv',
                'header'   => 'Email студента;Код дисциплины;Название дисциплины;Email преподавателя',
                'example'  => 'ivanov@uni.ru;МАТ101;Высшая математика;jobgiver@uni.ru',
            ],
        ];

        if (!isset($templates[$type])) abort(404);

        $t       = $templates[$type];
        $content = chr(0xEF) . chr(0xBB) . chr(0xBF); // UTF-8 BOM
        $content .= $t['header'] . "\n" . $t['example'] . "\n";

        return response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $t['filename'] . '"',
        ]);
    }
}
