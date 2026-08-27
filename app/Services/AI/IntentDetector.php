<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * IntentDetector.
 *
 * Implements "Step 2: Intent Detection" of the assistant pipeline. Uses a
 * transparent, explainable keyword/regex matcher (suitable for a thesis and
 * easy to extend). Returns the best matching {@see Intent} and a confidence
 * score. The detector is intentionally deterministic so behaviour is
 * auditable and reproducible.
 */
class IntentDetector
{
    /**
     * Keyword patterns per intent (lowercased).
     *
     * @var array<string, array<int, string>>
     */
    private array $patterns = [
        Intent::STUDENT_ATTENDANCE => ['attendance', 'present', 'absent', 'attended'],
        Intent::STUDENT_NEXT_EXAM => ['next exam', 'upcoming exam', 'exam schedule', 'my exam'],
        Intent::STUDENT_CGPA => ['cgpa', 'gpa', 'grade point', 'my result', 'my marks'],
        Intent::STUDENT_SCHEDULE => ['my schedule', 'my routine', 'timetable', 'class schedule'],
        Intent::TEACHER_LOW_ATTENDANCE => ['below 75', 'low attendance', 'attendance below', 'poor attendance'],
        Intent::TEACHER_COURSE_PERFORMANCE => ['course performance', 'performance analysis', 'subject analysis'],
        Intent::TEACHER_PENDING_EVALUATIONS => ['pending evaluation', 'pending marks', 'unevaluated', 'not graded'],
        Intent::ADMIN_ADMISSION_STATS => ['admission statistic', 'admission stats', 'admissions'],
        Intent::ADMIN_ENROLLMENT_REPORT => ['enrollment report', 'enrollment', 'enrolled students'],
        Intent::ADMIN_OUTSTANDING_FEES => ['outstanding fee', 'due fee', 'pending payment', 'fee due'],
    ];

    /**
     * Detect the intent for a given query.
     *
     * @return array{intent: string, confidence: float}
     */
    public function detect(string $query): array
    {
        $text = strtolower(trim($query));

        $best = Intent::GENERAL;
        $bestScore = 0;

        foreach ($this->patterns as $intent => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $score += strlen($keyword);
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $intent;
            }
        }

        $confidence = $bestScore > 0 ? min(1.0, $bestScore / 20) : 0.0;

        return ['intent' => $best, 'confidence' => $confidence];
    }
}
