@extends('layouts.guest')

@section('title', __('Forgot password'))

@section('body')
    <x-partials.authentication-card title="{{ __('Forgot your password?') }}" :description="__('We will send a reset link to your email address.')">
        <x-display-validation-errors />
        <livewire:auth.forgot-password-form />
    </x-partials.authentication-card>
@endsection
