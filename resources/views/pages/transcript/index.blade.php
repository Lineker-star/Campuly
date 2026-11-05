@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['text' => 'Transcripts', 'active'],
]])

@section('title', __('Transcripts'))
@section('page_heading', __('Transcripts'))

@section('content')
    <div class="space-y-6">
        @if ($errors->has('transcript'))
            <april:alert variant="destructive">
                <slot:title>{{ __('The transcript was not issued') }}</slot:title>
                <slot:description>{{ $errors->first('transcript') }}</slot:description>
            </april:alert>
        @endif

        <livewire:transcript-directory />
    </div>
@endsection
