@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('health-records.index'), 'text' => 'Health records', 'active'],
]])

@section('title', __('Health records'))
@section('page_heading', __('Health records'))

@section('content')
    @livewire('student-health-record-directory')
@endsection
