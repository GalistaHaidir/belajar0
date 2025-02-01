document.addEventListener("DOMContentLoaded", function () {
    const calendar = document.getElementById("calendar");
    const modal = new bootstrap.Modal(document.getElementById("memoModal"));
    const tanggalInput = document.getElementById("tanggal");
    const memoInput = document.getElementById("memo");

    // Fungsi untuk memuat memo dari database
    async function loadMemos() {
        const response = await fetch("load_memos.php");
        const memos = await response.json();

        // Tandai tanggal yang memiliki memo
        document.querySelectorAll(".day").forEach(day => {
            const date = day.dataset.date;
            if (memos[date]) {
                day.classList.add("has-memo");
            }
        });
    }

    // Generate kalender
    const daysInMonth = new Date(2025, 1, 0).getDate(); // Februari 2025
    for (let i = 1; i <= daysInMonth; i++) {
        const day = document.createElement("div");
        day.className = "day";
        day.textContent = i;
        day.dataset.date = `2025-02-${i.toString().padStart(2, "0")}`;
        day.addEventListener("click", async function () {
            const date = this.dataset.date;
            tanggalInput.value = date;

            // Ambil memo untuk tanggal ini
            const response = await fetch(`get_memo.php?date=${date}`);
            const data = await response.json();
            memoInput.value = data.memo || ""; // Isi memo jika ada, kosongkan jika tidak ada

            modal.show();
        });
        calendar.appendChild(day);
    }

    // Muat memo setelah kalender dibuat
    loadMemos();
});