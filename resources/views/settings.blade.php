@extends('statamic::layout')
@section('title', 'Cookie Consent')

@section('content')
    <div class="max-w-3xl mx-auto p-4">
        @if ($sites->count() > 1)
            <div class="card p-1 mb-4 flex gap-1">
                @foreach ($sites as $s)
                    <a href="{{ cp_route('cookie-consent.index', ['site' => $s->handle()]) }}"
                       class="btn flat {{ $s->handle() === $site ? 'btn-primary' : '' }}">{{ $s->name() }}</a>
                @endforeach
            </div>
        @endif

        <cookie-consent-settings
            :blueprint="{{ Illuminate\Support\Js::from($blueprint) }}"
            :values="{{ Illuminate\Support\Js::from($values) }}"
            :meta="{{ Illuminate\Support\Js::from($meta) }}"
            submit-url="{{ cp_route('cookie-consent.update', ['site' => $site]) }}"
        ></cookie-consent-settings>
    </div>
@endsection
