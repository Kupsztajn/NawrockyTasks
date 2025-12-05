document.addEventListener('DOMContentLoaded', () => {
    const taskInput = document.getElementById('taskTitle');
    const voiceBtn = document.getElementById('voiceBtn');

    if (!taskInput || !voiceBtn) return; // zabezpieczenie, jeśli formularz nie ma mikrofonu

    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        voiceBtn.disabled = true;
        voiceBtn.title = 'Twoja przeglądarka nie obsługuje rozpoznawania mowy';
        return;
    }

    const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
    recognition.lang = 'pl-PL';
    recognition.interimResults = false;

    voiceBtn.addEventListener('click', () => {
        recognition.start();
        voiceBtn.textContent = '🎙️...';
    });

    recognition.addEventListener('result', (event) => {
        const transcript = event.results[0][0].transcript;
        taskInput.value = transcript;
    });

    recognition.addEventListener('end', () => {
        voiceBtn.textContent = '🎤';
    });

    recognition.addEventListener('error', (event) => {
        console.error('Błąd rozpoznawania mowy:', event.error);
        voiceBtn.textContent = '🎤';
    });
});
