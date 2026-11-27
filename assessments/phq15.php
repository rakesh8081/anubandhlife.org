<?php
// PHQ-15 — Patient Health Questionnaire somatic symptom scale.
// Used in DSM-5 as the Level 2 Somatic Symptom measure (adult).
// Developed by Drs. Robert L. Spitzer, Janet B.W. Williams, Kurt Kroenke and colleagues,
// with an educational grant from Pfizer Inc. No permission required to reproduce,
// translate, display or distribute.
return [
    'id'           => 'phq15',
    'order'        => 3,
    'title'        => 'PHQ-15 Physical Symptoms Screening',
    'summary'      => 'A 15-question check of how much common physical symptoms, such as pain, tiredness or dizziness, have bothered you over the last four weeks.',
    'minutes'      => 3,
    'instructions' => 'During the past 4 weeks, how much have you been bothered by any of the following problems?',
    'options'      => [
        0 => 'Not bothered at all',
        1 => 'Bothered a little',
        2 => 'Bothered a lot',
    ],
    'items' => [
        'q1'  => 'Stomach pain',
        'q2'  => 'Back pain',
        'q3'  => 'Pain in your arms, legs, or joints (knees, hips, etc.)',
        'q4'  => ['text' => 'Menstrual cramps or other problems with your periods', 'optional' => true,
                  'hint' => 'Only if this applies to you — otherwise leave it blank.'],
        'q5'  => 'Headaches',
        'q6'  => 'Chest pain',
        'q7'  => 'Dizziness',
        'q8'  => 'Fainting spells',
        'q9'  => 'Feeling your heart pound or race',
        'q10' => 'Shortness of breath',
        'q11' => 'Pain or problems during sexual intercourse',
        'q12' => 'Constipation, loose bowels, or diarrhea',
        'q13' => 'Nausea, gas, or indigestion',
        'q14' => 'Feeling tired or having low energy',
        'q15' => 'Trouble sleeping',
    ],
    'crisis_items' => [],
    'max_score' => 30,
    'bands' => [
        [0, 4,   'Minimal', 'Your answers suggest minimal physical symptom burden.'],
        [5, 9,   'Low',     'Your answers suggest a low level of physical symptoms.'],
        [10, 14, 'Medium',  'Your answers suggest a medium level of physical symptoms. Consider discussing them with a doctor, especially if they affect your daily life.'],
        [15, 30, 'High',    'Your answers suggest a high level of physical symptoms. Please discuss them with a doctor, who can check for physical causes and advise on next steps.'],
    ],
    'source' => 'PHQ-15 developed by Drs. Robert L. Spitzer, Janet B.W. Williams, Kurt Kroenke and colleagues, with an educational grant from Pfizer Inc. No permission required to reproduce, translate, display or distribute.',
];
