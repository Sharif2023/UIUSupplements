// Additional function for updating session status
async function updateSessionStatus(sessionId, status) {
    try {
        const response = await fetch('api/admin_sessions.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                session_id: sessionId,
                status: status
            })
        });
        const data = await response.json();

        if (data.success) {
            showToast(`Session ${status.toLowerCase()} successfully`, 'success');
            loadSessions();
        } else {
            showToast(data.error || 'Failed to update session', 'error');
        }
    } catch (error) {
        console.error('Error updating session:', error);
        showToast('Failed to update session', 'error');
    }
}
