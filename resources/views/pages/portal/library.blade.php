@extends('layouts.app', ['breadcrumbs' => [['href' => route('dashboard'), 'text' => 'Dashboard'], ['href' => route('portal.overview'), 'text' => 'My school'], ['text' => 'Library', 'active']]])

@section('title', __('Library'))
@section('page_heading', __('Library'))

@section('content')
    <div class="flex flex-col gap-6">
        <april:card>
            <slot:title>{{ $studentRecord->user?->name ?? $studentRecord->admission_number }}</slot:title>
            <slot:description>{{ __(':name · Books currently borrowed', ['name' => $studentRecord->school?->name]) }}</slot:description>
            <slot:content>
                @if ($loans->isEmpty())
                    <p class="text-sm text-muted-foreground">{{ __('There are no books on loan.') }}</p>
                @else
                    <div class="flex flex-col divide-y">
                        @foreach ($loans as $loan)
                            <div class="flex flex-col gap-1 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-medium">{{ $loan->copy?->title?->title }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ __('Copy :barcode', ['barcode' => $loan->copy?->barcode]) }}
                                        @if ($loan->school_id !== $studentRecord->school_id)
                                            {{ __('· Return to :name', ['name' => $loan->school?->name]) }}
                                        @endif
                                    </p>
                                </div>
                                <p class="text-sm {{ $loan->daysLate() > 0 ? 'text-destructive' : 'text-muted-foreground' }}">
                                    {{ __('Due :date', ['date' => local_date($loan->due_on, 'medium')]) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </slot:content>
        </april:card>

        <april:card>
            <slot:title>{{ __('Library queue') }}</slot:title>
            <slot:description>{{ __('Titles reserved for this learner.') }}</slot:description>
            <slot:content>
                @if ($reservations->isEmpty())
                    <p class="text-sm text-muted-foreground">{{ __('There are no active reservations.') }}</p>
                @else
                    <div class="flex flex-col divide-y">
                        @foreach ($reservations as $reservation)
                            <div class="flex flex-col gap-1 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-medium">{{ $reservation->title?->title }}</p>
                                    <p class="text-sm text-muted-foreground">{{ $reservation->status->label() }}</p>
                                </div>
                                @if ($reservation->holds_until)
                                    <p class="text-sm text-muted-foreground">{{ __('Collect by :date', ['date' => local_date($reservation->holds_until, 'medium')]) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </slot:content>
        </april:card>
    </div>
@endsection
