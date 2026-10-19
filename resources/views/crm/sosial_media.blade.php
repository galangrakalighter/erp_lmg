@php
    $socialMedia = $socialMedia ?? null;

    if (is_string($socialMedia)) {
        $decoded = json_decode($socialMedia, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $socialMedia = $decoded;
        } else {
            $socialMedia = [
                'lainnya' => $socialMedia,
            ];
        }
    }
@endphp

@if(is_array($socialMedia) && count(array_filter($socialMedia)))
    <div class="d-flex flex-column align-items-start">
        @foreach($socialMedia as $platform => $links)
            @php
                $links = is_array($links) ? $links : [$links];
            @endphp

            @foreach($links as $link)
                @if(!empty($link))
                    <a
                        href="{{ filter_var($link, FILTER_VALIDATE_URL) ? $link : '#' }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="badge badge-light border text-primary mb-1"
                    >
                        {{ ucfirst($platform) }}
                    </a>
                @endif
            @endforeach
        @endforeach
    </div>
@else
    <span class="text-muted">-</span>
@endif