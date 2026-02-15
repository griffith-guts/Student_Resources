<?php
session_start();
require_once 'config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Planner — Student Resource System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Study Planner</div>
                <div class="topbar-subtitle">Generate a personalized study schedule for your exams</div>
            </div>
        </div>

        <div class="content-area">
            <div class="planner-grid">
                <!-- Input Form -->
                <div class="card">
                    <div class="card-title">🗓️ Schedule Setup</div>
                    <div class="card-subtitle">Enter your details to generate a study plan</div>

                    <div class="form-group">
                        <label for="subjects">Subjects (comma-separated)</label>
                        <textarea id="subjects" rows="4" 
                                  placeholder="e.g. Mathematics, Physics, Chemistry, English, Computer Science"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="exam_date">Exam Start Date</label>
                        <input type="date" id="exam_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>

                    <div class="form-group">
                        <label for="hours_per_day">Study Hours Per Day</label>
                        <select id="hours_per_day">
                            <?php for ($h = 1; $h <= 12; $h++): ?>
                                <option value="<?= $h ?>" <?= $h === 4 ? 'selected' : '' ?>><?= $h ?> hour<?= $h > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="break_day">Day Off per Week</label>
                        <select id="break_day">
                            <option value="">No break days</option>
                            <option value="0">Sunday</option>
                            <option value="6">Saturday</option>
                            <option value="-1">No break</option>
                        </select>
                    </div>

                    <button class="btn btn-primary form-btn" onclick="generateSchedule()">
                        🗓️ Generate Study Plan
                    </button>

                    <div id="planner-error" class="alert alert-error" style="display:none; margin-top:1rem"></div>
                </div>

                <!-- Output Section -->
                <div>
                    <div id="summary-cards" style="display:none; margin-bottom:1.5rem">
                        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr)">
                            <div class="stat-card">
                                <div class="stat-icon amber">📅</div>
                                <div>
                                    <div class="stat-value" id="days-remaining">0</div>
                                    <div class="stat-label-card">Days Remaining</div>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon green">📚</div>
                                <div>
                                    <div class="stat-value" id="total-subjects">0</div>
                                    <div class="stat-label-card">Subjects</div>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon blue">⏰</div>
                                <div>
                                    <div class="stat-value" id="total-hours">0</div>
                                    <div class="stat-label-card">Total Hours</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="schedule-output" class="card">
                        <div class="section-header">
                            <div>
                                <div class="card-title">📋 Your Study Schedule</div>
                                <div class="card-subtitle">Day-by-day breakdown</div>
                            </div>
                            <div style="display:flex; gap:0.5rem">
                                <button class="btn btn-outline btn-sm" onclick="printSchedule()">🖨️ Print</button>
                                <button class="btn btn-outline btn-sm" onclick="downloadCSV()">⬇️ CSV</button>
                            </div>
                        </div>

                        <div class="table-wrapper">
                            <table id="schedule-table">
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Date</th>
                                        <th>Day of Week</th>
                                        <th>Subject</th>
                                        <th>Hours</th>
                                        <th>Focus Area</th>
                                    </tr>
                                </thead>
                                <tbody id="schedule-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const FOCUS_AREAS = ['Theory & Concepts', 'Problem Solving', 'Past Papers', 'Revision', 'Formula Review', 'Mock Test', 'Quick Review'];

let scheduleData = [];

function generateSchedule() {
    const subjectsRaw = document.getElementById('subjects').value.trim();
    const examDate    = document.getElementById('exam_date').value;
    const hoursPerDay = parseInt(document.getElementById('hours_per_day').value);
    const breakDay    = document.getElementById('break_day').value;
    const errEl       = document.getElementById('planner-error');

    errEl.style.display = 'none';

    if (!subjectsRaw) {
        showError('Please enter at least one subject.');
        return;
    }
    if (!examDate) {
        showError('Please select an exam date.');
        return;
    }

    const subjects = subjectsRaw.split(',').map(s => s.trim()).filter(s => s.length > 0);
    if (subjects.length === 0) {
        showError('Please enter valid subject names.');
        return;
    }

    const today    = new Date();
    today.setHours(0, 0, 0, 0);
    const exam     = new Date(examDate);
    const diffMs   = exam - today;
    const totalDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (totalDays < 1) {
        showError('Exam date must be at least 1 day in the future.');
        return;
    }

    // Build study days (skip break day)
    const studyDays = [];
    for (let i = 0; i < totalDays; i++) {
        const d = new Date(today);
        d.setDate(today.getDate() + i);
        if (breakDay !== '' && d.getDay() === parseInt(breakDay)) continue;
        studyDays.push(d);
    }

    if (studyDays.length === 0) {
        showError('Not enough study days. Try adjusting your break day or exam date.');
        return;
    }

    // Distribute subjects across days
    scheduleData = [];
    let subjectIndex = 0;
    let dayInSubject = 0;
    const daysPerSubject = Math.max(1, Math.floor(studyDays.length / subjects.length));

    for (let i = 0; i < studyDays.length; i++) {
        const day    = studyDays[i];
        const isLast = i === studyDays.length - 1;

        // Transition subject
        if (dayInSubject >= daysPerSubject && subjectIndex < subjects.length - 1) {
            subjectIndex++;
            dayInSubject = 0;
        }

        const subject   = subjects[subjectIndex];
        const focusCycle = FOCUS_AREAS[(dayInSubject) % FOCUS_AREAS.length];

        scheduleData.push({
            dayNum : i + 1,
            date   : day.toLocaleDateString('en-IN', { day:'2-digit', month:'short', year:'numeric' }),
            dayName: DAYS[day.getDay()],
            subject,
            hours  : hoursPerDay,
            focus  : isLast ? '🔥 Final Revision' : focusCycle
        });

        dayInSubject++;
    }

    // Render
    const tbody = document.getElementById('schedule-body');
    tbody.innerHTML = '';
    for (const row of scheduleData) {
        const isWeekend = row.dayName === 'Sunday' || row.dayName === 'Saturday';
        tbody.innerHTML += `
            <tr style="${isWeekend ? 'background:rgba(247,183,49,0.03)' : ''}">
                <td><strong>${row.dayNum}</strong></td>
                <td>${row.date}</td>
                <td><span class="badge ${isWeekend ? 'badge-amber' : 'badge-blue'}">${row.dayName}</span></td>
                <td class="day-subject">${row.subject}</td>
                <td>${row.hours}h</td>
                <td style="color:var(--text-muted); font-size:0.85rem">${row.focus}</td>
            </tr>
        `;
    }

    // Update summary
    document.getElementById('days-remaining').textContent = totalDays;
    document.getElementById('total-subjects').textContent = subjects.length;
    document.getElementById('total-hours').textContent    = studyDays.length * hoursPerDay;

    document.getElementById('summary-cards').style.display = 'block';
    document.getElementById('schedule-output').style.display = 'block';
    document.getElementById('schedule-output').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showError(msg) {
    const el = document.getElementById('planner-error');
    el.textContent = '⚠️ ' + msg;
    el.style.display = 'flex';
}

function printSchedule() {
    window.print();
}

function downloadCSV() {
    if (scheduleData.length === 0) return;
    const header = ['Day', 'Date', 'Day of Week', 'Subject', 'Hours', 'Focus Area'];
    const rows   = scheduleData.map(r => [r.dayNum, r.date, r.dayName, r.subject, r.hours, r.focus]);
    const csv    = [header, ...rows].map(r => r.join(',')).join('\n');
    const blob   = new Blob([csv], { type: 'text/csv' });
    const url    = URL.createObjectURL(blob);
    const a      = document.createElement('a');
    a.href       = url;
    a.download   = 'study_schedule.csv';
    a.click();
    URL.revokeObjectURL(url);
}

// Set minimum date to tomorrow
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
document.getElementById('exam_date').min = tomorrow.toISOString().split('T')[0];
</script>
</body>
</html>
