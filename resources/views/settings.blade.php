@extends('statamic::layout')
@section('title', 'Cookie Consent')

@section('content')
    <div class="max-w-3xl mx-auto p-4">
        <h1 class="mb-6">Cookie Consent</h1>

        @if ($sites->count() > 1)
            <div class="mb-4 flex gap-2">
                @foreach ($sites as $s)
                    <a href="{{ cp_route('cookie-consent.index', ['site' => $s->handle()]) }}"
                       class="btn {{ $s->handle() === $site ? 'btn-primary' : '' }}">{{ $s->name() }}</a>
                @endforeach
            </div>
        @endif

        @if (session('success'))
            <div class="card p-3 mb-4 text-green-700">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ cp_route('cookie-consent.update') }}" class="card p-4">
            @csrf
            <input type="hidden" name="site" value="{{ $site }}">

            <div class="mb-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="enabled" value="1" @checked($settings['enabled'])>
                    <span class="font-bold text-sm">Ativar o banner de cookies neste site</span>
                </label>
                <p class="help-block">Desativado: nenhum banner aparece e todo conteúdo condicionado a grupos é tratado como permitido.</p>
            </div>

            <div class="mb-4">
                <label class="font-bold text-sm block mb-1">Versão do consentimento</label>
                <input type="number" name="version" min="1" value="{{ $settings['version'] }}" class="input-text" required>
                <p class="help-block">Aumente esse número para forçar todos os visitantes a decidir de novo.</p>
            </div>

            <div class="mb-4">
                <label class="font-bold text-sm block mb-1">Posição do banner</label>
                <select name="position" class="input-text">
                    @foreach (['bottom' => 'Inferior', 'top' => 'Superior', 'bottom-left' => 'Inferior esquerda', 'bottom-right' => 'Inferior direita'] as $value => $label)
                        <option value="{{ $value }}" @selected($settings['position'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="font-bold text-sm block mb-1">Tema</label>
                <select name="theme" class="input-text">
                    @foreach (['auto' => 'Automático (sistema)', 'light' => 'Claro', 'dark' => 'Escuro'] as $value => $label)
                        <option value="{{ $value }}" @selected($settings['theme'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <hr class="my-4">

            @foreach ($settings['text'] as $key => $value)
                <div class="mb-4">
                    <label class="font-bold text-sm block mb-1">{{ $key }}</label>
                    <input type="text" name="text[{{ $key }}]" value="{{ $value }}" class="input-text">
                </div>
            @endforeach

            <hr class="my-4">

            <div class="mb-4">
                <label class="font-bold text-sm block mb-1">Grupos de cookies (JSON)</label>
                <textarea name="groups_json" rows="16" class="input-text font-mono text-xs">{{ json_encode($settings['groups'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                <p class="help-block">
                    Cada grupo: <code>name</code>, <code>description</code>, <code>required</code>,
                    <code>default</code>, <code>legal_basis</code> (RGPD/LGPD), <code>cookies[]</code>
                    (cada um com <code>name</code>, <code>purpose</code>, <code>retention</code>).
                </p>
            </div>

            <div class="mb-4">
                <label class="font-bold text-sm block mb-1">Consent Mode (JSON)</label>
                <textarea name="consent_mode_json" rows="6" class="input-text font-mono text-xs" data-role="consent-mode-json">{{ json_encode($settings['consent_mode'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                <p class="help-block">Mapa <code>grupo → sinais do Google Consent Mode v2</code> (ad_storage, analytics_storage, ...).</p>
            </div>

            <button type="submit" class="btn-primary">Salvar</button>
        </form>
    </div>

    <script>
        // consent_mode is validated server-side as an array, but the textarea
        // above posts it as text like groups_json — decode client-side into
        // the hidden field's PHP-array wire format before submit.
        document.querySelector('form').addEventListener('submit', function () {
            var json = document.querySelector('[data-role="consent-mode-json"]').value;
            var parsed = JSON.parse(json);
            var container = this;
            Object.keys(parsed).forEach(function (group) {
                parsed[group].forEach(function (signal, i) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'consent_mode[' + group + '][' + i + ']';
                    input.value = signal;
                    container.appendChild(input);
                });
            });
        });
    </script>
@endsection
