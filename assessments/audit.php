<?php
// AUDIT — Alcohol Use Disorders Identification Test (Babor, Higgins-Biddle, Saunders & Monteiro,
// World Health Organization, 2001). Free to use. Risk zones follow the WHO manual.
$frequency = [0 => 'Never', 1 => 'Less than monthly', 2 => 'Monthly', 3 => 'Weekly', 4 => 'Daily or almost daily'];
$yesNo     = [0 => 'No', 2 => 'Yes, but not in the last year', 4 => 'Yes, during the last year'];
$skipIfNonDrinker = ['q1', 0];

return [
    'id'           => 'audit',
    'order'        => 5,
    'title'        => 'AUDIT Alcohol Use Screening',
    'summary'      => 'A 10-question check, developed by the World Health Organization, of drinking habits and alcohol-related risk.',
    'minutes'      => 3,
    'instructions' => 'Please answer about your alcohol use during the past year. One "drink" means one standard drink: about one small peg (30 ml) of spirits, one glass (100 ml) of wine, or one bottle (330 ml) of beer.',
    'items' => [
        'q1'  => ['text' => 'How often do you have a drink containing alcohol?',
                  'options' => [0 => 'Never', 1 => 'Monthly or less', 2 => '2–4 times a month', 3 => '2–3 times a week', 4 => '4 or more times a week']],
        'q2'  => ['text' => 'How many drinks containing alcohol do you have on a typical day when you are drinking?',
                  'options' => [0 => '1 or 2', 1 => '3 or 4', 2 => '5 or 6', 3 => '7, 8 or 9', 4 => '10 or more'],
                  'skip_if' => $skipIfNonDrinker],
        'q3'  => ['text' => 'How often do you have six or more drinks on one occasion?',
                  'options' => $frequency, 'skip_if' => $skipIfNonDrinker],
        'q4'  => ['text' => 'How often during the last year have you found that you were not able to stop drinking once you had started?',
                  'options' => $frequency, 'skip_if' => $skipIfNonDrinker],
        'q5'  => ['text' => 'How often during the last year have you failed to do what was normally expected of you because of drinking?',
                  'options' => $frequency, 'skip_if' => $skipIfNonDrinker],
        'q6'  => ['text' => 'How often during the last year have you needed a first drink in the morning to get yourself going after a heavy drinking session?',
                  'options' => $frequency, 'skip_if' => $skipIfNonDrinker],
        'q7'  => ['text' => 'How often during the last year have you had a feeling of guilt or remorse after drinking?',
                  'options' => $frequency, 'skip_if' => $skipIfNonDrinker],
        'q8'  => ['text' => 'How often during the last year have you been unable to remember what happened the night before because you had been drinking?',
                  'options' => $frequency, 'skip_if' => $skipIfNonDrinker],
        'q9'  => ['text' => 'Have you or someone else been injured as a result of your drinking?',
                  'options' => $yesNo],
        'q10' => ['text' => 'Has a relative, friend, doctor or other health worker been concerned about your drinking or suggested you cut down?',
                  'options' => $yesNo],
    ],
    'crisis_items' => [],
    'max_score' => 40,
    'bands' => [
        [0, 7,   'Low risk',            'Your answers suggest low-risk drinking or no drinking.'],
        [8, 15,  'Increasing risk',     'Your answers suggest a pattern of drinking that increases the risk of harm. Cutting down is advised.'],
        [16, 19, 'Higher risk',         'Your answers suggest drinking that is likely already causing harm. Talking to a doctor or counsellor about cutting down is recommended.'],
        [20, 40, 'Possible dependence', 'Your answers suggest possible alcohol dependence. Please speak with a doctor or addiction specialist; stopping suddenly after heavy drinking can be unsafe without medical advice.'],
    ],
    'source' => 'AUDIT: Babor, Higgins-Biddle, Saunders & Monteiro, The Alcohol Use Disorders Identification Test, World Health Organization, 2001.',
];
