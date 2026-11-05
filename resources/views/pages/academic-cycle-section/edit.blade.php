@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('academic-cycle-sections.index'), 'text' => school_terms('section', 'Sections')],
    ['href' => route('academic-cycle-sections.show', $academicCycleSection), 'text' => $academicCycleSection->name],
    ['href' => route('academic-cycle-sections.edit', $academicCycleSection), 'text' => 'Edit', 'active'],
]])

@section('title', __("Edit $academicCycleSection->name"))
@section('page_heading', __("Edit $academicCycleSection->name"))

@section('content')
    <april:card class="mx-auto max-w-3xl">
        <slot:title>{{ __('Edit :name · :name2 · :name3', ['name' => $academicCycleSection->academicLevel->name, 'name2' => $academicCycleSection->name, 'name3' => $academicCycleSection->academicYear->name]) }}</slot:title>
        <slot:description>
            {{ __('A change here updates this one :term in this one :term2. No learner, result, attendance, or timetable record moves.', ['term' => strtolower(school_term('section', 'section')), 'term2' => strtolower(school_term('academic_year', 'school year'))]) }}
        </slot:description>
        <slot:content>
            <livewire:academic-cycle-section-form :academic-cycle-section="$academicCycleSection" />
        </slot:content>
    </april:card>
@endsection
