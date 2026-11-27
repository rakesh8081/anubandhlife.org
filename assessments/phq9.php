<?php
// PHQ-9 — Patient Health Questionnaire (depression screening).
// Also used as the DSM-5 "Severity Measure for Depression — Adult".
// Developed by Drs. Robert L. Spitzer, Janet B.W. Williams, Kurt Kroenke and colleagues,
// with an educational grant from Pfizer Inc. No permission required to reproduce,
// translate, display or distribute.
return [
    'id'           => 'phq9',
    'title'        => 'PHQ-9 Depression Screening',
    'summary'      => 'A 9-question check of how often low mood and related problems have bothered you over the last two weeks.',
    'minutes'      => 3,
    'instructions' => 'Over the last 2 weeks, how often have you been bothered by any of the following problems?',
    'options'      => [
        0 => 'Not at all',
        1 => 'Several days',
        2 => 'More than half the days',
        3 => 'Nearly every day',
    ],
    'items' => [
        'q1' => 'Little interest or pleasure in doing things',
        'q2' => 'Feeling down, depressed, or hopeless',
        'q3' => 'Trouble falling or staying asleep, or sleeping too much',
        'q4' => 'Feeling tired or having little energy',
        'q5' => 'Poor appetite or overeating',
        'q6' => 'Feeling bad about yourself — or that you are a failure or have let yourself or your family down',
        'q7' => 'Trouble concentrating on things, such as reading the newspaper or watching television',
        'q8' => 'Moving or speaking so slowly that other people could have noticed? Or the opposite — being so fidgety or restless that you have been moving around a lot more than usual',
        'q9' => 'Thoughts that you would be better off dead or of hurting yourself in some way',
    ],
    // Any answer above 0 on these items shows crisis support immediately
    'crisis_items' => ['q9'],
    // Asked after the scored items; recorded but not scored
    'extra' => [
        'difficulty' => [
            'text'    => 'If you are bothered by any of these problems, how difficult have they made it for you to do your work, take care of things at home, or get along with other people?',
            'options' => ['Not difficult at all', 'Somewhat difficult', 'Very difficult', 'Extremely difficult'],
        ],
    ],
    'max_score' => 27,
    'bands' => [
        [0, 4,   'Minimal',           'Your answers suggest minimal depressive symptoms.'],
        [5, 9,   'Mild',              'Your answers suggest mild depressive symptoms. Keep an eye on how you feel, and consider talking to someone you trust or a professional if things do not improve.'],
        [10, 14, 'Moderate',          'Your answers suggest moderate depressive symptoms. Speaking with a qualified mental health professional is recommended.'],
        [15, 19, 'Moderately severe', 'Your answers suggest moderately severe depressive symptoms. Please consider reaching out to a qualified mental health professional soon.'],
        [20, 27, 'Severe',            'Your answers suggest severe depressive symptoms. Please reach out to a qualified mental health professional as soon as possible.'],
    ],
    'source' => 'PHQ-9 developed by Drs. Robert L. Spitzer, Janet B.W. Williams, Kurt Kroenke and colleagues, with an educational grant from Pfizer Inc. No permission required to reproduce, translate, display or distribute.',
];
