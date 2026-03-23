const awarenessQuestions = [
    // PART 1: Baseline Understanding
    { id: "q1", type: "scale", text: "How would you rate your overall understanding of psychological disorders? (1 = Very Poor to 5 = Excellent)", min: 1, max: 5 },
    { id: "q2", type: "radio", text: "How common do you believe psychological disorders are among students your age?", options: ["Very Rare", "Somewhat Rare", "Common", "Very Common"] },
    { id: "q3", type: "checkbox", text: "Which of the following conditions are you familiar with?", options: ["Depression", "Anxiety", "Schizophrenia", "Bipolar Disorder", "OCD", "Autism Spectrum", "None"] },
    { id: "q4", type: "radio", text: "Where do you get most of your information regarding mental health?", options: ["Social Media", "School/College Curriculum", "Friends/Family", "News/Articles", "Professional Sources"] },
    { id: "q5", type: "text", text: "In your own words, how would you describe the main difference between everyday stress and a clinical psychological disorder?" },
    
    // PART 2: Misconceptions
    { id: "q6", type: "scale", text: "Psychological disorders are medical conditions that require treatment, similar to physical illnesses. (1 = Strongly Disagree to 5 = Strongly Agree)", min: 1, max: 5 },
    { id: "q7", type: "scale", text: "A person can overcome a psychological disorder simply by having stronger willpower and positive thinking. (1 = Strongly Disagree to 5 = Strongly Agree)", min: 1, max: 5 },
    { id: "q8", type: "radio", text: "People diagnosed with psychological disorders are generally more unpredictable or dangerous than the average person.", options: ["True", "False", "Not Sure"] },
    { id: "q9", type: "radio", text: "Seeking therapy or counseling is only necessary for people having severe mental breakdowns.", options: ["True", "False", "Not Sure"] },
    { id: "q10", type: "text", text: "What do you believe is the biggest reason students hesitate to seek help or talk openly about their mental health?" },
    
    // PART 3: Social Attitudes
    { id: "q11", type: "scale", text: "I would feel comfortable studying or working closely on a project with someone who has a diagnosed psychological disorder. (1 = Strongly Disagree to 5 = Strongly Agree)", min: 1, max: 5 },
    { id: "q12", type: "radio", text: "If a close friend confided in you that they were experiencing a psychological disorder, what would be your primary reaction?", options: ["Just listen", "Advise them to snap out of it", "Suggest a professional", "Distance yourself"] },
    { id: "q13", type: "radio", text: "Seeking professional help for mental health is often seen as a sign of weakness by society.", options: ["True", "False", "Not Sure"] },
    { id: "q14", type: "scale", text: "My school/college provides enough education and support resources regarding psychological disorders. (1 = Strongly Disagree to 5 = Strongly Agree)", min: 1, max: 5 },
    { id: "q15", type: "text", text: "Based on your experience, what is one specific thing your school or college could do to improve students' understanding of psychological disorders?" }
];