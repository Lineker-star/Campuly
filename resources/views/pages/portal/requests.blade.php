@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('portal.overview'), 'text' => 'My school'],
    ['text' => 'Requests', 'active'],
]])

@section('title', __('Requests'))
@section('page_heading', __('Requests'))

@section('content')
    <livewire:portal-requests :student-record="$studentRecord" />
@endsection
