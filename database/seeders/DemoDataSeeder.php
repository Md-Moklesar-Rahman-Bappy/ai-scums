<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\Fee;
use App\Models\FeeType;
use App\Models\Institution;
use App\Models\Notice;
use App\Models\Routine;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

/**
 * DemoDataSeeder.
 *
 * Populates the demo school (slug "demo-school") with a realistic academic
 * dataset: an academic year, classes/sections, subjects, students, teachers,
 * attendance history, exams with marks, fees, notices and routines. This makes
 * the UI and the read-only AI Assistant immediately demonstrable after install.
 *
 * It is idempotent per institution: re-running only appends data, existing
 * rows are not duplicated because the demo school already exists.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('slug', 'demo-school')->firstOrFail();
        $instId = $institution->id;

        // --- Academic year -------------------------------------------------
        $academicYear = AcademicYear::firstOrCreate(
            ['institution_id' => $instId, 'name' => '2024-2025'],
            ['start_date' => '2024-04-01', 'end_date' => '2025-03-31', 'is_current' => true]
        );

        // --- Classes, sections and subjects --------------------------------
        $subjectNames = ['Mathematics', 'English', 'Science', 'Social Studies'];
        $classes = [];

        foreach (['Grade 9', 'Grade 10', 'Grade 11'] as $className) {
            $class = SchoolClass::firstOrCreate(
                ['institution_id' => $instId, 'academic_year_id' => $academicYear->id, 'name' => $className]
            );

            $sections = [];
            foreach (['A', 'B'] as $sectionName) {
                $sections[] = Section::firstOrCreate(
                    ['institution_id' => $instId, 'class_id' => $class->id, 'name' => $sectionName]
                );
            }

            $subjects = [];
            foreach ($subjectNames as $subjectName) {
                $subjects[] = Subject::firstOrCreate(
                    ['institution_id' => $instId, 'name' => $subjectName, 'class_id' => $class->id],
                    ['code' => strtoupper(substr($subjectName, 0, 3)).'-'.$class->id, 'type' => 'subject', 'credit_hours' => 1]
                );
            }

            $classes[] = ['class' => $class, 'sections' => $sections, 'subjects' => $subjects];
        }

        // --- Teachers ------------------------------------------------------
        $teachers = Teacher::factory()->count(6)->create(['institution_id' => $instId]);
        // Allocate subjects to teachers (round-robin).
        $subjectsAll = Subject::where('institution_id', $instId)->get();
        foreach ($subjectsAll as $index => $subject) {
            $subject->teachers()->attach($teachers[$index % $teachers->count()]->id);
        }

        // --- Students ------------------------------------------------------
        $lowAttendanceStudents = [];
        foreach ($classes as $c) {
            foreach ($c['sections'] as $section) {
                /** @var Section $section */
                $students = Student::factory()->count(5)->create([
                    'institution_id' => $instId,
                    'academic_year_id' => $academicYear->id,
                    'class_id' => $c['class']->id,
                    'section_id' => $section->id,
                ]);

                // Flag a couple of students for deliberately low attendance.
                if (count($lowAttendanceStudents) < 3) {
                    $lowAttendanceStudents[] = $students->first()->id;
                }
            }
        }

        $allStudents = Student::where('institution_id', $instId)->get();

        // --- Attendance (last 20 days) ------------------------------------
        $days = collect(range(0, 19))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));
        foreach ($allStudents as $student) {
            $isLow = in_array($student->id, $lowAttendanceStudents, true);
            foreach ($days as $day) {
                $status = $isLow
                    ? (fake()->boolean(55) ? 'absent' : 'present')
                    : (fake()->boolean(88) ? 'present' : fake()->randomElement(['absent', 'late']));

                Attendance::firstOrCreate(
                    ['institution_id' => $instId, 'student_id' => $student->id, 'subject_id' => null, 'date' => $day],
                    ['status' => $status, 'section_id' => $student->section_id]
                );
            }
        }

        // --- Exams and marks ----------------------------------------------
        foreach ($classes as $c) {
            foreach ($c['subjects'] as $subject) {
                /** @var Subject $subject */
                $exam = Exam::create([
                    'institution_id' => $instId,
                    'academic_year_id' => $academicYear->id,
                    'subject_id' => $subject->id,
                    'section_id' => $c['sections'][0]->id,
                    'name' => 'Midterm',
                    'exam_type' => 'theory',
                    'exam_date' => now()->subDays(10),
                    'total_marks' => 100,
                    'pass_marks' => 40,
                ]);

                $classStudents = Student::where('class_id', $c['class']->id)->get();
                foreach ($classStudents as $student) {
                    $obtained = fake()->numberBetween(28, 98);
                    ExamMark::firstOrCreate(
                        ['institution_id' => $instId, 'exam_id' => $exam->id, 'student_id' => $student->id],
                        [
                            'marks_obtained' => $obtained,
                            'total_marks' => 100,
                            'grade' => ExamMark::deriveGrade((float) $obtained, 100.0),
                        ]
                    );
                }
            }
        }

        // --- Fee types and fees -------------------------------------------
        $tuition = FeeType::firstOrCreate(
            ['institution_id' => $instId, 'name' => 'Tuition'],
            ['description' => 'Monthly tuition fee', 'default_amount' => 2000]
        );

        foreach ($allStudents as $student) {
            $roll = $student->id % 3;
            $paid = match ($roll) {
                0 => 2000,
                1 => 1000,
                default => 0,
            };
            $fee = Fee::create([
                'institution_id' => $instId,
                'student_id' => $student->id,
                'fee_type_id' => $tuition->id,
                'amount' => 2000,
                'paid_amount' => $paid,
                'due_date' => now()->addDays(15),
                'status' => 'pending',
            ]);
            $fee->recalcStatus();
            $fee->save();
        }

        // --- Notices (announcements + events) -----------------------------
        Notice::factory()->count(5)->create([
            'institution_id' => $instId,
            'type' => 'announcement',
            'audience' => 'all',
            'created_by' => null,
        ]);
        Notice::factory()->count(3)->create([
            'institution_id' => $instId,
            'type' => 'event',
            'audience' => 'all',
            'created_by' => null,
        ]);

        // --- Routines (weekly class schedule) -----------------------------
        $day = 1;
        foreach ($classes as $c) {
            foreach ($c['subjects'] as $subject) {
                foreach ($c['sections'] as $section) {
                    Routine::create([
                        'institution_id' => $instId,
                        'type' => 'class',
                        'subject_id' => $subject->id,
                        'teacher_id' => $subject->teachers()->first()?->id,
                        'section_id' => $section->id,
                        'day_of_week' => $day,
                        'start_time' => '09:00',
                        'end_time' => '09:45',
                        'room' => 'Room '.$c['class']->id.$section->name,
                    ]);
                }
                $day = $day >= 5 ? 1 : $day + 1;
            }
        }

        $this->command->info('Demo data seeded for institution: '.$institution->name);
    }
}
