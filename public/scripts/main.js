const header = document.querySelector('h2');
console.log(header);
header.addEventListener('click', () => {
    header.style.color = 'green';
});

function inviteUser(projectId, userId, button) {
    button.disabled = true;
    button.textContent = 'Inviting...';

    const formData = new FormData();
    formData.append('project_id', projectId);
    formData.append('invitee_id', userId);

    fetch('/invite-user', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => { throw new Error(text) });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            button.textContent = 'Invited!';
            button.style.background = '#6c757d';
        } else {
            button.textContent = 'Error';
            button.disabled = false;
            alert(data.error || 'Failed to send invitation');
        }
    })
    .catch(error => {
        console.error('Error inviting user:', error);
        button.textContent = 'Error';
        button.disabled = false;
        alert('Failed to send invitation');
    });
}

// Animacje dla kart projektów
document.addEventListener('DOMContentLoaded', () => {
    const projectCards = document.querySelectorAll('.project-card');
    const taskItems = document.querySelectorAll('.task-item');

    // Animacja pojawiania się kart projektów
    projectCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Animacja pojawiania się zadań
    taskItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        setTimeout(() => {
            item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, index * 50);
    });

    // Efekt hover dla przycisków
    const buttons = document.querySelectorAll('button');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'scale(1.05)';
        });
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'scale(1)';
        });
    });

    // Searchbars — many per project
    document.querySelectorAll('.user-search').forEach(input => {
        let timeout;
        input.addEventListener('input', function() {
            const panel = this.parentElement;
            const results = panel.querySelector('.user-results');

            clearTimeout(timeout);
            const query = this.value.trim();

            if (query.length < 2) {
                results.innerHTML = '';
                return;
            }

            timeout = setTimeout(() => {
                fetch(`/search-users?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(users => {
                        results.innerHTML = '';
                        if (users.length === 0) {
                            results.innerHTML = '<p>No users found.</p>';
                            return;
                        }

                        users.forEach(user => {
                            const row = document.createElement('div');
                            row.classList.add('user-result');
                            row.innerHTML = `
                                <span>${user.email}</span>
                                <button class="invite-btn" data-project-id="${input.dataset.projectId}" data-user-id="${user.id}">
                                    Invite
                                </button>
                            `;
                            results.appendChild(row);
                        });
                        
                        
                        // Attach event listeners to new buttons
                        results.querySelectorAll('.invite-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                inviteUser(
                                    this.dataset.projectId,
                                    this.dataset.userId,
                                    this
                                );
                            });
                        });
                    });
            }, 300);
        });
    });

    // Toggle invite panels
    document.querySelectorAll('.open-invite').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.projectId;
            const panel = document.getElementById(`invite-panel-${id}`);
             if (!panel) return; // <- ważne!

            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        });
    });

    // Toggle edit project form
    const editBtn = document.getElementById('editProjectBtn');
    const editForm = document.getElementById('editProjectForm');
    const cancelBtn = document.getElementById('cancelEditBtn');

    if (editBtn && editForm && cancelBtn) {
        editBtn.addEventListener('click', function() {
            editForm.style.display = editForm.style.display === 'none' ? 'block' : 'none';
        });

        cancelBtn.addEventListener('click', function() {
            editForm.style.display = 'none';
        });
    }

});
