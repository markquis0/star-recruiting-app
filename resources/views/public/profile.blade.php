@extends('layouts.app')

@section('title', $candidate->first_name . ' ' . $candidate->last_name . ' - Public Profile')

@section('content')
<div class="container" style="max-width: 900px; margin: 2rem auto; padding: 0 1rem;">
    {{-- Header --}}
    <div class="card mb-4">
        <div class="card-body">
            <h1 class="h3 mb-2" style="color: #FF3B6B;">
                {{ $candidate->first_name }} {{ $candidate->last_name }}
            </h1>
            <p class="text-muted mb-1">
                {{ $candidate->role_title ?? 'Candidate' }}
                @if(!empty($candidate->years_exp))
                    · {{ $candidate->years_exp }} years experience
                @endif
            </p>
            <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                Profile generated via Star Recruiting
            </p>
        </div>
    </div>

    {{-- Aptitude Summary --}}
    @if($aptitudeProfile)
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3" style="color: #FF3B6B;">Aptitude Profile</h2>
                
                @if(isset($aptitudeProfile['overall_accuracy']))
                    <p class="mb-2">
                        <strong>Overall accuracy:</strong> {{ $aptitudeProfile['overall_accuracy'] }}%
                    </p>
                @endif
                
                @if(!empty($aptitudeProfile['summary']))
                    <p class="mb-2">
                        <strong>Summary:</strong> {{ $aptitudeProfile['summary'] }}
                    </p>
                @endif
                
                @if(!empty($aptitudeProfile['confidence']))
                    <p class="mb-2">
                        <strong>Confidence:</strong> {{ ucfirst($aptitudeProfile['confidence']) }}
                    </p>
                @endif

                @if(!empty($aptitudeProfile['dimensions']))
                    <div class="mt-3">
                        <p class="small text-muted text-uppercase mb-2">Dimensions</p>
                        <ul class="list-unstyled mb-0">
                            @foreach($aptitudeProfile['dimensions'] as $key => $dim)
                                <li class="mb-1">
                                    <strong>{{ $dim['label'] ?? ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                    {{ $dim['accuracy'] ?? 'N/A' }}%
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Behavioral Summary --}}
    @if($behavioralAssessment)
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3" style="color: #FF3B6B;">Behavioral Profile</h2>
                
                @if($behavioralAssessment->category)
                    <p class="mb-2">
                        <strong>Category:</strong> {{ $behavioralAssessment->category }}
                    </p>
                @endif

                @if($behavioralAssessment->total_score !== null)
                    <p class="mb-2">
                        <strong>Overall Score:</strong> {{ $behavioralAssessment->total_score }}%
                    </p>
                @endif

                @php
                    $scoreSummary = $behavioralAssessment->score_summary ?? [];
                    $traits = is_array($scoreSummary) ? $scoreSummary : [];
                @endphp

                @if(!empty($traits))
                    <div class="mt-3">
                        <p class="small text-muted text-uppercase mb-2">Trait Scores</p>
                        @foreach($traits as $traitName => $score)
                            @if(is_numeric($score))
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small">{{ ucfirst($traitName) }}</span>
                                        <span class="small text-muted">{{ $score }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $score }}%; background-color: #FF3B6B;"
                                             aria-valuenow="{{ $score }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Projects --}}
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3" style="color: #FF3B6B;">
                Project Experience
                @if($projects->count())
                    ({{ $projects->count() }})
                @endif
            </h2>

            @if($projects->isEmpty())
                <p class="text-muted small">This candidate has not added any projects yet.</p>
            @else
                @foreach($projects as $form)
                    @php $data = $form->data ?? []; @endphp

                    <div class="border rounded mb-4 p-3">
                        <div class="mb-2">
                            <h3 class="h6 mb-1" style="color: #FF3B6B;">
                                {{ $data['project_title'] ?? 'Untitled Project' }}
                            </h3>
                            <p class="text-muted small mb-1">
                                @if(!empty($data['role_title']))
                                    {{ $data['role_title'] }}
                                @endif
                                @if(!empty($data['company']))
                                    @if(!empty($data['role_title'])) · @endif
                                    {{ $data['company'] }}
                                @endif
                            </p>
                            @if(!empty($data['timeframe']))
                                <p class="text-muted small">{{ $data['timeframe'] }}</p>
                            @endif
                        </div>

                        @if(!empty($data['summary_one_liner']))
                            <p class="small mb-2">{{ $data['summary_one_liner'] }}</p>
                        @endif

                        {{-- Metrics --}}
                        @if(!empty($data['metrics']) && is_array($data['metrics']))
                            <div class="mt-2 mb-2">
                                <p class="small text-muted text-uppercase mb-1">Key Metrics</p>
                                <ul class="small mb-0" style="list-style: disc; padding-left: 1.5rem;">
                                    @foreach($data['metrics'] as $m)
                                        <li>
                                            {{ $m['metric_name'] ?? 'Metric' }}:
                                            {{ $m['baseline_value'] ?? '?' }} → {{ $m['final_value'] ?? '?' }}
                                            @if(!empty($m['timeframe']))
                                                ({{ $m['timeframe'] }})
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Details --}}
                        <div class="mt-3 small">
                            @if(!empty($data['context']))
                                <p class="mb-1"><strong>Context:</strong> {{ $data['context'] }}</p>
                            @endif
                            @if(!empty($data['problem']))
                                <p class="mb-1"><strong>Problem / Opportunity:</strong> {{ $data['problem'] }}</p>
                            @endif
                            @if(!empty($data['affected_audience']))
                                <p class="mb-1"><strong>Affected Audience:</strong> {{ $data['affected_audience'] }}</p>
                            @endif
                            @if(!empty($data['primary_goal']))
                                <p class="mb-1"><strong>Primary Goal:</strong> {{ $data['primary_goal'] }}</p>
                            @endif
                            @if(!empty($data['responsibilities']))
                                <p class="mb-1"><strong>Responsibilities:</strong> {{ $data['responsibilities'] }}</p>
                            @endif
                            @if(!empty($data['teams_involved']) && is_array($data['teams_involved']))
                                <p class="mb-1"><strong>Teams Involved:</strong> {{ implode(', ', $data['teams_involved']) }}</p>
                            @endif
                            @if(!empty($data['collaboration_example']))
                                <p class="mb-1"><strong>Collaboration Example:</strong> {{ $data['collaboration_example'] }}</p>
                            @endif
                            @if(!empty($data['challenges']))
                                <p class="mb-1"><strong>Challenges:</strong> {{ $data['challenges'] }}</p>
                            @endif
                            @if(!empty($data['challenge_response']))
                                <p class="mb-1"><strong>How They Responded:</strong> {{ $data['challenge_response'] }}</p>
                            @endif
                            @if(!empty($data['tradeoffs']))
                                <p class="mb-1"><strong>Tradeoffs / Decisions:</strong> {{ $data['tradeoffs'] }}</p>
                            @endif
                            @if(!empty($data['outcome_summary']))
                                <p class="mb-1"><strong>Outcome:</strong> {{ $data['outcome_summary'] }}</p>
                            @endif
                            @if(!empty($data['impact']))
                                <p class="mb-1"><strong>Impact:</strong> {{ $data['impact'] }}</p>
                            @endif
                            @if(!empty($data['recognition']))
                                <p class="mb-1"><strong>Recognition / Follow-up:</strong> {{ $data['recognition'] }}</p>
                            @endif
                            @if(!empty($data['learning']))
                                <p class="mb-1"><strong>What They Learned:</strong> {{ $data['learning'] }}</p>
                            @endif
                            @if(!empty($data['retro']))
                                <p class="mb-0"><strong>What They'd Do Differently:</strong> {{ $data['retro'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="text-center text-muted small mt-4 mb-4">
        Generated by Star Recruiting – Profile is read-only and shared by the candidate.
    </div>
</div>
@endsection

