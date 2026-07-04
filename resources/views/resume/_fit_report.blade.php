@php
    $recommendation = $analysisSections['recommendation'] ?? '';
    $recommendationClass = match (true) {
        str_contains($recommendation, 'Strong') => 'rec-strong',
        str_contains($recommendation, 'Good') => 'rec-good',
        str_contains($recommendation, 'Borderline') => 'rec-borderline',
        default => 'rec-low',
    };

    $matchPercent = max(0, min(100, (int) ($analysisSections['match_percentage'] ?? 0)));
    $ringRadius = 38;
    $ringCircumference = round(2 * M_PI * $ringRadius, 2);
    $ringOffset = round($ringCircumference * (1 - ($matchPercent / 100)), 2);
    $ringTone = match (true) {
        $matchPercent >= 80 => 'fit-score-ring--strong',
        $matchPercent >= 65 => 'fit-score-ring--good',
        $matchPercent >= 45 => 'fit-score-ring--borderline',
        default => 'fit-score-ring--low',
    };
@endphp

<div class="fit-card fit-report-card">
    <div class="fit-results__header">
        <div>
            <div class="fit-card__title" style="margin-bottom: 2px;">Candidate match assessment</div>
            <div class="fit-results__stamp">Generated from your latest submission</div>
        </div>
    </div>

    <div class="fit-score-block">
        <div class="fit-score-ring {{ $ringTone }}" data-percent="{{ $matchPercent }}">
            <svg class="fit-score-ring__svg" viewBox="0 0 88 88" aria-hidden="true">
                <circle class="fit-score-ring__track" cx="44" cy="44" r="{{ $ringRadius }}"></circle>
                <circle
                    class="fit-score-ring__progress"
                    cx="44"
                    cy="44"
                    r="{{ $ringRadius }}"
                    stroke-dasharray="{{ $ringCircumference }}"
                    stroke-dashoffset="{{ $ringCircumference }}"
                    data-target-offset="{{ $ringOffset }}"
                    transform="rotate(-90 44 44)"
                ></circle>
            </svg>
            <span class="fit-score-ring__value">{{ $matchPercent }}%</span>
        </div>
        <div class="fit-score-meta">
            <h3>Overall match</h3>
            <p>{{ $analysisSections['must_haves_line'] }}</p>
        </div>
    </div>

    <div class="fit-section" style="margin-top: 0; padding-top: 0; border-top: none;">
        <h4>Summary</h4>
        <p>{{ $analysisSections['summary'] }}</p>
    </div>

    @if (!empty($analysisSections['strengths']))
        <div class="fit-section">
            <h4>Strengths</h4>
            <ol>
                @foreach ($analysisSections['strengths'] as $strength)
                    <li>{{ $strength }}</li>
                @endforeach
            </ol>
        </div>
    @endif

    @if (!empty($analysisSections['gaps']))
        <div class="fit-section">
            <h4>Gaps</h4>
            <ol>
                @foreach ($analysisSections['gaps'] as $gap)
                    <li>{{ $gap }}</li>
                @endforeach
            </ol>
        </div>
    @endif

    <div class="fit-section">
        <h4>Recommendation</h4>
        <span class="fit-recommendation {{ $recommendationClass }}">{{ $analysisSections['recommendation'] }}</span>
    </div>
</div>
