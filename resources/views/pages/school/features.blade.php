@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('schools.settings'), 'text' => 'School setup'],
    ['text' => 'School features', 'active'],
]])

@section('title', __('School features'))
@section('page_heading', __('Choose the tools your school uses'))

@section('content')
    <livewire:manage-school-features />
@endsection
