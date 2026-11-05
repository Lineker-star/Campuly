<div>
    @if ($showHeading)
        <april:card>
            <slot:title>{{ $timetable->name }}</slot:title>
            <slot:description>
                @if ($showDescription && filled($timetable->description))
                    {{ $timetable->description }}
                @elseif ($showCalendar)
                    {{ __('Scan this timetable by month, week, or day.') }}
                @else
                    {{ __(':filled_count of :slot_count places on the week are taken.', ['filled_count' => $grid['filled_count'], 'slot_count' => $grid['slot_count']]) }}
                @endif
            </slot:description>
            <slot:content>
                @if ($audienceNote !== '')
                    <p class="mb-4 rounded-md border border-primary/20 bg-primary/5 px-3 py-2 text-sm text-muted-foreground">
                        {{ $audienceNote }}
                    </p>
                @endif
                @if ($showCalendar)
                    @include('livewire.partials.read-only-timetable-calendar')
                @else
                    @include('livewire.partials.timetable-grid', ['grid' => $grid])
                @endif
            </slot:content>
        </april:card>
    @else
        @if ($showCalendar)
            @include('livewire.partials.read-only-timetable-calendar')
        @else
            @include('livewire.partials.timetable-grid', ['grid' => $grid])
        @endif
    @endif
</div>
