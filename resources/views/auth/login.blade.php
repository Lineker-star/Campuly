@extends('layouts.guest')

@section('title', 'Login')

@section('body')
    <x-partials.authentication-card title="Welcome back" description="{{ __('Sign in to continue to :app.', ['app' => config('app.name')]) }}">
        <x-display-validation-errors />
        <livewire:auth.login-form />
        <x-slot:footer>
            <a href="{{ route('password.request') }}" class="font-medium text-foreground underline-offset-4 hover:underline" aria-label="Forgot Password">Forgot your password?</a>
        </x-slot:footer>
    </x-partials.authentication-card>
@endsection
