const calendarElement = document.getElementById('calendar');
const notes = {};

function createCalendar(year, month) {
    const date = new Date(year, month, 1);
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    // Menghapus kalender sebelumnya
    calendarElement.innerHTML = '';

    // Menambahkan hari ke kalender
    for (let i = 1; i <= daysInMonth; i++) {
        const dayElement = document.createElement('div');
        dayElement.className = 'day';
        dayElement.innerText = i;

        // Menambahkan catatan jika ada
        if (notes[i]) {
            const noteElement = document.createElement('div');
            noteElement.className = 'note';
            noteElement.innerText = notes[i];
            dayElement.appendChild(noteElement);
        }

        // Event untuk menambahkan catatan
        dayElement.addEventListener('click', () => {
            const memo = prompt('Masukkan catatan untuk tanggal ' + i);
            if (memo) {
                notes[i] = memo;
                createCalendar(year, month); // Refresh kalender
            }
        });

        calendarElement.appendChild(dayElement);
    }
}

// Membuat kalender untuk bulan ini
const today = new Date();
createCalendar(today.getFullYear(), today.getMonth());