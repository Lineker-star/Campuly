<?php

namespace App\Actions\Enrollment;

use App\Actions\Admissions\DeclineWaitlistEntry;
use App\Actions\Audit\RecordAuditEvent;
use App\Enums\AcademicStructureStatus;
use App\Enums\AuditAction;
use App\Exceptions\InvalidValueException;
use App\Models\AcademicCycleSection;
use App\Models\AcademicPeriod;
use App\Models\EnrollmentPlacement;
use App\Models\StudentRecord;
use App\Models\User;
use App\Services\Academic\SectionSeats;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Append placement history for admissions, promotions, and section changes.
 * Repeating the current placement adds no history.
 */
class ChangeEnrollmentPlacement
{
    public function __construct(
        private RecordAuditEvent $auditor,
        private SectionSeats $seats,
        private DeclineWaitlistEntry $waitlist,
    ) {}

    /**
     * Place the enrollment in an exact academic-cycle section.
     *
     * @throws InvalidValueException when the cycle section, period, or enrollment does not fit
     */
    public function place(
        StudentRecord $enrollment,
        AcademicCycleSection $academicCycleSection,
        ?AcademicPeriod $academicPeriod = null,
        ?User $actor = null,
        ?string $reason = null,
        ?CarbonInterface $effectiveOn = null,
    ): StudentRecord {
        $enrollmentId = $enrollment->getKey();

        return DB::transaction(function () use ($enrollmentId, $academicCycleSection, $academicPeriod, $actor, $reason, $effectiveOn): StudentRecord {
            // Serialize placement changes for one enrollment. This prevents
            // two retries from creating duplicate history rows.
            $enrollment = StudentRecord::query()
                ->lockForUpdate()
                ->findOrFail($enrollmentId);
            $academicCycleSection = AcademicCycleSection::query()
                ->with('academicYear')
                ->lockForUpdate()
                ->findOrFail($academicCycleSection->getKey());
            $academicYear = $academicCycleSection->academicYear;

            $this->failIfRecordsDoNotFit($enrollment, $academicCycleSection, $academicPeriod);

            // The pointer moves now, so the history cannot say the learner
            // sits here only from a later day.
            if ($effectiveOn !== null && $effectiveOn->isAfter(school_today()->endOfDay())) {
                throw new InvalidValueException(__('A new place starts when it is made. Choose today or an earlier day.'));
            }

            // The same place in the same year is not a move. Record nothing.
            if ($this->alreadyPlaced($enrollment, $academicCycleSection)) {
                return $enrollment;
            }

            // A seat offered to a waiting family is taken, so a learner placed
            // another way cannot fill it before the family answers.
            if ($this->seats->isFull($academicCycleSection)) {
                throw new InvalidValueException(
                    __('The :term is full at :capacity seats, counting open waitlist offers. Add the candidate to its admission waitlist instead.', ['term' => strtolower(school_term('section', 'section')), 'capacity' => $academicCycleSection->capacity])
                );
            }

            EnrollmentPlacement::create([
                'student_record_id' => $enrollment->id,
                'academic_year_id' => $academicYear->id,
                'academic_period_id' => $academicPeriod?->id,
                'academic_cycle_section_id' => $academicCycleSection->id,
                'effective_on' => $effectiveOn ?? school_today($enrollment->school_id),
                'changed_by' => $actor?->id,
                'reason' => $reason,
            ]);

            // The enrollment keeps a pointer to where the student sits now.
            $enrollment->academic_cycle_section_id = $academicCycleSection->id;
            $enrollment->save();

            $enrollment->academicYears()->syncWithoutDetaching([$academicYear->id => [
                'academic_cycle_section_id' => $academicCycleSection->id,
            ]]);

            $this->waitlist->withdrawForEnrolled($enrollment, $actor);

            $this->auditor->record(
                AuditAction::EnrollmentPlaced,
                $enrollment,
                [
                    'academic_year_id' => $academicYear->id,
                    'academic_cycle_section_id' => $academicCycleSection->id,
                    'reason' => $reason,
                ],
                $actor,
            );

            return $enrollment;
        });
    }

    /**
     * Check that the cycle section and period belong to this enrollment.
     *
     * @throws InvalidValueException
     */
    private function failIfRecordsDoNotFit(
        StudentRecord $enrollment,
        AcademicCycleSection $academicCycleSection,
        ?AcademicPeriod $academicPeriod,
    ): void {
        if ($enrollment->status->isClosed()) {
            throw new InvalidValueException(__('This enrollment is closed. It cannot take a new placement.'));
        }

        if ($academicCycleSection->school_id !== $enrollment->school_id) {
            throw new InvalidValueException(__('That :term belongs to another school.', ['term' => strtolower(school_term('section', 'section'))]));
        }

        if ($academicCycleSection->academicYear->isClosed()) {
            throw new InvalidValueException(__('The academic year is closed. Reopen it before you move a student.'));
        }

        if ($academicCycleSection->status !== AcademicStructureStatus::Active) {
            throw new InvalidValueException(__('Activate the :term before placing a student in it.', ['term' => strtolower(school_term('section', 'section'))]));
        }

        if ($academicPeriod !== null && $academicPeriod->academic_year_id !== $academicCycleSection->academic_year_id) {
            throw new InvalidValueException(__('That :term is not part of the :term2’s :term3.', ['term' => strtolower(school_term('period', 'period')), 'term2' => strtolower(school_term('section', 'section')), 'term3' => strtolower(school_term('academic_year', 'school year'))]));
        }
    }

    /**
     * Check if the enrollment already sits here in this year.
     */
    private function alreadyPlaced(
        StudentRecord $enrollment,
        AcademicCycleSection $academicCycleSection,
    ): bool {
        return $enrollment->academic_cycle_section_id === $academicCycleSection->id;
    }
}
