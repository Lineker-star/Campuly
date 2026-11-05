@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('course-offerings.index'), 'text' => school_terms('course', 'Course').' being taught'],
    ['href' => route('course-offerings.roll-forward.show'), 'text' => 'Roll over subjects', 'active'],
]])

@section('title', __('Roll over subjects'))
@section('page_heading', __('Roll over subjects'))

@section('content')
    <livewire:roll-over-subjects :setup="request()->boolean('setup')" />
@endsection
