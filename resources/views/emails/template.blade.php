@component('mail::message')
    {{-- Header --}}
    @slot('header')
        @if (!empty($custom_header))
            {!! $custom_header !!}
        @else
            @component('mail::header', ['url' => 'https://hr.dlabsrl.it/'])
            @endcomponent
        @endif
    @endslot

# {{$subject}}
{!! $body !!}

    {{-- Footer --}}
    @slot('footer')
        @if (!empty($custom_footer))
            {!! $custom_footer !!}
        @else
            @component('mail::footer')
                D.Lab s.r.l.
                Via Liberazione 100 - 31028 Vazzola (TV)
                P.IVA: IT05115050261
            @endcomponent
        @endif
    @endslot
@endcomponent
