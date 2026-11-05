<div>
    @if ($outlines->isEmpty() && $earlierSyllabi->isEmpty())
        <p class="text-sm text-muted-foreground">{{ __('The library has no outline for this subject yet, and no earlier syllabus of it is published.') }}</p>
    @else
        <form wire:submit="copy" class="flex flex-col gap-2 md:w-1/2">
            <label for="topic-source" class="text-sm font-medium">{{ __('Copy topics from') }}</label>
            <select id="topic-source" wire:model="source" {{ field_error_bindings('source') }} class="h-10 rounded-md border border-input bg-background px-3 text-sm">
                <option value="">{{ __('Choose a source') }}</option>
                @if ($outlines->isNotEmpty())
                    <optgroup label="Curriculum library">
                        @foreach ($outlines as $outline)
                            <option value="outline:{{ $outline->id }}">{{ $outline->name }} ({{ trans_choice(':count topic|:count topics', $outline->topics_count) }})</option>
                        @endforeach
                    </optgroup>
                @endif
                @if ($earlierSyllabi->isNotEmpty())
                    <optgroup label="Earlier syllabi">
                        @foreach ($earlierSyllabi as $earlier)
                            <option value="syllabus:{{ $earlier->id }}">
                                {{ __(':name · :name2 · :name3 (revision :revision, :topics_count)', ['name' => $earlier->name, 'name2' => $earlier->courseOffering->academicLevel->name, 'name3' => $earlier->courseOffering->academicPeriod->label ?? $earlier->courseOffering->academicPeriod->name, 'revision' => $earlier->revision, 'topics_count' => trans_choice(':count topic|:count topics', $earlier->topics_count)]) }}
                            </option>
                        @endforeach
                    </optgroup>
                @endif
            </select>
            <x-field-error name="source" />
            <p class="text-sm text-muted-foreground">{{ __('The topics are added after the ones already in this draft.') }}</p>
            <div><april:button type="submit" variant="outline" wire:loading.attr="disabled">{{ __('Copy topics') }}</april:button></div>
        </form>
    @endif
</div>
