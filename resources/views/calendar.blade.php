@extends('layouts.app')

@section('title', 'Calendar')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-calendar-alt"></i> Calendar</h1>
    @auth
        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <button class="btn btn-primary" onclick="openAddMilestoneModal()">
                <i class="fas fa-plus"></i> Add Event
            </button>
        @endif
    @endauth
</div>

<!-- Calendar Controls -->
<div class="calendar-controls">
    <button class="btn btn-secondary" onclick="previousMonth()">
        <i class="fas fa-chevron-left"></i>
    </button>
    <h2 id="current-month">Loading...</h2>
    <button class="btn btn-secondary" onclick="nextMonth()">
        <i class="fas fa-chevron-right"></i>
    </button>
    <button class="btn btn-secondary" onclick="goToToday()">Today</button>
</div>

<!-- Calendar Grid -->
<div class="calendar-container">
    <div class="calendar-weekdays">
        <div class="weekday">Sun</div>
        <div class="weekday">Mon</div>
        <div class="weekday">Tue</div>
        <div class="weekday">Wed</div>
        <div class="weekday">Thu</div>
        <div class="weekday">Fri</div>
        <div class="weekday">Sat</div>
    </div>
    <div class="calendar-grid" id="calendar-grid">
        <!-- Calendar days will be populated here -->
    </div>
</div>

<!-- Event Details Modal -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Events</h3>
            <span class="modal-close" onclick="closeEventModal()">&times;</span>
        </div>
        <div class="modal-body" id="modal-events">
            <!-- Events list will be shown here -->
        </div>
    </div>
</div>

<!-- Add Milestone Modal -->
<div id="addMilestoneModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Event</h3>
            <span class="modal-close" onclick="closeAddMilestoneModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="milestoneForm">
                <div class="form-group">
                    <label>Project</label>
                    <select id="milestone-project" required class="form-input">
                        <option value="">Select Project</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Event Title</label>
                    <input type="text" id="milestone-title" required class="form-input">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="milestone-description" class="form-input" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select id="milestone-type" class="form-input">
                        <option value="milestone">Milestone</option>
                        <option value="deadline">Deadline</option>
                        <option value="meeting">Meeting</option>
                        <option value="review">Review</option>
                        <option value="launch">Launch</option>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="milestone-date" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Time (Optional)</label>
                        <input type="time" id="milestone-time" class="form-input">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Create Event
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.calendar-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.calendar-controls h2 {
    min-width: 200px;
    text-align: center;
    margin: 0;
}

.calendar-container {
    background: var(--bg-card);
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid var(--border);
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    margin-bottom: 1px;
}

.weekday {
    text-align: center;
    padding: 1rem;
    font-weight: 600;
    color: var(--text-secondary);
    background: var(--bg-card-hover);
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: var(--border);
}

.calendar-day {
    background: var(--bg-dark);
    min-height: 100px;
    padding: 0.5rem;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.calendar-day:hover {
    background: var(--bg-card-hover);
}

.calendar-day.other-month {
    opacity: 0.3;
}

.calendar-day.today {
    background: rgba(79, 127, 255, 0.1);
    border: 2px solid var(--primary-blue);
}

.day-number {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.day-events {
    font-size: 0.75rem;
}

.event-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 4px;
}

.event-item {
    padding: 0.25rem 0.5rem;
    margin-bottom: 0.25rem;
    border-radius: 4px;
    font-size: 0.75rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: var(--bg-card);
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
}

.modal-header h3 {
    margin: 0;
}

.modal-close {
    font-size: 2rem;
    cursor: pointer;
    color: var(--text-muted);
}

.modal-close:hover {
    color: var(--text-primary);
}

.modal-body {
    padding: 1.5rem;
}

.event-detail {
    padding: 1rem;
    margin-bottom: 1rem;
    background: var(--bg-dark);
    border-radius: 8px;
    border-left: 4px solid;
}

.event-detail h4 {
    margin-bottom: 0.5rem;
}

.event-detail p {
    margin: 0.25rem 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .calendar-day {
        min-height: 60px;
        padding: 0.25rem;
    }
    
    .day-number {
        font-size: 0.85rem;
    }
    
    .event-item {
        font-size: 0.65rem;
    }
    
    .modal-content {
        width: 95%;
        max-width: none;
    }
}

/* Enhanced Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideDown {
    from { 
        opacity: 0;
        transform: translateY(-50px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

.modal.show {
    display: flex !important;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: var(--bg-card);
    border-radius: 16px;
    width: 90%;
    max-width: 550px;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    border: 1px solid var(--border);
    animation: slideDown 0.3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
    background: var(--bg-card-hover);
}

.modal-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
}

.modal-close {
    font-size: 2rem;
    cursor: pointer;
    color: var(--text-muted);
    transition: all 0.2s;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.modal-close:hover {
    color: var(--text-primary);
    background: var(--bg-dark);
}

.modal-body {
    padding: 2rem;
}

/* Form Styling */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-primary);
    font-size: 0.95rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--bg-dark);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 0.95rem;
    transition: all 0.3s;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px rgba(79, 127, 255, 0.1);
    background: var(--bg-card);
}

.form-input::placeholder {
    color: var(--text-muted);
}

select.form-input {
    cursor: pointer;
}

textarea.form-input {
    resize: vertical;
    min-height: 80px;
}

/* Event Detail Cards */
.event-detail {
    padding: 1.25rem;
    margin-bottom: 1rem;
    background: var(--bg-dark);
    border-radius: 10px;
    border-left: 4px solid;
    transition: all 0.2s;
}

.event-detail:hover {
    transform: translateX(4px);
    background: var(--bg-card-hover);
}

.event-detail h4 {
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
    color: var(--text-primary);
}

.event-detail p {
    margin: 0.4rem 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.event-detail p i {
    width: 16px;
    opacity: 0.7;
}

</style>
@endpush

@push('scripts')
<script>
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
let allEvents = [];

$(document).ready(function() {
    loadProjects();
    loadEvents();
    renderCalendar();
});

function loadProjects() {
    $.get('/api/v1/projects')
        .done(response => {
            const projects = response.data || response;
            let options = '<option value="">Select Project</option>';
            projects.forEach(project => {
                options += `<option value="${project.id}">${project.name}</option>`;
            });
            $('#milestone-project').html(options);
        });
}

function loadEvents() {
    $.get(`/api/v1/calendar/month/${currentYear}/${currentMonth + 1}`)
        .done(response => {
            allEvents = response.events || {};
            renderCalendar();
        })
        .fail(() => {
            showToast('Failed to load events', 'error');
        });
}

function renderCalendar() {
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    const daysInPrevMonth = new Date(currentYear, currentMonth, 0).getDate();
    
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                        'July', 'August', 'September', 'October', 'November', 'December'];
    
    $('#current-month').text(`${monthNames[currentMonth]} ${currentYear}`);
    
    let html = '';
    
    // Previous month days
    for (let i = firstDay - 1; i >= 0; i--) {
        const day = daysInPrevMonth - i;
        html += `<div class="calendar-day other-month">
            <div class="day-number">${day}</div>
        </div>`;
    }
    
    // Current month days
    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const date = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = today.getDate() === day && today.getMonth() === currentMonth && today.getFullYear() === currentYear;
        const dayEvents = allEvents[date] || [];
        
        html += `<div class="calendar-day ${isToday ? 'today' : ''}" onclick="showDayEvents('${date}')">
            <div class="day-number">${day}</div>
            <div class="day-events">`;
        
        dayEvents.slice(0, 3).forEach(event => {
            html += `<div class="event-item" style="background: ${event.color}15; border-left: 3px solid ${event.color};">
                ${event.title}
            </div>`;
        });
        
        if (dayEvents.length > 3) {
            html += `<div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">
                +${dayEvents.length - 3} more
            </div>`;
        }
        
        html += `</div></div>`;
    }
    
    // Next month days
    const remainingCells = 42 - (firstDay + daysInMonth);
    for (let day = 1; day <= remainingCells; day++) {
        html += `<div class="calendar-day other-month">
            <div class="day-number">${day}</div>
        </div>`;
    }
    
    $('#calendar-grid').html(html);
}

function previousMonth() {
    currentMonth--;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    loadEvents();
}

function nextMonth() {
    currentMonth++;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    loadEvents();
}

function goToToday() {
    const today = new Date();
    currentMonth = today.getMonth();
    currentYear = today.getFullYear();
    loadEvents();
}

function showDayEvents(date) {
    const events = allEvents[date] || [];
    $('#modal-title').text(`Events on ${new Date(date).toLocaleDateString()}`);
    
    let html = '';
    if (events.length === 0) {
        html = '<p style="color: var(--text-muted); text-align: center;">No events on this date</p>';
    } else {
        events.forEach(event => {
            html += `<div class="event-detail" style="border-left-color: ${event.color};">
                <h4>${event.title}</h4>
                <p><i class="fas fa-project-diagram"></i> ${event.project}</p>
                <p><i class="fas fa-tag"></i> ${event.type}</p>
            </div>`;
        });
    }
    
    $('#modal-events').html(html);
    $('#eventModal').addClass('show');
}

function closeEventModal() {
    $('#eventModal').removeClass('show');
}

function openAddMilestoneModal() {
    $('#addMilestoneModal').addClass('show');
    // Set today as default
    const today = new Date().toISOString().split('T')[0];
    $('#milestone-date').val(today);
}

function closeAddMilestoneModal() {
    $('#addMilestoneModal').removeClass('show');
    $('#milestoneForm')[0].reset();
}

$('#milestoneForm').submit(function(e) {
    e.preventDefault();
    
    const data = {
        project_id: $('#milestone-project').val(),
        title: $('#milestone-title').val(),
        description: $('#milestone-description').val(),
        type: $('#milestone-type').val(),
        scheduled_date: $('#milestone-date').val(),
        scheduled_time: $('#milestone-time').val() || null,
    };
    
    $.post('/api/v1/milestones', data)
        .done(() => {
            showToast('Event created successfully!', 'success');
            closeAddMilestoneModal();
            loadEvents();
        })
        .fail(xhr => {
            showToast(xhr.responseJSON?.message || 'Failed to create event', 'error');
        });
});

// Close modal on outside click
$('.modal').click(function(e) {
    if (e.target === this) {
        $(this).removeClass('show');
    }
});
</script>
@endpush
