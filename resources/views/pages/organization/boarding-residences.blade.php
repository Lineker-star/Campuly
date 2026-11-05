@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('organizations.index'), 'text' => 'Organizations'],
    ['href' => route('organizations.show', $organization), 'text' => $organization->name],
    ['text' => 'Shared residences', 'active'],
]])

@section('title', __('Shared residences'))

@section('page_heading', __('Shared residences'))

@section('content')
    <div class="mx-auto w-full max-w-6xl">
        @livewire('organization-boarding-residences', ['organization' => $organization])
    </div>
@endsection
