@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('portal-requests.index'), 'text' => 'Family requests', 'active'],
]])

@section('title', __('Family requests'))
@section('page_heading', __('Family requests'))

@section('content')
    <livewire:portal-request-inbox />
@endsection
