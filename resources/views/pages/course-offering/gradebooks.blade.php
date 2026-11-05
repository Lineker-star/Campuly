@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('gradebooks.index'), 'text' => 'Gradebooks', 'active'],
]])

@section('title', __('Gradebooks'))
@section('page_heading', __('Gradebooks'))

@section('content')
    <livewire:gradebook-directory />
@endsection
