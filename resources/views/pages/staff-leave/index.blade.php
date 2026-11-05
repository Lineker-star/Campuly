@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('staff-leave.index'), 'text' => 'Staff leave', 'active'],
]])

@section('title', __('Staff leave'))
@section('page_heading', __('Staff leave'))

@section('content')
    <livewire:staff-leave-board />
@endsection
