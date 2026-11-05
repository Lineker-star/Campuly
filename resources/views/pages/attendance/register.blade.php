@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['text' => 'Attendance register', 'active'],
]])

@section('title', __('Attendance register'))
@section('page_heading', __('Attendance register'))

@section('content')
    <livewire:attendance-register />
@endsection
