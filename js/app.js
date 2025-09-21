// ============================================
// APLICACIÓN WEB PEÑA DE FÚTBOL - TEMPORADA 2026
// Sistema de gestión de estadísticas de jugadores
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    
    let players = [];
    let isLoggedIn = false;
    let selectedPlayerId = null;
    
    // Elementos del DOM
    const offensiveTableBody = document.querySelector('#offensiveTable tbody');
    const penaltyTableBody = document.querySelector('#penaltyTable tbody');
    const refreshBtn = document.getElementById('refreshBtn');
    const loginForm = document.getElementById('loginForm');
    const loginSection = document.getElementById('loginSection');
    const adminPanel = document.getElementById('adminPanel');
    const playerSelect = document.getElementById('playerSelect');
    const playerForm = document.getElementById('playerForm');
    const resetStatsBtn = document.getElementById('resetStatsBtn');
    const adminMessages = document.getElementById('adminMessages');
    const loginError = document.getElementById('loginError');
    const newPlayerName = document.getElementById('newPlayerName');
    const logoutBtn = document.getElementById('logoutBtn');
    const clearFormBtn = document.getElementById('clearFormBtn');
    const exportDataBtn = document.getElementById('exportDataBtn');
    const lastUpdateElement = document.getElementById('lastUpdate');
    const totalPlayersElement = document.getElementById('totalPlayers');
    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalConfirm = document.getElementById('modalConfirm');
    const modalCancel = document.getElementById('modalCancel');
    const modalClose = document.getElementById('modalClose');
    const toastContainer = document.getElementById('toastContainer');
    
    // ============================================
    // FUNCIONES DE UTILIDAD
    // ============================================
    
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <p>${message}</p>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.parentElement.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }
    
    function showModal(title, message, onConfirm) {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modal.style.display = 'flex';
        
        // Set up confirm handler
        modalConfirm.onclick = () => {
            modal.style.display = 'none';
            onConfirm();
        };
    }
    
    function hideModal() {
        modal.style.display = 'none';
    }
    
    function updateLastUpdate() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        lastUpdateElement.textContent = `Última actualización: ${timeStr}`;
    }
    
    function calculateOffensivePoints(goals, assists) {
        return (goals * 3) + (assists * 2);
    }
    
    function calculatePenaltyPoints(yellows, reds) {
        return (yellows * -1) + (reds * -3);
    }
    
    function showAdminMessage(message, type = 'success') {
        const messageElement = document.createElement('div');
        messageElement.className = `message ${type}`;
        messageElement.textContent = message;
        
        adminMessages.appendChild(messageElement);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (messageElement.parentElement) {
                messageElement.parentElement.removeChild(messageElement);
            }
        }, 5000);
    }
    
    function clearForm() {
        playerForm.matchesPlayed.value = '';
        playerForm.goals.value = '';
        playerForm.assists.value = '';
        playerForm.yellows.value = '';
        playerForm.reds.value = '';
        newPlayerName.value = '';
        playerSelect.selectedIndex = 0;
        selectedPlayerId = null;
    }
    
    // ============================================
    // FUNCIONES DE API
    // ============================================
    
    async function apiCall(endpoint, options = {}) {
        try {
            const response = await fetch(endpoint, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || `HTTP error! status: ${response.status}`);
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    async function loadPlayers() {
        try {
            offensiveTableBody.innerHTML = '<tr class="loading-row"><td colspan="5"><div class="loading-spinner"></div>Cargando estadísticas...</td></tr>';
            penaltyTableBody.innerHTML = '<tr class="loading-row"><td colspan="5"><div class="loading-spinner"></div>Cargando sanciones...</td></tr>';
            
            const data = await apiCall('api/players.php?action=list');
            
            if (data.success) {
                players = data.players;
                updateTables();
                updatePlayerSelect();
                updateLastUpdate();
                
                // Update total players count
                if (totalPlayersElement) {
                    totalPlayersElement.textContent = players.length;
                }
                
                showToast(`Datos actualizados. ${players.length} jugadores cargados.`, 'success');
            } else {
                throw new Error(data.error || 'Error desconocido');
            }
        } catch (error) {
            console.error('Error loading players:', error);
            offensiveTableBody.innerHTML = `<tr><td colspan="5" class="error-message">Error al cargar datos: ${error.message}</td></tr>`;
            penaltyTableBody.innerHTML = `<tr><td colspan="5" class="error-message">Error al cargar datos: ${error.message}</td></tr>`;
            showToast('Error al cargar los datos de jugadores', 'error');
        }
    }
    
    async function loginUser(username, password) {
        try {
            const data = await apiCall('api/login.php', {
                method: 'POST',
                body: JSON.stringify({ username, password })
            });
            
            if (data.success) {
                isLoggedIn = true;
                loginSection.style.display = 'none';
                adminPanel.style.display = 'block';
                loginError.style.display = 'none';
                
                showToast('Inicio de sesión exitoso', 'success');
                showAdminMessage('Bienvenido al panel de administración', 'success');
                
                // Load fresh data
                await loadPlayers();
            } else {
                throw new Error(data.error || 'Error de autenticación');
            }
        } catch (error) {
            console.error('Login error:', error);
            loginError.style.display = 'block';
            loginError.textContent = error.message;
            showToast('Error en el inicio de sesión', 'error');
        }
    }
    
    async function logoutUser() {
        try {
            await apiCall('api/login.php', {
                method: 'DELETE'
            });
            
            isLoggedIn = false;
            adminPanel.style.display = 'none';
            loginSection.style.display = 'block';
            clearForm();
            
            showToast('Sesión cerrada correctamente', 'info');
        } catch (error) {
            console.error('Logout error:', error);
            // Even if logout fails, hide admin panel
            isLoggedIn = false;
            adminPanel.style.display = 'none';
            loginSection.style.display = 'block';
        }
    }
    
    async function savePlayerData(playerId, playerData) {
        try {
            const endpoint = playerId ? 'api/players.php?action=update' : 'api/players.php?action=create';
            const data = await apiCall(endpoint, {
                method: 'POST',
                body: JSON.stringify(playerId ? { id: playerId, ...playerData } : playerData)
            });
            
            if (data.success) {
                const action = playerId ? 'actualizado' : 'creado';
                showAdminMessage(`Jugador ${action} exitosamente`, 'success');
                showToast(`Jugador ${action}`, 'success');
                
                await loadPlayers();
                clearForm();
            } else {
                throw new Error(data.error || 'Error al guardar');
            }
        } catch (error) {
            console.error('Save error:', error);
            showAdminMessage(error.message, 'error');
            showToast('Error al guardar los datos', 'error');
        }
    }
    
    async function resetAllStats() {
        try {
            const data = await apiCall('api/players.php?action=reset', {
                method: 'POST'
            });
            
            if (data.success) {
                showAdminMessage('Todas las estadísticas han sido reiniciadas', 'success');
                showToast('Estadísticas reiniciadas', 'success');
                
                await loadPlayers();
                clearForm();
            } else {
                throw new Error(data.error || 'Error al reiniciar');
            }
        } catch (error) {
            console.error('Reset error:', error);
            showAdminMessage(error.message, 'error');
            showToast('Error al reiniciar estadísticas', 'error');
        }
    }
    
    // ============================================
    // FUNCIONES DE ACTUALIZACIÓN DE UI
    // ============================================
    
    function updateTables() {
        updateOffensiveTable();
        updatePenaltyTable();
    }
    
    function updateOffensiveTable() {
        // Sort by offensive points (desc), then by goals (desc), then by assists (desc)
        const sortedPlayers = [...players].sort((a, b) => {
            const pointsA = calculateOffensivePoints(a.goals, a.assists);
            const pointsB = calculateOffensivePoints(b.goals, b.assists);
            
            if (pointsB !== pointsA) return pointsB - pointsA;
            if (b.goals !== a.goals) return b.goals - a.goals;
            return b.assists - a.assists;
        });
        
        offensiveTableBody.innerHTML = '';
        
        sortedPlayers.forEach((player, index) => {
            const points = calculateOffensivePoints(player.goals, player.assists);
            
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td class="text-left">${player.name}</td>
                <td>${player.matches_played || 0}</td>
                <td>${player.goals}</td>
                <td>${player.assists}</td>
                <td class="points-col font-weight-bold">${points}</td>
            `;
            
            offensiveTableBody.appendChild(row);
        });
        
        // If no players have points, show message
        if (sortedPlayers.every(p => calculateOffensivePoints(p.goals, p.assists) === 0)) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="5" class="text-center" style="padding: 2rem; color: #6c757d; font-style: italic;">Aún no hay estadísticas ofensivas registradas</td>';
            offensiveTableBody.appendChild(row);
        }
    }
    
    function updatePenaltyTable() {
        // Sort by total cards (desc: most carded first), then by reds (desc), then by name (asc)
        const sortedPlayers = [...players].sort((a, b) => {
            const totalCardsA = a.yellows + a.reds;
            const totalCardsB = b.yellows + b.reds;
            
            if (totalCardsB !== totalCardsA) return totalCardsB - totalCardsA;
            if (b.reds !== a.reds) return b.reds - a.reds;
            return a.name.localeCompare(b.name);
        });
        
        penaltyTableBody.innerHTML = '';
        
        sortedPlayers.forEach((player, index) => {
            
            const row = document.createElement('tr');
            
            // Add special class for players with high penalties
            if (player.reds > 0 || player.yellows >= 3) {
                row.classList.add('high-penalties');
            }
            
            row.innerHTML = `
                <td class="text-left">${player.name}</td>
                <td>${player.matches_played || 0}</td>
                <td>${player.yellows}</td>
                <td>${player.reds}</td>
            `;
            
            penaltyTableBody.appendChild(row);
        });
        
        // If no players have penalties, show message
        if (sortedPlayers.every(p => p.yellows === 0 && p.reds === 0)) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="5" class="text-center" style="padding: 2rem; color: #6c757d; font-style: italic;">¡Perfecto! No hay sanciones registradas</td>';
            penaltyTableBody.appendChild(row);
        }
    }
    
    function updatePlayerSelect() {
        playerSelect.innerHTML = '<option value="">Selecciona un jugador...</option>';
        
        // Sort players by name for better UX
        const sortedPlayers = [...players].sort((a, b) => a.name.localeCompare(b.name));
        
        sortedPlayers.forEach(player => {
            const option = document.createElement('option');
            option.value = player.id;
            option.textContent = player.name;
            playerSelect.appendChild(option);
        });
    }
    
    function loadPlayerDataToForm(player) {
        if (!player) return;
        
        playerForm.matchesPlayed.value = player.matches_played || 0;
        playerForm.goals.value = player.goals;
        playerForm.assists.value = player.assists;
        playerForm.yellows.value = player.yellows;
        playerForm.reds.value = player.reds;
        
        selectedPlayerId = player.id;
        newPlayerName.value = '';
    }
    
    // ============================================
    // FUNCIÓN DE EXPORTACIÓN
    // ============================================
    
    function exportData() {
        const exportData = {
            timestamp: new Date().toISOString(),
            season: '2026',
            referee: 'Juanxus',
            totalPlayers: players.length,
            players: players.map(player => ({
                ...player,
                offensivePoints: calculateOffensivePoints(player.goals, player.assists),
                penaltyPoints: calculatePenaltyPoints(player.yellows, player.reds)
            }))
        };
        
        const dataStr = JSON.stringify(exportData, null, 2);
        const dataBlob = new Blob([dataStr], {type: 'application/json'});
        const url = URL.createObjectURL(dataBlob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = `pena-futbol-temporada-2026-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
        showToast('Datos exportados correctamente', 'success');
    }
    
    // ============================================
    // EVENT LISTENERS
    // ============================================
    
    // Refresh button
    refreshBtn.addEventListener('click', loadPlayers);
    
    // Login form
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const username = loginForm.username.value.trim();
        const password = loginForm.password.value;
        
        if (!username || !password) {
            loginError.style.display = 'block';
            loginError.textContent = 'Usuario y contraseña son requeridos';
            return;
        }
        
        await loginUser(username, password);
    });
    
    // Logout button
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logoutUser);
    }
    
    // Player selection
    playerSelect.addEventListener('change', (e) => {
        const playerId = e.target.value;
        if (playerId) {
            const player = players.find(p => p.id == playerId);
            loadPlayerDataToForm(player);
        } else {
            clearForm();
        }
    });
    
    // Player form submission
    playerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const isCreatingNew = newPlayerName.value.trim();
        
        if (isCreatingNew) {
            // Creating new player
            const playerData = {
                name: newPlayerName.value.trim(),
                matches_played: parseInt(playerForm.matchesPlayed.value) || 0,
                goals: parseInt(playerForm.goals.value) || 0,
                assists: parseInt(playerForm.assists.value) || 0,
                yellows: parseInt(playerForm.yellows.value) || 0,
                reds: parseInt(playerForm.reds.value) || 0
            };
            
            await savePlayerData(null, playerData);
        } else if (selectedPlayerId) {
            // Updating existing player
            const playerData = {
                matches_played: parseInt(playerForm.matchesPlayed.value) || 0,
                goals: parseInt(playerForm.goals.value) || 0,
                assists: parseInt(playerForm.assists.value) || 0,
                yellows: parseInt(playerForm.yellows.value) || 0,
                reds: parseInt(playerForm.reds.value) || 0
            };
            
            await savePlayerData(selectedPlayerId, playerData);
        } else {
            showAdminMessage('Debes seleccionar un jugador o escribir un nombre para crear uno nuevo', 'error');
        }
    });
    
    // Clear form button
    if (clearFormBtn) {
        clearFormBtn.addEventListener('click', clearForm);
    }
    
    // Reset statistics button
    resetStatsBtn.addEventListener('click', () => {
        showModal(
            'Reiniciar Estadísticas',
            '¿Estás seguro de que quieres reiniciar TODAS las estadísticas de TODOS los jugadores? Esta acción no se puede deshacer.',
            resetAllStats
        );
    });
    
    // Export data button
    if (exportDataBtn) {
        exportDataBtn.addEventListener('click', exportData);
    }
    
    // Modal event listeners
    modalCancel.addEventListener('click', hideModal);
    modalClose.addEventListener('click', hideModal);
    
    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            hideModal();
        }
    });
    
    // Handle new player name input
    newPlayerName.addEventListener('input', (e) => {
        if (e.target.value.trim()) {
            playerSelect.selectedIndex = 0;
            selectedPlayerId = null;
            // Clear form values when creating new player
            playerForm.goals.value = '';
            playerForm.assists.value = '';
            playerForm.yellows.value = '';
            playerForm.reds.value = '';
        }
    });
    
    // Form validation - prevent negative numbers
    ['matchesPlayed', 'goals', 'assists', 'yellows', 'reds'].forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('change', (e) => {
                if (parseInt(e.target.value) < 0) {
                    e.target.value = 0;
                }
            });
        }
    });
    
    // ============================================
    // KEYBOARD SHORTCUTS
    // ============================================
    
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + R to refresh data
        if ((e.ctrlKey || e.metaKey) && e.key === 'r' && isLoggedIn) {
            e.preventDefault();
            loadPlayers();
        }
        
        // Escape to close modal
        if (e.key === 'Escape') {
            hideModal();
        }
    });
    
    // ============================================
    // INICIALIZACIÓN
    // ============================================
    
    // Load initial data
    loadPlayers();
    
    // Auto-refresh every 5 minutes if not in admin mode
    setInterval(() => {
        if (!isLoggedIn) {
            loadPlayers();
        }
    }, 300000); // 5 minutes
    
    console.log('🎯 Aplicación Peña de Fútbol - Temporada 2026 iniciada correctamente');
    console.log('👥 Total de jugadores cargados:', players.length);
    console.log('⚽ Sistema listo para gestionar estadísticas');
});

// ============================================
// FUNCIONES GLOBALES DE UTILIDAD
// ============================================

// Función para debug en consola
window.debugApp = function() {
    console.log('=== DEBUG PEÑA DE FÚTBOL ===');
    console.log('Players:', window.players || 'No disponible');
    console.log('Logged in:', window.isLoggedIn || false);
    console.log('API endpoints:', {
        list: 'api/players.php?action=list',
        login: 'api/login.php',
        create: 'api/players.php?action=create',
        update: 'api/players.php?action=update',
        reset: 'api/players.php?action=reset'
    });
};