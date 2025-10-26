/**
 * Progress Tracker JavaScript
 * Tracks student progress and updates database via AJAX
 */

const API_BASE = '/handlers/';

/**
 * Update lesson progress
 */
function updateProgress(lessonId, timeSpent) {
    fetch(`${API_BASE}update_progress.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `lesson_id=${lessonId}&time_spent=${timeSpent}`
    }).catch(err => {
        console.error('Failed to update progress:', err);
    });
}

/**
 * Mark lesson as completed
 */
function markLessonCompleted(lessonId) {
    fetch(`${API_BASE}update_progress.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `lesson_id=${lessonId}&status=completed`
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Lesson marked as completed');
            // Update UI
            const lessonElement = document.querySelector(`[data-lesson-id="${lessonId}"]`);
            if (lessonElement) {
                lessonElement.classList.add('completed');
            }
        }
    }).catch(err => {
        console.error('Failed to mark lesson as completed:', err);
    });
}

/**
 * Track video watch time
 */
function trackVideoTime(videoElement, lessonId) {
    let totalTime = 0;
    let startTime = Date.now();
    
    const updateInterval = setInterval(() => {
        if (!videoElement.paused) {
            const currentTime = Math.floor(videoElement.currentTime);
            updateProgress(lessonId, currentTime);
            totalTime = currentTime;
        }
    }, 5000); // Update every 5 seconds
    
    videoElement.addEventListener('ended', () => {
        clearInterval(updateInterval);
        markLessonCompleted(lessonId);
    });
    
    videoElement.addEventListener('pause', () => {
        updateProgress(lessonId, Math.floor(videoElement.currentTime));
    });
}

