@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('timetables.index'), 'text' => 'Timetables'],
    ['href' => route('timetables.week'), 'text' => 'My week', 'active'],
]])

@section('title', __('My week'))
@section('page_heading', __('My week'))

@section('content')
    <april:card>
        <slot:title>{{ __('Lessons you teach') }}</slot:title>
        @if ($timetables->isNotEmpty())
            <slot:description>{{ __('From :and', ['and' => $timetables->pluck('name')->join(', ', ' and ')]) }}</slot:description>
        @endif
        <slot:content>
            @if ($days === [])
                <x-empty-state icon="lucide-calendar-clock" title="{{ __('No lessons this week') }}"
                    :description="__('Your lessons appear here when a published timetable places a subject you teach.')" />
            @else
                <x-personal-week :days="$days" />
            @endif
        </slot:content>
    </april:card>
@endsection
