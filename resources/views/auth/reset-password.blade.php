@extends('layouts.guest')

@section('title', __('Reset Password'))

@section('body')
    <x-partials.authentication-card title="{{ __('Reset your password') }}" :description="__('Choose a new password for your account.')">
        <x-display-validation-errors />
        <livewire:auth.reset-password-form :request="$request" />
    </x-partials.authentication-card>
@endsection
