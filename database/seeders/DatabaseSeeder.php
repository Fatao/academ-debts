<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Group, Discipline, Debt, Retake, Notification};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Группы по курсам
        $g = [];

        // 1 курс
        $g['ИБ51б']  = Group::create(['name' => 'ИБ51б',  'year' => 1]);
        $g['ИВТ51б'] = Group::create(['name' => 'ИВТ51б', 'year' => 1]);
        $g['ПИ51б']  = Group::create(['name' => 'ПИ51б',  'year' => 1]);

        // 2 курс
        $g['ИБ41б']  = Group::create(['name' => 'ИБ41б',  'year' => 2]);
        $g['ИВТ41б'] = Group::create(['name' => 'ИВТ41б', 'year' => 2]);
        $g['ПИ41б']  = Group::create(['name' => 'ПИ41б',  'year' => 2]);

        // 3 курс
        $g['ИБ31б']  = Group::create(['name' => 'ИБ31б',  'year' => 3]);
        $g['ИВТ31б'] = Group::create(['name' => 'ИВТ31б', 'year' => 3]);
        $g['ПИ31б']  = Group::create(['name' => 'ПИ31б',  'year' => 3]);

        // 4 курс
        $g['ИБ21б']  = Group::create(['name' => 'ИБ21б',  'year' => 4]);
        $g['ИВТ21б'] = Group::create(['name' => 'ИВТ21б', 'year' => 4]);
        $g['ПИ21б']  = Group::create(['name' => 'ПИ21б',  'year' => 4]);

        // Администратор
        User::create([
            'email'       => 'admin@edu.ugrasu.ru',
            'password'    => Hash::make('password'),
            'last_name'   => 'Администратор',
            'first_name'  => 'Системный',
            'middle_name' => null,
            'is_admin'    => true,
            'is_dean'     => true,
        ]);

        // Деканат
        $dean = User::create([
            'email'       => 'dean@edu.ugrasu.ru',
            'password'    => Hash::make('password'),
            'last_name'   => 'Деканов',
            'first_name'  => 'Пётр',
            'middle_name' => 'Иванович',
            'is_dean'     => true,
        ]);

        // Преподаватели
        $teacher1 = User::create([
            'email'       => 'ivanov@edu.ugrasu.ru',
            'password'    => Hash::make('password'),
            'last_name'   => 'Иванов',
            'first_name'  => 'Иван',
            'middle_name' => 'Иванович',
            'is_teacher'  => true,
        ]);

        $teacher2 = User::create([
            'email'       => 'petrova@edu.ugrasu.ru',
            'password'    => Hash::make('password'),
            'last_name'   => 'Петрова',
            'first_name'  => 'Мария',
            'middle_name' => 'Сергеевна',
            'is_teacher'  => true,
        ]);

        $teacher3 = User::create([
            'email'       => 'sidorov@edu.ugrasu.ru',
            'password'    => Hash::make('password'),
            'last_name'   => 'Сидоров',
            'first_name'  => 'Алексей',
            'middle_name' => 'Петрович',
            'is_teacher'  => true,
        ]);

        // Студенты
        $student1 = User::create([
            'email'       => 'smirnov@edu.ugrasu.ru',
            'password'    => Hash::make('password'),
            'last_name'   => 'Смирнов',
            'first_name'  => 'Алексей',
            'middle_name' => 'Николаевич',
            'group_id'    => $g['ИВТ41б']->id,
        ]);

        $student2 = User::create([
            'email'       => 'kozlova@edu.ugrasu.ru',
            'password'    => Hash::make('password'),
            'last_name'   => 'Козлова',
            'first_name'  => 'Анна',
            'middle_name' => 'Дмитриевна',
            'group_id'    => $g['ИВТ41б']->id,
        ]);

        $student3 = User::create([
            'email'       => 'novikov@edu.ugrasu.ru',
            'password'    => Hash::make('password'),
            'last_name'   => 'Новиков',
            'first_name'  => 'Дмитрий',
            'middle_name' => 'Андреевич',
            'group_id'    => $g['ПИ41б']->id,
        ]);

        $student4 = User::create([
            'email'       => 'fatao@edu.ugrasu.ru',
            'password'    => Hash::make('password'),
            'last_name'   => 'Abdulrahman',
            'first_name'  => 'Abdul Fatao',
            'middle_name' => null,
            'group_id'    => $g['ПИ41б']->id,
        ]);

        // Дисциплины
        $math    = Discipline::create(['code' => 'МАТ101', 'name' => 'Высшая математика']);
        $prog    = Discipline::create(['code' => 'ПРО201', 'name' => 'Программирование']);
        $db      = Discipline::create(['code' => 'БД301',  'name' => 'Базы данных']);
        $physics = Discipline::create(['code' => 'ФИЗ101', 'name' => 'Физика']);
        $networks = Discipline::create(['code' => 'СЕТ401', 'name' => 'Компьютерные сети']);

        // Привязка преподавателей к дисциплинам
        $math->teachers()->attach($teacher1->id);
        $prog->teachers()->attach($teacher1->id);
        $db->teachers()->attach($teacher2->id);
        $physics->teachers()->attach($teacher3->id);
        $networks->teachers()->attach($teacher2->id);

        // Задолженности
        $debt1 = Debt::create([
            'student_id'     => $student1->id,
            'discipline_id'  => $math->id,
            'assigned_by_id' => $teacher1->id,
            'status'         => 'DEBT',
            'comment'        => 'Не явился на экзамен в зимнюю сессию',
        ]);

        $debt2 = Debt::create([
            'student_id'     => $student2->id,
            'discipline_id'  => $prog->id,
            'assigned_by_id' => $teacher1->id,
            'status'         => 'DEBT',
            'comment'        => 'Не сдал курсовую работу',
        ]);

        $debt3 = Debt::create([
            'student_id'     => $student3->id,
            'discipline_id'  => $db->id,
            'assigned_by_id' => $teacher2->id,
            'status'         => 'DEBT',
        ]);

        $debt4 = Debt::create([
            'student_id'     => $student1->id,
            'discipline_id'  => $physics->id,
            'assigned_by_id' => $teacher3->id,
            'status'         => 'CLOSED',
            'grade_value'    => 4,
            'grade_scale'    => 'EXAM',
            'comment'        => 'Закрыта после пересдачи',
        ]);

        // Пересдача
        $retake = Retake::create([
            'discipline_id'    => $math->id,
            'type'             => 'REGULAR',
            'building_number'  => '3',
            'room_number'      => '201',
            'start_datetime'   => now()->addDays(5)->setTime(10, 0),
            'duration_minutes' => 90,
            'status'           => 'SCHEDULED',
            'created_by_id'    => $dean->id,
        ]);

        $retake->students()->attach($student1->id, ['result_status' => 'NOT_TAKEN']);
        $retake->teachers()->attach($teacher1->id);
        $retake->debts()->attach($debt1->id);

        // Уведомления
        Notification::send(
            $student1->id,
            Notification::TYPE_RETAKE_ASSIGNED,
            'Назначена пересдача',
            "Вам назначена пересдача по дисциплине «{$math->name}» на " .
            now()->addDays(5)->setTime(10, 0)->format('d.m.Y в H:i') . '.',
            ['related_retake_id' => $retake->id]
        );

        Notification::send(
            $teacher1->id,
            Notification::TYPE_RETAKE_ASSIGNED,
            'Назначена пересдача',
            "Вы назначены преподавателем на пересдачу по дисциплине «{$math->name}».",
            ['related_retake_id' => $retake->id]
        );

        $this->command->info('База данных заполнена тестовыми данными.');
        $this->command->newLine();
        $this->command->table(
            ['Роль', 'Email', 'Пароль'],
            [
                ['Администратор', 'admin@edu.ugrasu.ru',   'password'],
                ['Деканат',       'dean@edu.ugrasu.ru',    'password'],
                ['Преподаватель', 'ivanov@edu.ugrasu.ru',  'password'],
                ['Преподаватель', 'petrova@edu.ugrasu.ru', 'password'],
                ['Студент',       'smirnov@edu.ugrasu.ru', 'password'],
                ['Студент',       'kozlova@edu.ugrasu.ru', 'password'],
            ]
        );
    }
}