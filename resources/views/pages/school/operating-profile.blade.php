@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('schools.settings'), 'text' => 'School setup'],
    ['text' => 'School language', 'active'],
]])

@section('title', __('School language'))
@section('page_heading', __('Use the words your school uses'))

@section('content')
    <livewire:edit-school-language :setup="request()->boolean('setup')" />
@endsection
