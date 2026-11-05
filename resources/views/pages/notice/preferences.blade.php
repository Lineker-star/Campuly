@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['text' => 'Notice delivery', 'active'],
]])

@section('title', __('Notice delivery'))
@section('page_heading', __('Notice delivery'))

@section('content')
    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6">
        <livewire:notice-email-preferences />
    </div>
@endsection
