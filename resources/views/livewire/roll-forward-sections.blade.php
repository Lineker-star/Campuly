@php
    $controlClasses = 'mt-1 h-11 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring';
    $copies = $preview['copies'] ?? collect();
    $skips = $preview['skips'] ?? collect();
    $leftBehind = $preview['leftBehind'] ?? collect();
    $sectionWord = strtolower(school_term('section', 'section'));
    $sectionsWord = strtolower(school_terms('section', 'Section'));
    $targetIsClosed = $target?->isClosed() ?? false;
@endphp
<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <p class="text-sm text-muted-foreground">{{ __('Each :section_word becomes a draft in the new year with its name, stream, shift, language, room, and capacity. Learners, :term, attendance, results, and timetables stay behind.', ['section_word' => $sectionWord, 'term' => strtolower(school_terms('homeroom_teacher', 'Class teacher'))]) }}</p>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="source-year" class="text-sm text-muted-foreground">{{ __('Copy from') }}</label>
            <select id="source-year" wire:model.live="sourceAcademicYearId" class="{{ $controlClasses }}" {{ field_error_bindings('sourceAcademicYearId') }}>
                <option value="">{{ __('Choose a school year') }}</option>
                @foreach ($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                @endforeach
            </select>
            <x-field-error name="sourceAcademicYearId" class="mt-1" />
        </div>
        <div>
            <label for="target-year" class="text-sm text-muted-foreground">{{ __('Create in') }}</label>
            <select id="target-year" wire:model.live="targetAcademicYearId" class="{{ $controlClasses }}" {{ field_error_bindings('targetAcademicYearId') }}>
                <option value="">{{ __('Choose a school year') }}</option>
                @foreach ($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                @endforeach
            </select>
            <x-field-error name="targetAcademicYearId" class="mt-1" />
        </div>
    </div>

    @if ($problem)
        <p class="text-sm text-destructive" role="alert">{{ $problem }}</p>
    @elseif ($preview !== null)
        @if ($targetIsClosed)
            <p class="text-sm text-destructive" role="alert">{{ __(':name is closed. Reopen it before you copy :sections_word into it.', ['name' => $target->name, 'sections_word' => $sectionsWord]) }}</p>
        @endif

        @if ($copies->isNotEmpty())
            <section class="space-y-3">
                <h2 class="text-base font-semibold">{{ __('Will be created as drafts') }}</h2>
                <ul class="divide-y border-y">
                    @foreach ($copies as $copy)
                        <li wire:key="copy-{{ $copy->id }}" class="flex flex-col gap-1 py-3 text-sm sm:flex-row sm:items-center sm:gap-4">
                            <span class="min-w-0 flex-1 font-medium">{{ $copy->academicLevel->name }} · {{ $copy->label ?? $copy->name }}</span>
                            <span class="text-muted-foreground">{{ collect([$copy->stream, $copy->shift, $copy->room])->filter()->join(' · ') ?: '—' }} · <span class="tabular-nums">{{ $copy->capacity ?? '—' }}</span> {{ __('places') }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($skips->isNotEmpty())
            <section class="space-y-1 text-sm">
                <h2 class="text-base font-semibold">{{ __('Already in :name', ['name' => $target->name]) }}</h2>
                <p class="text-muted-foreground">{{ $skips->map(fn ($skip) => $skip->academicLevel->name.' · '.$skip->name)->join('; ') }}</p>
            </section>
        @endif

        @if ($leftBehind->isNotEmpty())
            <section class="space-y-1 text-sm">
                <h2 class="text-base font-semibold">{{ __('Left behind') }}</h2>
                <p class="text-muted-foreground">{{ __(':join. The :section_word is archived, or its :term is archived or now holds other :term2.', ['join' => $leftBehind->map(fn ($section) => $section->academicLevel->name.' · '.$section->name)->join('; '), 'section_word' => $sectionWord, 'term' => strtolower(school_term('class_level', 'class')), 'term2' => strtolower(school_terms('class_level', 'Class'))]) }}</p>
            </section>
        @endif

        @if ($copies->isEmpty() && $skips->isEmpty() && $leftBehind->isEmpty())
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <p class="text-muted-foreground">{{ __(':name has no :sections_word to copy.', ['name' => $source->name, 'sections_word' => $sectionsWord]) }}</p>
                <april:button-link href="{{ route('academic-cycle-sections.create', ['academic_year_id' => $source->id] + ($setup ? ['setup' => 1, 'school_setup' => 1] : [])) }}" variant="outline" class="h-11 select-none">{{ __('Add a :section_word to :name', ['section_word' => $sectionWord, 'name' => $source->name]) }}</april:button-link>
            </div>
        @elseif ($copies->isEmpty())
            <p class="text-sm text-muted-foreground">{{ __('Nothing left to copy into :name.', ['name' => $target->name]) }}</p>
        @endif

        @if ($copies->isNotEmpty() && ! $targetIsClosed)
            <div>
                <april:button type="button" class="h-11 select-none" wire:click="rollForward" wire:loading.attr="disabled" wire:target="rollForward">{{ __('Create :count draft :count2', ['count' => $copies->count(), 'count2' => $copies->count() === 1 ? $sectionWord : $sectionsWord]) }}</april:button>
            </div>
        @endif
    @endif
</div>
