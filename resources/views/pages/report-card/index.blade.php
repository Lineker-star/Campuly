@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('report-cards.index'), 'text' => 'Report cards', 'active'],
]])

@section('title', __('Report cards'))
@section('page_heading', __('Report cards'))

@section('content')
    <livewire:report-card-directory />
@endsection
