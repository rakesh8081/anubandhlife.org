<?php
// GAD-7 — Generalized Anxiety Disorder 7-item scale (anxiety screening).
// Widely used free anxiety screen (not itself one of the APA's DSM-5 severity measures).
// Developed by Drs. Robert L. Spitzer, Janet B.W. Williams, Kurt Kroenke and colleagues,
// with an educational grant from Pfizer Inc. No permission required to reproduce,
// translate, display or distribute.
return [
    'id'           => 'gad7',
    'title'        => 'GAD-7 Anxiety Screening',
    'summary'      => 'A 7-question check of how often worry and anxiety have bothered you over the last two weeks.',
    'minutes'      => 2,
    'instructions' => 'Over the last 2 weeks, how often have you been bothered by the following problems?',
    'options'      => [
        0 => 'Not at all',
        1 => 'Several days',
        2 => 'More than half the days',
        3 => 'Nearly every day',
    ],
    'items' => [
        'q1' => 'Feeling nervous, anxious, or on edge',
        'q2' => 'Not being able to stop or control worrying',
        'q3' => 'Worrying too much about different things',
        'q4' => 'Trouble relaxing',
        'q5' => 'Being so restless that it is hard to sit still',
        'q6' => 'Becoming easily annoyed or irritable',
        'q7' => 'Feeling afraid, as if something awful might happen',
    ],
    'crisis_items' => [],
    'extra' => [
        'difficulty' => [
            'text'    => 'If you are bothered by any of these problems, how difficult have they made it for you to do your work, take care of things at home, or get along with other people?',
            'options' => ['Not difficult at all', 'Somewhat difficult', 'Very difficult', 'Extremely difficult'],
        ],
    ],
    'max_score' => 21,
    'bands' => [
        [0, 4,   'Minimal',  'Your answers suggest minimal anxiety symptoms.'],
        [5, 9,   'Mild',     'Your answers suggest mild anxiety symptoms. Keep an eye on how you feel, and consider talking to someone you trust or a professional if things do not improve.'],
        [10, 14, 'Moderate', 'Your answers suggest moderate anxiety symptoms. Speaking with a qualified mental health professional is recommended.'],
        [15, 21, 'Severe',   'Your answers suggest severe anxiety symptoms. Please reach out to a qualified mental health professional as soon as possible.'],
    ],
    'source' => 'GAD-7 developed by Drs. Robert L. Spitzer, Janet B.W. Williams, Kurt Kroenke and colleagues, with an educational grant from Pfizer Inc. No permission required to reproduce, translate, display or distribute.',
];
