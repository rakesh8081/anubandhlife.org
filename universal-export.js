async function generateUniversalReport(mode) {
    const user = JSON.parse(localStorage.getItem('mindLabUser'));
    const reportElement = document.getElementById('printable-report');
    
    if (!user || !reportElement) {
        alert("Error: Report data is missing.");
        return;
    }

    // The Bulletproof PDF Configuration
    const pdfOptions = {
        margin:       0.5, 
        filename:     `MindLab_Profile_${user.name.replace(/\s+/g, '_')}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 2, 
            windowWidth: 800 
        },
        jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' },
        // THE FIX: This tells the engine to never split any element with the 'pdf-protect' class
        pagebreak:    { mode: 'css', avoid: ['.pdf-protect'] } 
    };

    if (mode === 'print') {
        html2pdf().set(pdfOptions).from(reportElement).save();
    } else if (mode === 'email') {
        alert(`Preparing to email results to ${user.email}. This may take a few seconds...`);
        const reportHTML = reportElement.innerHTML;

        try {
            const response = await fetch('mailer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    participantName: user.name,
                    participantEmail: user.email,
                    reportContent: reportHTML
                })
            });

            const result = await response.json();
            if (result.success) {
                alert("Success! Your profile has been sent to " + user.email);
            } else {
                alert("We encountered an issue sending the email: " + result.message);
            }
        } catch (error) {
            console.error("Mail Error:", error);
            alert("A network error occurred. Please try downloading the PDF instead.");
        }
    }
}